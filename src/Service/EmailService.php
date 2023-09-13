<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $toEmail, string $subject, string $htmlContent): void
    {
        $email = (new Email())
            ->from('snowtriks@outlook.com')
            ->to($toEmail)
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
        dd($email);
    }
}