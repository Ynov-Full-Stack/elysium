<?php

namespace App\MessageHandler;

use App\Mail\MailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[AsMessageHandler]
class MailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function __invoke(MailMessage $message)
    {
        $email = (new TemplatedEmail())
            ->from('sokomiano@gmail.com')
            ->to($message->to)
            ->subject($message->subject)
            ->htmlTemplate($message->template)
            ->context($message->context);

        $this->mailer->send($email);
    }
}
