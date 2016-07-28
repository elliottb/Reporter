<?php

namespace Reporter;

class ResultSet
{
	protected $results;
	protected $fails;
	protected $passes;
	protected $skips;

	public function __construct() 
	{
		$this->passes = 0;
		$this->fails = 0;
		$this->skips = 0;
	}

	public function setFail($config) 
	{
		$result = new Result($config);
		$result->status = 'fail';
		$this->results[] = $result;
		$this->fails++;
	}

	public function setPass($config) 
	{
		$result = new Result($config);
		$result->status = 'pass';
		$this->results[] = $result;
		$this->passes++;
	}

	public function setSkipped($config) 
	{
		$result = new Result($config);
		$result->status = 'skipped';
		$this->results[] = $result;
		$this->skips++;
	}

	public function getFailCount() 
	{
		return $this->fails;
	}

	public function getPassCount() 
	{
		return $this->passes;
	}

	public function getSkipCount() 
	{
		return $this->skips;
	}

	public function getResults() 
	{
		return $this->results;
	}
}
