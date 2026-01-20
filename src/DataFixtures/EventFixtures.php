<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Enum\EventType;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $types = [
            EventType::CONCERT,
            EventType::SPECTACLE,
            EventType::CONFERENCE,
            EventType::WORKSHOP,
            EventType::FESTIVAL,
            EventType::THEATER,
            EventType::SPORT,
            EventType::EXHIBITION,
            EventType::PARTY,
            EventType::TRAINING,
            EventType::GALA,
            EventType::OTHER,
        ];

        $organizers = [];
        for ($i = 0; $i < 20; $i++) {
            $organizer = new User();
            $organizer->setEmail("org{$i}@events.fr");
            $organizer->setPassword(password_hash('dummy', PASSWORD_DEFAULT));
            $organizer->setRoles(['ROLE_USER']);
            $organizer->setLastname($faker->lastName);
            $organizer->setFirstname($faker->firstName);
            $organizer->setBirthdate(new \DateTime('-35 years'));
            $manager->persist($organizer);
            $organizers[] = $organizer;
        }
        $manager->flush();

        for ($i = 0; $i < 20; $i++) {
            $eventDate = $faker->dateTimeBetween('2026-03-01', '2026-12-31');
            $regStart = $faker->dateTimeBetween('2026-01-25', (clone $eventDate)->modify('-1 month'));
            $regEnd = $faker->dateTimeBetween($regStart, (clone $eventDate)->modify('-3 days'));

            $event = new Event();
            $event->setName($faker->sentence(3));
            $event->setDescription($faker->paragraph(3));
            $event->setCreatedAt(new \DateTimeImmutable());
            $event->setEventDate(\DateTimeImmutable::createFromMutable($eventDate));
            $event->setRegistrationStartAt(\DateTimeImmutable::createFromMutable($regStart));
            $event->setRegistrationEndAt(\DateTimeImmutable::createFromMutable($regEnd));
            $event->setPrice($faker->randomFloat(2, 10, 200));
            $event->setTotalSeats($faker->numberBetween(50, 5000));
            $event->setOrganizer($organizers[$i]);
            $event->setType($types[$i % count($types)]);
            $event->setVenueName($faker->company);
            $event->setStreetNumber($faker->buildingNumber);
            $event->setStreet($faker->streetName);
            $event->setPostalCode($faker->postcode);
            $event->setCity('Lyon');
            $event->setCountry('France');

            $manager->persist($event);
            $this->addReference('event-' . $i, $event);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            'App\DataFixtures\UserFixtures',
        ];
    }
}
