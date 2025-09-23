<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\Deck;
use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\ReviewProgressRepository;
use DateTime;

readonly class ReviewStats
{
    public function __construct(
        private ReviewProgressRepository $reviewProgressRepository,
        private CardRepository           $cardRepository
    )
    {

    }

    /**
     * Get review statistics for a deck, including due cards and their review status.
     *
     * @param Card[] $cards
     * @param User $user
     * @return array
     */
    public function getCardsWithReviewStatus(array $cards, User $user): array
    {
        $result = [];
        foreach ($cards as $card) {
            $cardData = $card;
            if (isset($card['id'])) {
                $cardData['reviewStatus'] = $this->getCardReviewStatus($card['id'], $user);
            }
            $result[] = $cardData;
        }
        return $result;
    }

    public function getCardReviewStatus(string $cardId, User $user): string
    {
        $reviewProgress = $this->reviewProgressRepository->findOneByUserAndCard($user, $cardId);

        if (!$reviewProgress) {
            return 'unseen';
        }

        if ($reviewProgress->getDueAt() <= new DateTime()) {
            return 'due';
        }

        return 'not_due';
    }

    /**
     * Get review statistics for a deck.
     *
     * @param Deck $deck
     * @param User $user
     * @return array
     */
    public function getDeckReviewStats(Deck $deck, User $user): array
    {
        $dueCount = $this->reviewProgressRepository->countDueCardsForDeck($deck, $user);
        $unseenCount = $this->cardRepository->countUnseenCardsInDeck($deck, $user);

        return [
            'dueCount' => $dueCount,
            'unseenCount' => $unseenCount
        ];
    }
}
