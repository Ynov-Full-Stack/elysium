<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:mail:test',
    description: 'Send a simple test email to Mailpit'
)]
class MailpitTestCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from('sokomiano@gmail.com')
            ->to('sokomiano@gmail.com')
            ->subject('Mailpit test email')
            ->text('If you see this email, Mailpit + Symfony Mailer work.');

        $this->mailer->send($email);

        $output->writeln('<info>Test email sent</info>');

        return Command::SUCCESS;
    }
}
