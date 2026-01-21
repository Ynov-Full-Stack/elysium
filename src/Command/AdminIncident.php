<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\EventType;
use App\Mail\ContextFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use App\Mail\MailMessage;
use App\Mail\MailService;


#[AsCommand(
    name: 'app:mail:adminIncident',
    description: 'Send a test reservation email'
)]
class AdminIncident extends Command
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
        
        $currentOrganizer = new User();
        $currentOrganizer->setFirstname("Le Organizer");
        $currentOrganizer->setLastname("Le Lastunamu");
        $currentOrganizer->setPassword("hmmm");
        $currentOrganizer->setRoles([ "ROLE_USER" ]);
        $currentOrganizer->setEmail('sokomiano@gmail.com');
        $currentOrganizer->setBirthdate(new \DateTime()->setDate(2002,3,13));


        $currentEvent = new Event();

        $currentEvent->setName("Japan Expo 2026");
        $currentEvent->setCity("Villepinte");
        $currentEvent->setCountry("France");
        $currentEvent->setCreatedAt(new \DateTimeImmutable()->setDate(2025,9,1));
        $currentEvent->setDescription("Japan Expo est LE rendez-vous des amoureux du Japon et de sa culture, du manga aux arts martiaux, du jeu vidéo au folklore nippon, de la J-music à la musique traditionnelle : un évènement incontournable pour tous ceux qui s’intéressent à la culture japonaise et une infinité de découvertes pour les curieux. Le tout à 30 minutes de Paris !");
        $currentEvent->setEventDate(new \DateTimeImmutable()->setDate(2026,7,9));
        $currentEvent->setOrganizer($currentUser);
        $currentEvent->setPostalCode("93420");
        $currentEvent->setPrice(28.8);
        $currentEvent->setRegistrationEndAt(new \DateTimeImmutable()->setDate(2026,7,2));
        $currentEvent->setRegistrationStartAt(new \DateTimeImmutable()->setDate(2025,10,31));
        $currentEvent->setStreet("ZAC Paris Nord 2");
        $currentEvent->setStreetNumber(null);
        $currentEvent->setType(EventType::FESTIVAL);
        $currentEvent->setVenueName("Parc des Expositions de Villepinte");
        

        $currentReservation = new Reservation();
        $currentReservation->setEvent($currentEvent);
        $currentReservation->setCreatedAt(new \DateTimeImmutable());
        $currentReservation->setSeatQuantity(2);
        $currentReservation->setUser($currentUser);
        
        $this->mailService->send(MailMessage::adminIncident($currentUser, $currentReservation));

        $output->writeln('<info>Email dispatched successfully</info>');

        return Command::SUCCESS;
    }
}
