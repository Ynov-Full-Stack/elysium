<?php

namespace App\Command;

use App\Entity\User;
use App\Mail\ContextFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use App\Mail\MailMessage;
use App\Mail\MailService;


#[AsCommand(
    name: 'app:mail:accountDel',
    description: 'Send a test reservation email'
)]
class AccountDeleteCommand extends Command
{
    public function __construct(
        private MailService $mailService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currentUser = new User();
        
        $currentUser->setFirstname("Brooki");
        $currentUser->setLastname("Le Maki");
        $currentUser->setPassword("nope");
        $currentUser->setRoles([ "ROLE_USER" ]);
        $currentUser->setEmail('sokomiano@gmail.com');
        $currentUser->setBirthdate(new \DateTime()->setDate(2002,03,13));
        
        $this->mailService->send(MailMessage::accountDeletion($currentUser));

        $output->writeln('<info>Email dispatched successfully</info>');

        return Command::SUCCESS;
    }
}
