<?php

namespace App\DataFixtures;

use App\Entity\Deck;
use App\Entity\Tag;
use App\Enum\CountryCodeAlpha2;
use App\Enum\DeckStatus;
use App\Enum\DeckVisibility;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class DeckFixtures extends Fixture
{
    protected $faker;
    private const DECK_REFERENCE = 'deck';

    public function __construct(UserRepository $userRepository)
    {
        $this->faker = Factory::create();
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->findAll();
        $deckCount = 0;
       
        foreach ($users as $user) {
            for ($i = 0; $i < 3; $i++) {
                $deck = new Deck();
                $deck->setOwner($user);
                $deck->setTitle($this->faker->sentence(3));
                $deck->setDescription($this->faker->paragraph());
                $deck->setLanguage(CountryCodeAlpha2::France);
                $deck->setVisibility(DeckVisibility::PUBLIC);
                $status = rand(0, 100);
                $numberTags = rand(1, 3);

                if ($status < 80) {
                    $deck->setStatus(DeckStatus::PUBLISHED);
                } else {
                    $deck->setStatus(DeckStatus::DRAFT);
                }

                for ($j = 0; $j < $numberTags; $j++) {
                    $tag = $this->getReference('tag_' . rand(0, 11), Tag::class);
                    $deck->addTag($tag);
                }

                $manager->persist($deck);
                $this->addReference(self::DECK_REFERENCE . '_' . $deckCount, $deck);
                $deckCount++;
            }
        }

        $manager->flush();
    }
}