<?php

/**
* Obtain the email of any Penn State student from their name
*
* @author Austin Kelleher, a@alk.im
*/
class PSUEmail {
	const LDAP_URL = "http://www.work.psu.edu/cgi-bin/ldap/ldap_query.cgi";
	private $emails = array();
	private $names = array();

	/**
	* Sends the request for data on users
	*/
	public function sendRequest() {
		foreach($this->names as $name) {
			$fields_string = null;

			$fields = array(
				'sn' => "",
				'cn' => $name,
				'uid' => "",
				'mail' => "",
				'full' => "0",
				'submit' => "Search"
			);

			foreach($fields as $key=>$value) { 
				$fields_string .= $key.'='.urlencode($value).'&'; 
			}

			rtrim($fields_string, '&');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, self::LDAP_URL);
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

			$result = curl_exec($ch);
			curl_close($ch);

			$this->parseData($result, $name);
		}
	}

	/**
	* Parse HTML data received from HTTP request & find all mailto elements
	*
	* @param result HTTP response data
	* @param name The name of the owner of the email being searched for
	*/
	private function parseData($result, $name){
		$regex = '~\bhref\s*+=\s*+(["\'])mailto:\K(?<mail>(?<name>[^@]++)@(?<domain>.*?))'.
			'\1[^>]*+>(?:\s*+</?(?!a\b)[^>]*+>\s*+)*+(?<content>[^<]++)~i';

		preg_match_all($regex, $result, $matches, PREG_SET_ORDER);

		for($i = 0; $i < count($matches); $i++) {
			array_push($this->emails, array(
				"name" => $name,
				"email" => $matches[$i]['mail']
			));
		}
	}

	/**
	* Sets the array of names to find emails for
	*/
	public function setNames($names) {
		$this->names = $names;
		return $this;
	}

	/**
	* Display all emails that were found
	*/
	public function getEmails() {
		return $this->emails;
	}

	/**
	* Empties the email array
	*/
	public function emptyEmails() {
		$this->emails = array();
		return $this;
	}
}

?>
