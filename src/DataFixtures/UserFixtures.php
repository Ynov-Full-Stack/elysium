<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    /**
     * @throws \DateMalformedStringException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Admin
        $admin = new User();
        $admin->setEmail('admin@events.SHOULDNOTEXIST.fr');
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setLastname('Super');
        $admin->setFirstname('Admin');
        $admin->setBirthdate(new \DateTime('-30 years'));
        $manager->persist($admin);

        // Organizers Lyon
        $orgData = [
            ['organizer@lyon.SHOULDNOTEXIST.fr', 'orga123', 'Dupont', 'Marie', '-35 years'],
            ['eventpro@events.SHOULDNOTEXIST.fr', 'event123', 'Martin', 'Pierre', '-40 years'],
            ['tech@conf.SHOULDNOTEXIST.fr', 'tech123', 'Bernard', 'Sophie', '-28 years'],
        ];

        foreach ($orgData as $data) {
            $org = new User();
            $org->setEmail($data[0]);
            $org->setPassword($this->hasher->hashPassword($org, $data[1]));
            $org->setRoles(['ROLE_USER']);
            $org->setLastname($data[2]);
            $org->setFirstname($data[3]);
            $org->setBirthdate(new \DateTime($data[4]));
            $manager->persist($org);
        }

        // 20 users random
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@test.SHOULDNOTEXIST.fr');
            $user->setPassword($this->hasher->hashPassword($user, 'user123'));
            $user->setRoles(['ROLE_USER']);
            $user->setLastname($faker->lastName);
            $user->setFirstname($faker->firstName);
            $user->setBirthdate($faker->dateTimeBetween('-60 years', '-18 years'));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
