<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use App\Mail\MailMessage;

#[AsCommand(
    name: 'app:test-email',
    description: 'Send a test reservation email'
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bus->dispatch(
            new MailMessage(
                to: 'sokomiano@gmail.com',
                subject: 'Test email',
                template: 'emails/created_user.html.twig',
                context: [
                    'user' => (object)['firstname' => 'John'],
                    'resource' => (object)['name' => 'Meeting Room'],
                    'reservation' => (object)[
                        'start' => new \DateTimeImmutable('+1 day'),
                        'end' => new \DateTimeImmutable('+1 day +2 hours'),
                    ],
                ]
            )
        );

        $output->writeln('Test email sent');

        return Command::SUCCESS;
    }
}
