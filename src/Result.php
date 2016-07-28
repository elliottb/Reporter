<?php

namespace Reporter;

class Result
{
	public $name;
	public $uri;
	public $content;
	public $operator;
	public $args;
	public $status;

	public function __construct($config) 
	{
		$this->name = $config->name;
		$this->uri = $config->uri;
		$this->content = $config->content;
		$this->operator = $config->operator;
		$this->args = $config->args;
	}
}
