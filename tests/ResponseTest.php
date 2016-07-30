<?php

namespace Reporter;
use Reporter;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
	public function testParseCurlResponseTextfile()
	{
		$resp = file_get_contents('tests/fixtures/resp1.txt');
		list($headers, $body) = explode(PHP_EOL . PHP_EOL, $resp, 2);

		$respObject = new Response($resp);

		$this->assertEquals($respObject->getStatusCode(), 200);
		$this->assertEquals($respObject->getHtml(), $body);
		$this->assertEquals($respObject->getRawResponse(), $resp);
		$this->assertEquals($respObject->getHeader('Content-Length'), 91);
	}

	public function testParseCurlResponsePage()
	{
		$resp = file_get_contents('tests/fixtures/resp2.txt');
		list($headers, $body) = explode(PHP_EOL . PHP_EOL, $resp, 2);

		$respObject = new Response($resp);

		$this->assertEquals(200, $respObject->getStatusCode());
		$this->assertEquals($body, $respObject->getHtml());
		$this->assertEquals($resp, $respObject->getRawResponse());
		$this->assertEquals('no-cache', $respObject->getHeader('Cache-Control'));
		$this->assertFalse($respObject->getHeader('X-NONEXISTENT-CACHE'));
	}

	public function testRedirectedResponsePage()
	{
		$resp = file_get_contents('tests/fixtures/respwithredirect.txt');
		list($headers, $headers2, $body) = explode(PHP_EOL . PHP_EOL, $resp, 3);
		
		$respObject = new Response($resp);

		$this->assertEquals(200, $respObject->getStatusCode());
		$this->assertEquals($body, $respObject->getHtml());
		$this->assertEquals($resp, $respObject->getRawResponse());
		$this->assertEquals('prod', $respObject->getHeader('X-AH-Environment'));
		$this->assertFalse($respObject->getHeader('X-NONEXISTENT-CACHE'));
	}
}
