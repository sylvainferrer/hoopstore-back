<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\Roles;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Crée 5 utilisateurs "classiques"
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setLastname($faker->lastName());
            $user->setFirstname($faker->firstName());
            $user->setBirthday(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-60 years', '-18 years')));
            $user->setAdresse($faker->streetAddress());
            $user->setCodePostal($faker->postcode());
            $user->setVille($faker->city());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setRole('ROLE_USER');

            // Hash du mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);

            $manager->persist($user);
        }

        // Ajoute un administrateur
        $admin = new User();
        $admin->setLastname('Admin');
        $admin->setFirstname('Super');
        $admin->setBirthday(new \DateTimeImmutable('1980-01-01'));
        $admin->setAdresse('1 rue de l’Admin');
        $admin->setCodePostal('75000');
        $admin->setVille('Paris');
        $admin->setEmail('jd@example.com');
        $admin->setRole('ROLE_SUPER_ADMIN');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, '123'));
        $manager->persist($admin);

        $manager->flush();
    }
}