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

	public function testSetFail()
	{
		$result_set = new ResultSet();
		$config = $this->getResultSetObject();
		$result_set->setFail($config);

		$this->assertEquals($result_set->getFailCount(), 1);
		$this->assertEquals($result_set->getPassCount(), 0);
		$this->assertEquals($result_set->getSkipCount(), 0);
	}

	public function testSetPass()
	{
		$result_set = new ResultSet();
		$config = $this->getResultSetObject();
		$result_set->setPass($config);

		$this->assertEquals($result_set->getFailCount(), 0);
		$this->assertEquals($result_set->getPassCount(), 1);
		$this->assertEquals($result_set->getSkipCount(), 0);
	}

	public function testSetSkip()
	{
		$result_set = new ResultSet();
		$config = $this->getResultSetObject();
		$result_set->setSkipped($config);

		$this->assertEquals($result_set->getFailCount(), 0);
		$this->assertEquals($result_set->getPassCount(), 0);
		$this->assertEquals($result_set->getSkipCount(), 1);
	}

	public function testGetResults()
	{
		$result_set = new ResultSet();

		$config = $this->getResultSetObject();

		$result_set->setPass($config);
		$result_set->setSkipped($config);
		$result_set->setFail($config);

		$results = $result_set->getResults();
		$this->assertTrue(is_array($results));
		$this->assertTrue(count($results) == 3);
		$this->assertContainsOnlyInstancesOf(Result::class, $results);
	}
}
