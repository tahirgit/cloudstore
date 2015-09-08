<?php

class EmailController extends Controller
{
	var $to = "support";
	var $subject;
	var $message;
 	var $from;
	var $fromName;
	var $headers;
	
	public function sendEmail()
	{
		$this->headers="From: ".$this->fromName." <support@centrica-it.com>\r\n".
						"Reply-To: support@centrica-it.com\r\n".
						"MIME-Version: 1.0\r\n".
						"Content-type: text/html; charset=UTF-8";
						
		if(mail($this->to, $this->subject, $this->message, $this->headers, "-f support@centrica-it.com"))
			return true;
		else
			return false;
	} 	
}
?>