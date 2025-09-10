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

        foreach ($decks as $deck) {
            for ($i = 0; $i < 20; $i++) {
                $card = new Card();
                $card->setDeck($deck);
                $deck->setCardCount($deck->getCardCount() + 1);
                $card->setPosition($i);

                $cardSideFront = new CardSide();
                $cardSideFront->setCard($card);
                $cardSideFront->setSide(CardSides::FRONT);

                $this->addReference(self::CARD_SIDE_REFERENCE . '_' . $cardSideCount, $cardSideFront);
                $cardSideCount++;

                $cardBlockFront = new CardBlock();
                $cardBlockFront->setCardSide($cardSideFront);
                $cardBlockFront->setContent($this->faker->sentence(10));

                $this->addReference(self::CARD_BLOCK_REFERENCE . '_' . $cardBlockCount, $cardBlockFront);
                $cardBlockCount++;

                $cardSideBack = new CardSide();
                $cardSideBack->setCard($card);
                $cardSideBack->setSide(CardSides::BACK);

                $this->addReference(self::CARD_SIDE_REFERENCE . '_' . $cardSideCount, $cardSideBack);
                $cardSideCount++;

                $cardBlockBack = new CardBlock();
                $cardBlockBack->setCardSide($cardSideBack);
                $cardBlockBack->setContent($this->faker->sentence(10));

                $this->addReference(self::CARD_BLOCK_REFERENCE . '_' . $cardBlockCount, $cardBlockBack);
                $cardBlockCount++;

                $manager->persist($card);
                $manager->persist($cardSideFront);
                $manager->persist($cardBlockFront);
                $manager->persist($cardSideBack);
                $manager->persist($cardBlockBack);

                $this->addReference(self::CARD_REFERENCE . '_' . $cardCount, $card);
                $cardCount++;
            }
        }

        $manager->flush();
    }
}