<?php

namespace Reporter;

class Notifier
{
	protected $host;
	protected $port;
	protected $auth;
	protected $secure;
	protected $username;
	protected $password;
	protected $from_email;
	protected $from_name;
	protected $phpmailer;
	protected $html;

	public function __construct($config) 
	{
		if (!isset($config['smtp_host'])) {
			trigger_error('Required config value smtp_host is not set in config file', E_USER_ERROR);
		} else {
			$this->host = $config['smtp_host'];
		}

		if (!isset($config['smtp_from_email'])) {
			trigger_error('Required config value smtp_from_email is not set in config file', E_USER_ERROR);
		} else {
			$this->from_email = $config['smtp_from_email'];
		}

		$this->port = isset($config['smtp_port']) ? $config['smtp_port'] : null;
		$this->auth = isset($config['smtp_auth']) ? $config['smtp_auth'] : null;
		$this->secure = isset($config['smtp_secure']) ? $config['smtp_secure'] : null;
		$this->username = isset($config['smtp_username']) ? $config['smtp_username'] : null;
		$this->password = isset($config['smtp_password']) ? $config['smtp_password'] : null;
		$this->from_name = isset($config['smtp_from_name']) ? $config['smtp_from_name'] : null;
		$this->html = isset($config['html_email']) ? $config['html_email'] : true;
		
		$this->instantiate();
	}

	protected function instantiate() 
	{
		try {
			$this->phpmailer = new \PHPMailer();
			$this->phpmailer->IsSMTP();
			$this->phpmailer->Host = $this->host;
			$this->phpmailer->From = $this->from_email;
			$this->phpmailer->isHTML($this->html);

			if ($this->auth) {
				$this->phpmailer->SMTPAuth = $this->auth;
			}
			if ($this->username) {
				$this->phpmailer->Username = $this->username;
			}
			if ($this->password) {
				$this->phpmailer->Password = $this->password;
			}
			if ($this->secure) {
				$this->phpmailer->SMTPSecure = $this->secure;
			}
			if ($this->from_name) {
				$this->phpmailer->FromName = $this->from_name;
			}
			if ($this->port) {
				$this->phpmailer->Port = $this->port;
			}			

		} catch (Exception $e) {
			trigger_error('Could not instantiate PHPMailer: ' . escapeshellcmd($e), E_USER_ERROR);
		}
	}

	public function sendResults($result_set, $test_config) 
	{
		$plaintext = '';

		$html = isset($test_config->description) ? '<p>' . $test_config->description . '</p>' : '';

		$html .= "<table style=\"border:1px solid grey;border-collapse:collapse;\">
			<tr>
				<th style=\"background-color:#FAFAFA;padding:2px 6px;text-align:left;\">Test Name</th>
				<th style=\"background-color:#FAFAFA;padding:2px 6px;text-align:left;\">Logic</th>
				<th style=\"background-color:#FAFAFA;padding:2px 6px;text-align:left;\">Result</th>
			</tr>\n";

		foreach ($result_set->getResults() as $result) {

			$logic = 'content: ' . htmlentities($result->content, ENT_QUOTES) . ', '  .
				'operator: ' . htmlentities($result->operator, ENT_QUOTES) . ', '  .
				'args: ' . htmlentities($result->args, ENT_QUOTES);

			$html .= "<tr style=\"border-bottom:1px solid grey;\">
				<td style=\"border-top:1px solid grey;padding:2px 6px;\">" . htmlentities($result->name, ENT_QUOTES) . "</td>
				<td style=\"border-top:1px solid grey;padding:2px 6px;\">" . $logic . "</td>
				<td style=\"border-top:1px solid grey;padding:2px 6px;\">" . htmlentities(strtoupper($result->status), ENT_QUOTES) . "</td>
			</tr>\n";

			$plaintext .= strip_tags($html) . "\n";
		}

		$html .= "</table>
		<br />
		<table style=\"border:1px solid grey;border-collapse:collapse;\">
			<tr>
				<th style=\"background-color:#FAFAFA;padding:2px 6px;text-align:left;\">Results</th>
				<th style=\"background-color:#FAFAFA;padding:2px 6px;\"></th>
			</tr>
			<tr>
				<td style=\"border-top:1px solid grey;padding:2px 6px;\">Passes</td>
				<td style=\"border-top:1px solid grey;padding:2px 6px;text-align:right;\">" . $result_set->getPassCount() . "</td>
			</tr>
			<tr>
				<td style=\"border-top:1px solid grey;padding:2px 6px;\">Fails</td>
				<td style=\"border-top:1px solid grey;padding:2px 6px;text-align:right;\">" . $result_set->getFailCount() . "</td>
			<tr>
				<td style=\"border-top:1px solid grey;padding:2px 6px;\">Skips</td>
				<td style=\"border-top:1px solid grey;padding:2px 6px;text-align:right;\">" . $result_set->getSkipCount() . "</td>
			</tr>
		</table>";

		$this->phpmailer->Body = $html;
		$this->phpmailer->AltBody = $plaintext;
		$this->phpmailer->Subject = 'Reporter Test Results: ' . self::htmlToPlainText($test_config->name);

		$emails = $test_config->emails;
		if (is_array($emails)) {
			foreach($emails as $email) {
				$this->phpmailer->AddAddress($email);
			}	
		}

		try {
			if (! $this->phpmailer->Send()) {
				trigger_error("PHPMailer Error: " . $this->phpmailer->ErrorInfo, E_USER_ERROR);
			}
		} catch (Exception $e) {
			trigger_error('PHPMailer Error: ' . escapeshellcmd($e), E_USER_ERROR);
		}

		return true;
	}

	public static function htmlToPlainText($html) 
	{
	    return html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
	}
}
