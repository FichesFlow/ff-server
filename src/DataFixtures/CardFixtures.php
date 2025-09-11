<?php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\CardBlock;
use App\Entity\CardSide;
use App\Enum\CardSides;
use App\Repository\DeckRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CardFixtures extends Fixture
{
    protected $faker;
    private const CARD_REFERENCE = 'card';
    private const CARD_SIDE_REFERENCE = 'card_side';
    private const CARD_BLOCK_REFERENCE = 'card_block';

    public function __construct(DeckRepository $deckRepository)
    {
        $this->faker = Factory::create();
        $this->deckRepository = $deckRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $decks = $this->deckRepository->findAll();
        $cardCount = 0;
        $cardSideCount = 0;
        $cardBlockCount = 0;
        $sides = [CardSides::FRONT, CardSides::BACK];

        foreach ($decks as $deck) {
            for ($i = 0; $i < 20; $i++) {
                $card = new Card();
                $card->setDeck($deck);
                $deck->setCardCount($deck->getCardCount() + 1);
                $card->setPosition($i);

                foreach ($sides as $side) {
                    $cardSide = new CardSide();
                    $cardSide->setCard($card);
                    $cardSide->setSide($side);

                    $manager->persist($cardSide);
                    $this->addReference(self::CARD_SIDE_REFERENCE . '_' . $cardSideCount, $cardSide);
                    $cardSideCount++;

                    $cardBlock = new CardBlock();
                    $cardBlock->setCardSide($cardSide);
                    $cardBlock->setContent($this->faker->sentence(10));

                    $manager->persist($cardBlock);
                    $this->addReference(self::CARD_BLOCK_REFERENCE . '_' . $cardBlockCount, $cardBlock);
                    $cardBlockCount++;
                }

                $manager->persist($card);
                $this->addReference(self::CARD_REFERENCE . '_' . $cardCount, $card);
                $cardCount++;
            }
        }

        $manager->flush();
    }
}