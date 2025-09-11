<?php

namespace App\DataFixtures;

use App\Entity\DeckRating;
use App\Repository\DeckRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RatingFixtures extends Fixture
{
    protected $faker;
    private const RATING_REFERENCE = 'rating';

    public function __construct(UserRepository $userRepository, DeckRepository $deckRepository)
    {
        $this->faker = Factory::create();
        $this->userRepository = $userRepository;
        $this->deckRepository = $deckRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->findAll();
        $decks = $this->deckRepository->findAll();
        $rated = []; // To track which user has rated which deck

        for ($i = 0; $i < 50; $i++) {
            $rater = $users[array_rand($users)];
            $deck = $decks[array_rand($decks)];
            $key = $rater->getId() . '-' . $deck->getId();

            // The user has already rated this deck
            while (isset($rated[$key])) {
                $deck = $decks[array_rand($decks)]; // Pick another deck
                $key = $rater->getId() . '-' . $deck->getId();
            }

            $rated[$key] = true; // Mark this user-deck pair as rated

            $rating = new DeckRating();
            $rating->setRater($rater);
            $rating->setDeck($deck);
            $deck->setRatingCount($deck->getRatingCount() + 1);
            $rating->setRating(rand(1, 5));

            $manager->persist($rating);
            $this->addReference(self::RATING_REFERENCE . '_' . $i, $rating);
        }

        $manager->flush();

        foreach ($decks as $deck) {
            $ratings = $deck->getDeckRatings();

            if (count($ratings) > 0) {
                $totalRating = 0;

                foreach ($ratings as $rating) {
                    $totalRating += $rating->getRating();
                }

                $averageRating = $totalRating / count($ratings);
                $deck->setRatingAvg($averageRating);
            } else {
                $deck->setRatingAvg(0);
            }
        }

        $manager->flush();
    }
}
