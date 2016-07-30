<?php

namespace Reporter;
use Reporter;

class ReporterTest extends \PHPUnit_Framework_TestCase
{
	public function testInstantiation()
	{
		$config_file = 'config.ini.sample';
		$config = parse_ini_file($config_file);
		$reporter = new Reporter\Reporter($config);
		return $config;
	}

	/**
     * @depends testInstantiation
     */
	public function testParseConfig($config) {
		$class = new \ReflectionClass('Reporter\Reporter');
		$method = $class->getMethod('parseConfig');
		$method->setAccessible(true);

		$reporter = new Reporter\Reporter($config);
		$this->assertTrue($method->invokeArgs($reporter, array($config)));

		$property = $class->getProperty('logfile');
		$property->setAccessible( true );
		$this->assertEquals($property->getValue($reporter), $config['logfile']);
		echo $config['logfile'];
	}

	/**
     * @depends testInstantiation
     * @expectedException PHPUnit_Framework_Error
     */
	public function testParseUnwritableLogfile($config) {
		$config['logfile'] = '/Does_not_exist.file';
		$reporter = new Reporter\Reporter($config);
	}
}
