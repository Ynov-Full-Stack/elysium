<?php

namespace App\Command;

use App\Entity\Reservation;
use App\Mail\MailMessage;
use App\Mail\MailService;
use App\Repository\ReservationRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:mail:reservation-reminders',
    description: 'Send reservation reminder emails'
)]
final class ReminderCommand extends Command {

    public function __construct(
        private ReservationRepository $reservationRepository,
        private MailService $mailService,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reminderOffsets = [
            'P3D'  => new \DateInterval('P3D'),
            'P2D'  => new \DateInterval('P2D'),
            'P1D'  => new \DateInterval('P1D'),
            'PT6H' => new \DateInterval('PT6H'),
        ];


        foreach ($reminderOffsets as $key => $interval) {
            $targetTime = new \DateTimeImmutable()->add($interval);

            $start = $targetTime->sub(new \DateInterval("PT5M"))->format('Y-m-d H:i:s');
            $end   = $targetTime->add(new \DateInterval("PT5M"))->format('Y-m-d H:i:s');
            $output->writeln("<info>Checking reservations' events from ".$start." to ".$end."</info>");
            
            $reservations = array_filter(
                $this->reservationRepository->findForReminder($interval, $key),
                fn (Reservation $r) => !$r->hasReminderBeenSent($key)
            );

            foreach ($reservations as $reservation) {
                $output->writeln("<info>Reservation no.".$reservation->getId()." sended to ".$reservation->getUser()->getEmail()."</info>");

                $this->mailService->send(MailMessage::reservationReminder($reservation->getUser(),$reservation));

                $reservation->addSentReminderOffset($key);
            }
        }
        $this->em->flush();

        return Command::SUCCESS;
    }
}