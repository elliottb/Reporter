<?php

namespace Reporter;
use Reporter;

class ContentStatusTest extends \PHPUnit_Framework_TestCase
{
	public function testRespEqual()
	{
		$resp = file_get_contents('tests/fixtures/resp1.txt');
		$respObject = new Response($resp);

		$this->assertTrue(Reporter\Content\Status::equal(200, $respObject));		
		$this->assertFalse(Reporter\Content\Status::equal(301, $respObject));

		return $respObject;

	}

	public function testRespNotEqual()
	{
		$resp = file_get_contents('tests/fixtures/resp1.txt');
		$respObject = new Response($resp);

		$this->assertTrue(Reporter\Content\Status::notEqual(301, $respObject));		
		$this->assertFalse(Reporter\Content\Status::notEqual(200, $respObject));

		return $respObject;

	}
}
