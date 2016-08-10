<?php

namespace Reporter;
use Reporter;

class ContentHtmlTest extends \PHPUnit_Framework_TestCase
{
	public function testResponseContains()
	{
		$resp = file_get_contents('tests/fixtures/resp1.txt');
		$respObject = new Response($resp);

		$this->assertTrue(Reporter\Content\Html::contains('Developer', $respObject));		
		$this->assertFalse(Reporter\Content\Html::contains('foobar', $respObject));

		return $respObject;

	}

	public function testHtmlContains()
	{
		$resp = file_get_contents('tests/fixtures/resp2.txt');
		$respObject = new Response($resp);

		$this->assertTrue(Reporter\Content\Html::contains('Hello World', $respObject));		
		$this->assertFalse(Reporter\Content\Html::contains('foobar', $respObject));		

		return $respObject;
	}

	/**
     * @depends testResponseContains
     */
	public function testResponseDoesntContain($respObject)
	{
		$this->assertTrue(Reporter\Content\Html::doesntContain('foobar', $respObject));		
		$this->assertFalse(Reporter\Content\Html::doesntContain('Developer', $respObject));
	}

	/**
     * @depends testHtmlContains
     */
	public function testHtmlDoesntContain($respObject)
	{
		$this->assertTrue(Reporter\Content\Html::doesntContain('foobar', $respObject));		
		$this->assertFalse(Reporter\Content\Html::doesntContain('Hello World', $respObject));		
	}
}
