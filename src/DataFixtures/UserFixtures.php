<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    protected UserPasswordHasherInterface $passwordHasher;
    protected $faker;
    private const USER_REFERENCE = 'user';

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($this->faker->email());
            $password = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($password);
            $user->setUserName($this->faker->userName());
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker->datetime()));
            $manager->persist($user);
            $manager->flush();

            $this->addReference(self::USER_REFERENCE . '_' . $i, $user);
        }
    }
}