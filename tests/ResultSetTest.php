<?php

namespace Reporter;
use Reporter;

class ResultSetTest extends \PHPUnit_Framework_TestCase
{
	protected function getResultSetObject() 
	{
		$config = new \Stdclass;
		$config->name = "Test name";
		$config->uri = "http://testuri";
		$config->content = "html";
		$config->operator = "contains";
		$config->args = "Hello Test!";
		return $config;
	}

	public function testResultSetInstance() {
		$result_set = new ResultSet();
		$this->assertInstanceOf(ResultSet::class, $result_set);
		return $result_set;
	}

 	/**
     * @depends testResultSetInstance
     */
	public function testSetFail(ResultSet $result_set)
	{
		$config = $this->getResultSetObject();
		$result_set->setFail($config);

		$this->assertEquals($result_set->getFailCount(), 1);
		$this->assertEquals($result_set->getPassCount(), 0);
		$this->assertEquals($result_set->getSkipCount(), 0);
	}

	/**
     * @depends testResultSetInstance
     */
	public function testSetPass(ResultSet $result_set)
	{
		$config = $this->getResultSetObject();
		$result_set->setPass($config);

		$this->assertEquals($result_set->getFailCount(), 1);
		$this->assertEquals($result_set->getPassCount(), 1);
		$this->assertEquals($result_set->getSkipCount(), 0);
	}

	/**
     * @depends testResultSetInstance
     */
	public function testSetSkip(ResultSet $result_set)
	{
		$config = $this->getResultSetObject();
		$result_set->setSkipped($config);

		$this->assertEquals($result_set->getFailCount(), 1);
		$this->assertEquals($result_set->getPassCount(), 1);
		$this->assertEquals($result_set->getSkipCount(), 1);
	}

	/**
     * @depends testResultSetInstance
     */
	public function testGetResults(ResultSet $result_set)
	{
		$results = $result_set->getResults();
		$this->assertTrue(is_array($results));
		$this->assertTrue(count($results) == 3);
		$this->assertContainsOnlyInstancesOf(Result::class, $results);
	}
}
