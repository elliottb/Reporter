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

		$this->assertTrue(Reporter\Content\Status::notEqual(301, $respObject));
		$this->assertFalse(Reporter\Content\Status::notEqual(200, $respObject));

		$this->assertTrue(Reporter\Content\Status::lessThan(400, $respObject));
		$this->assertFalse(Reporter\Content\Status::lessThan(200, $respObject));

		$this->assertTrue(Reporter\Content\Status::greaterThan(100, $respObject));
		$this->assertFalse(Reporter\Content\Status::greaterThan(200, $respObject));

		$this->assertTrue(Reporter\Content\Status::lessThanEqual(200, $respObject));
		$this->assertFalse(Reporter\Content\Status::lessThanEqual(100, $respObject));

		$this->assertTrue(Reporter\Content\Status::greaterThanEqual(200, $respObject));
		$this->assertFalse(Reporter\Content\Status::greaterThanEqual(400, $respObject));

		return $respObject;
	}
}
