<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Reservation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $organizer = new \App\Entity\User();
        $organizer->setEmail('reserv-org@test.fr');
        $organizer->setPassword(password_hash('test', PASSWORD_DEFAULT));
        $organizer->setRoles(['ROLE_USER']);
        $organizer->setLastname('Org');
        $organizer->setFirstname('Reservation');
        $organizer->setBirthdate(new \DateTime('-30 years'));
        $manager->persist($organizer);
        $manager->flush($organizer);

        $event = new Event();
        $event->setName('Concert Test');
        $event->setDescription('Event pour réservations test');
        $event->setEventDate(new \DateTimeImmutable('2026-06-01'));
        $event->setCreatedAt(new \DateTimeImmutable());
        $event->setRegistrationStartAt(new \DateTimeImmutable('2026-05-01'));
        $event->setRegistrationEndAt(new \DateTimeImmutable('2026-05-29'));
        $event->setPrice(50.0);
        $event->setTotalSeats(100);
        $event->setOrganizer($organizer);
        $event->setType(\App\Enum\EventType::CONCERT);
        $event->setVenueName('Test Arena');
        $event->setCity('Lyon');
        $manager->persist($event);

        $users = [];
        for ($u = 0; $u < 3; $u++) {
            $user = new \App\Entity\User();
            $user->setEmail("reserv{$u}@test.fr");
            $user->setPassword(password_hash('test', PASSWORD_DEFAULT));
            $user->setRoles(['ROLE_USER']);
            $user->setLastname('User');
            $user->setFirstname("Test$u");
            $user->setBirthdate(new \DateTime('-30 years'));
            $manager->persist($user);
            $users[] = $user;
        }

        for ($i = 0; $i < 20; $i++) {
            $reservation = new Reservation();
            $reservation->setEvent($event);
            $reservation->setUser($users[$i % 3]);
            $reservation->setSeatQuantity($faker->numberBetween(1, 4));
            $reservation->setStatus($faker->randomElement(['en cours', 'annulé']));
            $manager->persist($reservation);
        }

        $manager->flush();
    }


    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            EventFixtures::class,
        ];
    }
}
