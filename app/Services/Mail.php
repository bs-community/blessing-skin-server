<?php

namespace App\Services;

use PHPMailer;

class Mail
{
    /**
     * Instance of PHPMailer
     * @var object
     */
    private $mail;

    public function __construct()
    {
        $mail             = new PHPMailer();
        // $mail->SMTPDebug = 3;                       // Enable verbose debug output
        $mail->isSMTP();                               // Set mailer to use SMTP
        $mail->Host       = $_ENV['MAIL_HOST'];        // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                      // Enable SMTP authentication
        $mail->Username   = $_ENV['MAIL_USERNAME'];    // SMTP username
        $mail->Password   = $_ENV['MAIL_PASSWORD'];    // SMTP password
        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];  // Enable TLS encryption, `ssl` also accepted
        $mail->Port       = $_ENV['MAIL_PORT'];        // TCP port to connect to
        $mail->CharSet    = 'UTF-8';
        $this->mail       = $mail;
    }

    /**
     * Set sender name
     *
     * @param string $name [description]
     */
    public function from($name)
    {
        $this->mail->setFrom($_ENV['MAIL_USERNAME'], $name);
        return $this;
    }

    public function to($address)
    {
        $this->mail->addAddress($address);
        return $this;
    }

    public function subject($subject)
    {
        $this->mail->Subject = $subject;
        return $this;
    }

    public function getLastError()
    {
        return $this->mailer->ErrorInfo;
    }

    public function content($content)
    {
        $this->mail->isHTML(true);
        $this->mail->Body = $content;
        return $this;
    }

    /**
     * Send a mail
     *
     * @return boolean
     */
    public function send()
    {
        return $this->mail->send();
    }

}
