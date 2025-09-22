<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private const USER_REFERENCE = 'user';
    private const ADMIN_REFERENCE = 'admin';
    protected UserPasswordHasherInterface $passwordHasher;
    protected Generator $faker;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        // Create an admin user first
        $this->createAdminUser($manager);

        // Create regular users
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($this->faker->email());
            $password = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($password);
            $user->setUserName($this->faker->userName());
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->datetime()));
            $manager->persist($user);
            $manager->flush();

            $this->addReference(self::USER_REFERENCE . '_' . $i, $user);
        }
    }

    private function createAdminUser(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@mail.com');
        $admin->setUserName('admin');
        $password = $this->passwordHasher->hashPassword($admin, 'admin');
        $admin->setPassword($password);
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $admin->setScore(5000);
        $admin->setCreatedAt(new DateTimeImmutable());

        $manager->persist($admin);
        $manager->flush();

        $this->addReference(self::ADMIN_REFERENCE, $admin);
    }
}
