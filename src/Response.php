<?php

namespace Reporter;

class Response
{
	protected $status_code;
	protected $headers;
	protected $html;
	protected $raw_response;

	public function __construct($curl_response) 
	{
		$this->raw_response = $curl_response;

		$parsed_contents = $this->parseCurlResponse($curl_response);
		$this->status_code = $parsed_contents->status_code;
		$this->headers = $parsed_contents->headers;
		$this->html = $parsed_contents->html;
	}

	protected function parseCurlResponse($curl_response) 
	{
		$headers = array();
		$html = array();
		$header_count = 0;
		$line_number = 1;
		$previous_line = null;
		$is_header_line = true;

		foreach (preg_split("/((\r?\n)|(\r\n?))/", $curl_response) as $line) {
    		
			if (($line_number == 1 && strpos($line, 'HTTP/') === 0) || 
				($is_header_line && $line != '')) {
				//this is a header line, store
				$headers[$header_count][] = $line;
				$is_header_line = true;
			} elseif (strpos($line, 'HTTP/') === 0 && $previous_line === '') {
				//this is a new set of headers after 
				$header_count++;
				$headers[$header_count][] = $line;
				$is_header_line = true;
			} elseif ($line == '') {
				//empty line could be a break between redirected header responses or html
				$is_header_line = false;
			} else {
				//this is the rest of the html
				$html[] = $line;
			}

			$previous_line = $line;
			$line_number++;
		}

		$header_object = $this->parseHeaders($headers);

		$parsed_contents = new \Stdclass;
		$parsed_contents->html = implode("\n", $html);
		$parsed_contents->status_code = $header_object->status_code;
		$parsed_contents->headers = $header_object->headers;

		return $parsed_contents;
	}

	protected function parseHeaders(Array $headers) 
	{
		$filtered_headers = array();
		$status_code = null;
		//headers is an array of header sets, each containing an array of indivudual headers
		foreach ($headers as $set_index => $header_set) {
			foreach ($header_set as $header) {

				$header_name = null;
				$header_value = null;

				if (strpos($header, 'HTTP/') === 0) {
					//status code line could be something other than 1.1 in future
					$header_name = 'status_code';
					$first_space = strpos($header, ' ') + 1;
					$next_space = strpos($header, ' ', $first_space);
					$header_value = substr($header, $first_space, $next_space - $first_space);
					//store numeric status code too
					if ($header_value) {
						$status_code = $header_value;
						$filtered_headers[$set_index][$header_name] = $header_value;
					}
				} else {
					$parts = explode(': ', $header);
					$header_name = $parts[0];
					$header_value = $parts[1];
				}

				if ($header_name && $header_value) {
					$filtered_headers[$set_index][$header_name] = $header_value;
				}
			}
		}

		$header_object = new \StdClass;
		$header_object->status_code = $status_code;
		$header_object->headers = $filtered_headers;
		return $header_object;
	}

	public function getHtml() 
	{
		return $this->html;
	}

	public function getHeader($header) 
	{
		$header_sets = $this->headers;
		$last_header_set = array_pop($header_sets);
		if (isset($last_header_set[$header])) {
			return $last_header_set[$header];
		}
		return false;
	}

	public function getStatusCode() 
	{
		return $this->status_code;
	}

	public function getRawResponse() 
	{
		return $this->raw_response;
	}
}
