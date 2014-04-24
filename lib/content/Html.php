<?php

namespace Reporter\content;

class Html
{
	public static function contains($arg, \Reporter\Response $response_object)
	{
		return (strpos($response_object->getHtml(), $arg) !== false) ? true : false;
	}

	public static function doesntContain($arg, \Reporter\Response $response_object) 
	{
		return !self::contains($arg, $response_object);
	}
}
