<?php

namespace Reporter;

class Reporter
{
	protected $remote_content;
	protected $display_output = true;
	protected $logfile;
	protected $notifier;

	public function __construct() {}

	public function run($config)
	{
		if (!$this->parseConfig($config)) {
			trigger_error('There were errors parsing the config file', E_USER_ERROR);
		}

		// Only initiate Notifier if php_mailer_location is set
		if (isset($config['php_mailer_location']) && $config['php_mailer_location']) {
			$this->notifier = new \Reporter\Notifier($config);
		}

		$test_files = $this->getTestFiles($config);

		foreach ($test_files as $test_file) {
			if ($individual_test_config = $this->processTestFile($test_file)) {

				$result_set = new \Reporter\ResultSet();
				$this->runTestFile($individual_test_config, $result_set);

				$notification_expected = self::testNotificationLevelMet($test_config, $result_set) && 
					$this->notifier;

				if ($notification_expected && $this->notifier->sendResults($result_set, $test_config)) {
					echo $this->formatOutput('Results Emailed.');
				}
			}
		}
	}

	protected function getTestFiles($config) {
		$test_files = array();
		if ($config['test_file']) {
			if (!$this->validateTestFilename($config['test_file'], $config)) {
				trigger_error('Filename ' . escapeshellcmd($config['test_file']) . ' did not validate', E_USER_ERROR);
			}
			if (is_readable($config['test_file'])) {
				$test_files[] = $config['test_file'];
			} elseif (is_readable($config['include_base'] . $config['test_folder'] . '/' . $config['test_file'])) {
				$test_files[] = $config['include_base'] . $config['test_folder'] . '/' . $config['test_file'];
			} else {
				trigger_error('Could not read specified test file: ' . escapeshellcmd($config['test_file']), E_USER_ERROR);
			}
		}
		else {
			$test_file_candidates = glob($config['include_base'] . $config['test_folder']  . "/*." . $config['test_file_extension']);
			foreach ($test_file_candidates as $test_file) {
				if (is_readable($test_file)) {
					$test_files[] = $test_file;
				} else {
					trigger_error('Could not read specified test file: ' . escapeshellcmd($test_file), E_USER_WARNING);
				}	
			}
		}
		return $test_files;
	}

	protected function parseConfig($config)
	{
		if (isset($config['logfile']) && $logfile = $config['logfile']) {
			if ($handle = fopen($logfile, 'w')) {
				$this->logfile = $logfile;
			} else {
				trigger_error('Could not open logfile for writing', E_USER_ERROR);
			}	
		}

		if (isset($config['display_output'])) {
			$this->display_output = (bool) $config['display_output'];
		}

		return !(bool) error_get_last();
	}

	protected function processTestFile($filepath)
	{
		if ($contents = self::retrieveTestFileContents($filepath)) {
			if (($test_config = self::parseTestFileContents($contents)) !== false) {
				return $test_config;
			}
			trigger_error('Test file ' .  escapeshellcmd($filepath) . ' is not a valid json file and could not be parsed.', E_USER_ERROR);
		}
		trigger_error('Cound not retrieve contents of ' . escapeshellcmd($filepath), E_USER_ERROR);
	}

	protected function validateTestFilename($filepath, $config)
	{
		$path_parts = pathinfo($filepath);
		return $path_parts['extension'] == $config['test_file_extension'];
	}

	protected static function retrieveTestFileContents($filepath)
	{
		$contents = file_get_contents($filepath);
		return $contents;
	}

	protected static function parseTestFileContents($contents)
	{
		if ($config = json_decode($contents)) {
			return $config;
		} 
		return false;
	}

	protected static function testNotificationLevelMet($test_config, $result_set) 
	{
		$notification_level = isset($test_config->options->email_level) ? $test_config->options->email_level : null;

		switch ($notification_level) {
			case 'all':
				return true;
			case 'skip': 
				return (bool) $result_set->getFailCount() || (bool) $result_set->getSkipCount();
			case 'fail':
			default:
				// Default for missing or misset notification level is fail.
				return (bool) $result_set->getFailCount();
		}
	}

	protected function runTestFile($test_config, ResultSet &$result_set) 
	{
		$name = $test_config->name;
		$tests = $test_config->tests;

		echo $this->outputHeader('Executing test file ' . $name);
		foreach ($tests as $single_test_config) {
			echo $this->runTest($single_test_config, $result_set);
		}
		$result_string = 'Passes: ' . $result_set->getPassCount() . ' | Fails: ' . 
			$result_set->getFailCount().  ' | Skips: ' . $result_set->getSkipCount() ;
		echo $this->outputHeader('Test complete: ' . $result_string);
	}

	protected function runTest($single_test_config, ResultSet &$result_set) 
	{
		$output = '';
		$name = $single_test_config->name;
		$uri = $single_test_config->uri;
		$content = $single_test_config->content;
		$operator = $single_test_config->operator;
		$args = $single_test_config->args;

		$namespace_prefix = "\Reporter\Content\\";
		$class = $namespace_prefix . ucfirst($content);

		$method = self::convertOperatorToMethod($operator);

		if (class_exists($class) && method_exists($class, $method)) {

			try {
				$response_object = new \Reporter\Response($this->getResponse($uri));
			} catch (\Reporter\HostConnectionException $e) {
				$result_set->setFail($single_test_config);
				$msg = "- $name: FAIL";
				$output .= $this->formatOutput($msg);
				$msg = '--Error retrieving URL: ' . $e->getMessage();
				$output .= $this->formatOutput($msg);
				return $output;
			} catch (\Exception $e) {
				$result_set->setSkipped($single_test_config);
				$msg = "- $name: SKIPPED";
				$output .= $this->formatOutput($msg);
				$msg = '--Error retrieving URL - may be a Reporter system issue: ' . $e->getMessage();
				$output .= $this->formatOutput($msg);
				return $output;
			}

			if (!$class::$method($args, $response_object)) {
				$result_set->setFail($single_test_config);
				$msg = "$name: FAIL";
				$output .= $this->formatOutput($msg);
				
			} else {
				$result_set->setPass($single_test_config);
				$msg = "$name: PASS";
				$output .= $this->formatOutput($msg);
			}
		} else {
			$result_set->setSkipped($single_test_config);
			$msg = "$name: SKIPPED - content class or operator method not found";
			$output .= $this->formatOutput($msg);
		}

		return $output;
	}

	protected function getResponse($uri)
	{
		//use this content if already retrieved during this batch
		if (isset($this->remote_content[$uri])) {
			return $this->remote_content[$uri];
		}

		$contents = self::retrieveURI($uri);

		$this->remote_content[$uri] = $contents;
		return $contents;
	}

	protected static function convertOperatorToMethod($operator)
	{
		switch ($operator) {
			case '=': $method = 'equal'; break;
			case '!=': $method = 'notEqual'; break;
			case '<': $method = 'lessThan'; break;
			case '<=': $method = 'lessThanEqual'; break;
			case '>': $method = 'greaterThan'; break;
			case '>=': $method = 'greaterThanEqual'; break;
			case 'contains': $method = 'contains'; break;
			case '!contain': 
			case '!contains': $method = 'doesntContain'; break;
			default: $method = $operator; break;
		}
		return $method;
	}

	protected static function retrieveURI($uri) 
	{
		$parsed_headers = null;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Reporter - github.com/elliottb/Reporter');

		// TODO: will this also test an empty response or a 404?
		if (!$contents = curl_exec($ch)) {
			if (curl_errno($ch) == CURLE_COULDNT_RESOLVE_HOST || curl_errno($ch) == CURLE_COULDNT_CONNECT) {
				throw new \Reporter\HostConnectionException(curl_error($ch));
			}
			else {
				throw new \Exception(curl_error($ch));
			}
			curl_close($ch);
			return false;
		}

		curl_close($ch);
		return $contents;
	}

	protected static function writeToFile($file, $msg) 
	{
		if (is_writable($file)) {
			if  (!$handle = fopen($file, "a")) {
				return false;
			}
			if (fwrite($handle, "$msg\r\n") === FALSE) {
				return false;
			}
			fclose($handle);
			return true;
		}
		return false;
	}

	protected function formatOutput($msg)
	{
		$output = $msg . "\n";
		if ($this->logfile) {
			self::writeToFile($this->logfile, $msg);
		}
		return $output;
	}

	protected function outputHeader($msg)
	{
		$output = '';
		$break = str_repeat('-', 80);
		$datestamp = self::getDatestamp();
		$msg = substr($msg, 0, 78 - (strlen($datestamp) + 1));
		$msg = str_pad($msg . ': ' . $datestamp, 78, ' ') . ' ';
		$output .= $this->formatOutput($break);
		$output .= $this->formatOutput($msg);
		$output .= $this->formatOutput($break);
		return $output;
	}

	protected static function getDatestamp() 
	{
		return date('Y-m-d H:i:s e');
	}
}
