PSUEmail
========

Obtain the email from any Penn State University student

## Example

	$p = new PSUEmail();
	$p->setNames(array('Austin Kelleher'))
		->sendRequest();

	foreach($p->getEmails() as $email){
		echo $email["name"] . " " . $email["email"] . "\n";
	}
