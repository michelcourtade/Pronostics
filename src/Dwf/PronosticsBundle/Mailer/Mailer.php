<?php
namespace Dwf\PronosticsBundle\Mailer;

class Mailer
{
	protected $mailer;

	public function __construct(\Swift_Mailer $mailer)
	{
		$this->mailer = $mailer;
	}

	public function sendEmail($from, $to, $body, $subject = '')
	{
		$message = \Swift_Message::newInstance()
		->setSubject($subject)
		->setFrom($from)
		->setTo($to)
		->setBody($body, 'text/html');

		$this->mailer->send($message);
	}
}