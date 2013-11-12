<?php

/**
* Obtain email of any Penn State student from name
* and email them upon request
*
* @author Austin Kelleher, a@alk.im
*/
class PSUEmail {

	private $url = "http://www.work.psu.edu/cgi-bin/ldap/ldap_query.cgi";
	private $emails = array();
	private $names;
	private $subject;
	private $message;
	private $send_mail;
	private $from;

	/**
	* Construct the PSUEmail Class and set the data for obtaining PSU email
	*
	* @param subject Subject of the email
	* @param message Message of the email
	* @param names First and last name of the student seperated by a space
	* @param send_mail Whether or not to send an email or simply echo email
	*/
	public function PSUEmail($subject, $message, $names, $send_mail = false, $from = null){
		$this->names = $names;
		$this->subject = $subject;
		$this->message = $message;
		$this->send_mail = $send_mail;
		$this->from = $from;
	}

	/**
	* Sends the request for data on users
	*/
	public function sendRequest(){
		foreach($this->names as $name){
			$fields_string = null;

			$fields = array(
				'sn' => urlencode(""),
				'cn' => urlencode($name),
				'uid' => urlencode(""),
				'mail' => urlencode(""),
				'full' => urlencode("0"),
				'submit' => urlencode("Search"),
			);

			foreach($fields as $key=>$value) { 
				$fields_string .= $key.'='.$value.'&'; 
			}

			rtrim($fields_string, '&');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch,CURLOPT_URL, $this->url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

			$result = curl_exec($ch);
			curl_close($ch);

			$this->parseData($result);
		}

		if($this->send_mail){
			$this->initEmail();
		}

		$this->displayEmails();
	}

	/**
	* Parse HTML data received from HTTP request & find all mailto elements
	*
	* @param result HTTP response data
	*/
	public function parseData($result){
		$pattern = '~\bhref\s*+=\s*+(["\'])mailto:\K(?<mail>(?<name>[^@]++)@(?<domain>.*?))\1[^>]*+>(?:\s*+</?(?!a\b)[^>]*+>\s*+)*+(?<content>[^<]++)~i';
		preg_match_all($pattern, $result, $matches, PREG_SET_ORDER);

		for($i = 0; $i < count($matches); $i++){
			array_push($this->emails, $matches[$i]['mail']);
		}
	}

	/**
	* Send emails to all students requested
	*/
	public function initEmail(){
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: " . $this->from . "\r\n";
		$headers .= "Reply-To: " . $this->from . "\r\n";
		$headers .= "Return-Path: myplace@here.com\r\n";
		$bcc = "BCC: ";

		for($i = 1; $i < count($this->emails); $i++){
			if($i == count($this->emails)-1){
				$bcc .= $this->emails[$i];
			} else {
				$bcc .= $this->emails[$i] . ",";
			}
		}

		$headers .= $bcc . "\r\n";

		$headers .= 'X-Mailer: PHP/' . phpversion();
		mail($this->emails[0], $this->subject, $this->message, $headers);
	}

	/**
	* Display all emails that were found
	*/
	public function displayEmails(){
		foreach($this->emails as $email){
			echo $email . "\n";
		}
	}
}

?>