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

	public function getResultConfigObject() 
	{
		$config = new \Stdclass;
		$config->name = "Test name";
		$config->uri = "http://testuri";
		$config->content = "html";
		$config->operator = "contains";
		$config->args = "Hello Test!";
		return $config;
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
	public function testParseTestFileContentsInvalid($reporter) {
		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod('parseTestFileContents');
		$method->setAccessible(true);

		$invalid_json = 'foo' . file_get_contents('report_config/github.json');
		$this->assertFalse($method->invokeArgs($reporter, array($invalid_json)));
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
	 * @depends testParseTestFileContents
	 * @depends testInstantiation
	 */
	public function testNotificationLevelMet($config, $reporter) {

		$resultConfigObject = $this->getResultConfigObject();
		
		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod('testNotificationLevelMet');
		$method->setAccessible(true);

		// Test default level
		$resultSet = new ResultSet();

		$resultSet->setPass($resultConfigObject);
		$this->assertFalse($method->invokeArgs($reporter, array($config, &$resultSet)));
		
		// Test default after fail
		$resultSet->setFail($resultConfigObject);
		$this->assertTrue($method->invokeArgs($reporter, array($config, &$resultSet)));

		// Test all level
		$config->options = new \stdClass;
		$config->options->email_level = 'all';
		$resultSet = new ResultSet();
		$this->assertTrue($method->invokeArgs($reporter, array($config, &$resultSet)));

		// Test skip level
		$config->options->email_level = 'skip';
		$resultSet = new ResultSet();

		// Test skip false
		$this->assertFalse($method->invokeArgs($reporter, array($config, &$resultSet)));

		// Test skip true after skipping
		$resultSet->setSkipped($resultConfigObject);
		$this->assertTrue($method->invokeArgs($reporter, array($config, &$resultSet)));
		
		// Test skip true after false
		$resultSet = new ResultSet();
		$resultSet->setFail($resultConfigObject);
		$this->assertTrue($method->invokeArgs($reporter, array($config, &$resultSet)));
	}

	/**
	 * @depends testInstantiation
	 */
	public function testConvertOperatorToMethod($reporter) {

		$config = $this->getConfig();
		$config['test_file'] = 'github.json';

		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod('convertOperatorToMethod');
		$method->setAccessible(true);

		$this->assertEquals($method->invokeArgs($reporter, array('>')), 'greaterThan');
		$this->assertEquals($method->invokeArgs($reporter, array('<')), 'lessThan');
		$this->assertEquals($method->invokeArgs($reporter, array('=')), 'equal');
		$this->assertEquals($method->invokeArgs($reporter, array('!=')), 'notEqual');
		$this->assertEquals($method->invokeArgs($reporter, array('>=')), 'greaterThanEqual');
		$this->assertEquals($method->invokeArgs($reporter, array('<=')), 'lessThanEqual');
		$this->assertEquals($method->invokeArgs($reporter, array('>')), 'greaterThan');
		$this->assertEquals($method->invokeArgs($reporter, array('contains')), 'contains');
		$this->assertEquals($method->invokeArgs($reporter, array('!contain')), 'doesntContain');
		$this->assertEquals($method->invokeArgs($reporter, array('!contains')), 'doesntContain');
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
