<?php

namespace Reporter\Content;

class Status
{
	public static function equal($arg, \Reporter\Response $response_object)
	{
		return ($arg === $response_object->getStatusCode());
	}

	public static function notEqual($arg, \Reporter\Response $response_object) 
	{
		return ($arg !== $response_object->getStatusCode());
	}

	public static function lessThan($arg, \Reporter\Response $response_object)
	{
		return ($response_object->getStatusCode() < $arg);
	}

	public static function lessThanEqual($arg, \Reporter\Response $response_object) 
	{
		return ($response_object->getStatusCode() <= $arg);
	}

	public static function greaterThan($arg, \Reporter\Response $response_object)
	{
		return ($response_object->getStatusCode() > $arg);
	}

	public static function greaterThanEqual($arg, \Reporter\Response $response_object) 
	{
		return ($response_object->getStatusCode() >= $arg);
	}
}
