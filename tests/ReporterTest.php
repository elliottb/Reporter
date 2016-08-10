<?php

namespace Reporter;
use Reporter;

class ReporterTest extends \PHPUnit_Framework_TestCase
{
	public function getConfig()
	{
		$config = parse_ini_file('config.ini.sample');
		$config['include_base'] = './';
		return $config;
	}

	public function testInstantiation()
	{
		$reporter = new Reporter\Reporter($this->getConfig());
		return $reporter;
	}

	/**
	 * @depends testInstantiation
	 */
	public function testParseConfig($reporter) {
		$config = $this->getConfig();
		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod('parseConfig');
		$method->setAccessible(true);

		$this->assertTrue($method->invokeArgs($reporter, array($this->getConfig())));

		$property = $class->getProperty('logfile');
		$property->setAccessible( true );
		$this->assertEquals($property->getValue($reporter), $config['logfile']);
	}

	/**
	 * @depends testInstantiation
	 */
	public function testParseTestFileContents($reporter) {
		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod('parseTestFileContents');
		$method->setAccessible(true);

		$test_config_string = file_get_contents('report_config/github.json');
		$test_config = $method->invokeArgs($reporter, array($test_config_string));

		$this->assertTrue($test_config !== false);
		$this->assertTrue(gettype($test_config) == 'object');

		return $test_config;
	}

	/**
	 * @depends testInstantiation
	 */
	public function testRetrieveTestFileContents($reporter) {
		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod('retrieveTestFileContents');
		$method->setAccessible(true);

		$file_contents = $method->invokeArgs($reporter, array('report_config/github.json'));

		$this->assertTrue($file_contents !== false);
		$this->assertTrue(gettype(json_decode($file_contents)) == 'object');
	}

	/**
	 * @depends testInstantiation
	 * @depends testParseTestFileContents
	 */
	public function testRunTest($reporter, $test_config) {
		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod('runTest');
		$method->setAccessible(true);

		$single_test_config = array_pop($test_config->tests);

		$resultSet = new ResultSet();
		//Problem - test stores output, and doesn't return it. Change to return output from runTest to runTestFile and then store there instead.
		
		$returnVal = $method->invokeArgs($reporter, array($single_test_config, &$resultSet));
		$this->assertTrue(strpos($returnVal, 'GitHub Status Test: PASS') !== false);

		$results = $resultSet->getResults();
		$result = array_shift($results);

		$this->assertTrue($result->status == 'pass');
	}

	/**
	 * @depends testInstantiation
	 */
	public function testGetTestFiles($reporter) {

		$config = $this->getConfig();
		$config['test_file'] = 'github.json';

		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod('getTestFiles');
		$method->setAccessible(true);

		$expected_value = array($config['include_base'] . $config['test_folder'] . '/' . $config['test_file']);
		$return_val = $method->invokeArgs($reporter, array($config));
		$this->assertTrue($return_val == $expected_value);
	}

	/**
	 * @depends testInstantiation
	 */
	/*
	Note: This shouldn't run everything - refactor based on this
	public function testProcessTestFile($config) {
		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod('processTestFile');
		$method->setAccessible(true);

		$reporter = new Reporter\Reporter($config);
		$method->invokeArgs($reporter, array('tests/github.json'));
		
	}*/

	protected static function getMethod($name) {
		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

	public function getPrivateProperty( $className, $propertyName ) {
		$reflector = new ReflectionClass( $className );
		$property = $reflector->getProperty( $propertyName );
		$property->setAccessible( true );
		return $property;
	}
}
