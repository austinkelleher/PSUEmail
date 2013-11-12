PSUEmail
========

Obtain the email from any Penn State University student

## Example

	$p = new PSUEmail(
		"Email Subject",
		"Email message",
		/* Who to search for? */
		array(
			"Austin Kelleher"
		),
		/* Would you like to email them? */
		true,
		/* What is the email to send from? */
		"alk5492@psu.edu"
	);

	$p->sendRequest();
