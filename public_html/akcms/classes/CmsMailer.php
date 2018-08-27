<?php
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class CmsMailer
 *
 * Example usage https://github.com/PHPMailer/PHPMailer/,
 *
 * $mailer = new CmsMailer();
 *
 * $mailer->isHTML(true);
 *
 * $mailer->addAddress('address@gmail.com');
 *
 * $mailer->Subject = 'Тема сообщения';
 *
 * $mailer->Body = 'Текст сообщения';
 */
class CmsMailer extends PHPMailer
{
	public function __construct($exceptions = null)
	{
		global $cfg;
		parent::__construct($exceptions);

		$this->isSMTP();
		$this->Host = $cfg['email']['smtp'];
		$this->SMTPAuth = true;
		$this->Username = $cfg['email']['from'];
		$this->Password = $cfg['email']['pssw'];
		$this->SMTPSecure = 'tls';
		$this->From = $cfg['email']['from'];
		$this->FromName = $cfg['email']['fromName'];
		$this->CharSet = 'UTF-8';
		$this->isHTML();
	}
}