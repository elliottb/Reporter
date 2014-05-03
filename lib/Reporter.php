<?php

namespace Reporter;

class Reporter
{
	protected $config;
	protected $remote_content;
	protected $display_output = true;
	protected $logfile;
	protected $notifier;

	public function __construct($config) 
	{
		$this->config = $config;
		if (!$this->parseConfig($config)) {
			trigger_error('There were errors parsing the config file', E_USER_ERROR);
		}

		// Only initiate Notifier if php_mailer_location is set
		if (isset($config['php_mailer_location']) && $config['php_mailer_location']) {
			$this->notifier = new \Reporter\Notifier($this->config);
		}
	}

	public function run() 
	{
		if ($this->config['test_file']) {
			if (is_readable($this->config['test_file'])) {
				$this->processTestFile($this->config['test_file']);
			} elseif (is_readable($this->config['include_base'] . $this->config['test_folder'] . '/' . $this->config['test_file'])) {
				$this->processTestFile($this->config['include_base'] . $this->config['test_folder'] . '/' . $this->config['test_file']);
			} else {
				trigger_error('Could not read specified test file: ' . escapeshellcmd($this->config['test_file']), E_USER_ERROR);
			}

		} else {
			$this->output('Running all tests');
		}
	}

	protected function parseConfig($config) 
	{
		if (isset($this->config['logfile']) && $logfile = $this->config['logfile']) {
			if (is_writable($logfile) || $handle = fopen($logfile, 'w')) {
				$this->logfile = $logfile;
			} else {
				trigger_error('Could not open logfile for writing', E_USER_WARNING);
			}	
		}

		if (isset($this->config['display_output'])) {
			$this->display_output = (bool) $this->config['display_output'];
		}

		return !(bool) error_get_last();
	}

	protected function processTestFile($filepath) 
	{
		if ($this->validateTestFilename($filepath)) {
			if ($contents = $this->retrieveTestFileContents($filepath)) {
				if (($test_config = $this->parseTestFileContents($contents)) !== false) {
					$result_set = new \Reporter\ResultSet();
					$this->runTestFile($test_config, $result_set);
					$this->notifier->sendResults($result_set, $test_config);
				} else {
					trigger_error('Test file ' .  escapeshellcmd($filepath) . ' is not a valid json file and could not be parsed.', E_USER_ERROR);
				}
			} else {
				trigger_error('Cound not retrieve contents of ' . escapeshellcmd($filepath), E_USER_ERROR);
			}
		} else {
			trigger_error('Filename ' . escapeshellcmd($filepath) . ' did not validate', E_USER_ERROR);
		}
	}

	protected function validateTestFilename($filepath) 
	{
		$path_parts = pathinfo($filepath);
		return $path_parts['extension'] == $this->config['test_file_extension'];
	}

	protected function retrieveTestFileContents($filepath) 
	{
		$contents = file_get_contents($filepath);
		return $contents;
	}

	protected function parseTestFileContents($contents) 
	{
		if ($config = json_decode($contents)) {
			return $config;
		} 
		return false;
	}

	protected function runTestFile($test_config, ResultSet &$result_set) 
	{
		$name = $test_config->name;
		$tests = $test_config->tests;

		$this->outputHeader('Executing test file ' . $name);
		foreach ($tests as $single_test_config) {
			$this->runTest($single_test_config, $result_set);
		}
		$result_string = 'Passes: ' . $result_set->getPassCount() . ' | Fails: ' . 
			$result_set->getFailCount().  ' | Skips: ' . $result_set->getSkipCount() ;
		$this->outputHeader('Test complete: ' . $result_string);
	}

	protected function runTest($single_test_config, ResultSet &$result_set) 
	{
		$name = $single_test_config->name;
		$uri = $single_test_config->uri;
		$content = $single_test_config->content;
		$operator = $single_test_config->operator;
		$args = $single_test_config->args;

		$namespace_prefix = "\Reporter\Content\\";

		switch ($content) {
			case 'status':
			case 'html':
				$class = $namespace_prefix . ucfirst($content); break;
			default: 
				$class = $namespace_prefix . $content; break;
		}

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

		if (class_exists($class) && method_exists($class, $method)) {

			try {
				$response_object = new \Reporter\Response($this->getResponse($uri));
			} catch (Exception $e) {
				$result_set->setFail($single_test_config);
				$msg = "- $name: FAIL";
				$this->output($msg);
				$msg = '--Error retrieving URL: ' . $e->getMessage();
				$this->output($msg);
				return false;
			}

			if (!$class::$method($args, $response_object)) {
				$result_set->setFail($single_test_config);
				$msg = "$name: FAIL";
				$this->output($msg);
				
			} else {
				$result_set->setPass($single_test_config);
				$msg = "$name: PASS";
				$this->output($msg);
			}
		} else {
			$result_set->setSkipped($single_test_config);
			$msg = "$name: SKIPPED - content class or operator method not found";
			$this->output($msg);
		}

		return true;
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
			curl_close($ch);
			throw new \Exception(curl_error($ch));
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

	protected function output($msg) 
	{
		if ($this->display_output) {
			echo $msg . "\n";
		}
		if ($this->logfile) {
			self::writeToFile($this->logfile, $msg);
		}	
	}

	protected function outputHeader($msg)
	{
		$break = str_repeat('-', 80);
		$datestamp = self::getDatestamp();
		$msg = substr($msg, 0, 78 - (strlen($datestamp) + 1));
		$msg = str_pad($msg . ': ' . $datestamp, 78, ' ') . ' ';
		$this->output($break);
		$this->output($msg);
		$this->output($break);
	}

	protected static function getDatestamp() 
	{
		return date('Y-m-d H:i:s e');
	}
}
