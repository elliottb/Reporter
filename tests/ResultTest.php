<?php

namespace Reporter;
use Reporter;

class ResultTest extends \PHPUnit_Framework_TestCase
{
	public function testResultObject()
	{
		$config = new \Stdclass;
		$config->name = "Test name";
		$config->uri = "http://testuri";
		$config->content = "html";
		$config->operator = "contains";
		$config->args = "Hello Test!";

		//$config = $this->getResultSetObject();

		$result_set = new Result($config);
		
		$this->assertSame($result_set->name, $config->name);
		$this->assertSame($result_set->uri, $config->uri);
		$this->assertSame($result_set->content, $config->content);
		$this->assertSame($result_set->operator, $config->operator);
		$this->assertSame($result_set->args, $config->args);
	}

}
