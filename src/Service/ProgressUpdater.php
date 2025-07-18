<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\ReviewProgress;
use App\Entity\User;
use App\Repository\ReviewProgressRepository;
use DateTime;
use DateTimeInterface;

readonly class ProgressUpdater
{
    private const array LADDER = [1, 3, 7, 15, 30]; // days

    public function __construct(
        private ReviewProgressRepository $repository
    )
    {
    }

    public function update(User $user, Card $card, int $score, DateTimeInterface $when): ReviewProgress
    {
        // Load or create progress record
        $progress = $this->repository->findOneByUserAndCard($user, $card);
        $isNew = false;

        if (!$progress) {
            $progress = new ReviewProgress()
                ->setReviewer($user)
                ->setCard($card)
                ->setEasiness(2.5)
                ->setIntervalDays(1)
                ->setDueAt((clone $when)->modify('+1 day'))
                ->setTotalReviews(0);
            $isNew = true;
        }

        // Determine current position in the ladder
        $current = $progress->getIntervalDays();
        $idx = $this->indexInLadder($current);

        // Apply rule based on score
        if ($score === 0) {
            // Reset to beginning
            $idx = 0;
            $progress->setEasiness(max(1.3, $progress->getEasiness() - 0.2));
        } else {
            // Advance one step
            $idx = min($idx + 1, count(self::LADDER) - 1);
            if ($score === 5) {
                $progress->setEasiness(min(2.5, $progress->getEasiness() + 0.1));
            }
            // Score 3 keeps easiness unchanged (neutral progress)
        }

        $interval = self::LADDER[$idx];
        $dueAt = (clone $when)->modify("+{$interval} days");

        // Update all fields
        $progress
            ->setIntervalDays($interval)
            ->setDueAt($dueAt)
            ->setLastReviewAt(
                $when instanceof DateTime ?
                    $when :
                    new DateTime($when->format('Y-m-d H:i:s'))
            )
            ->setLastScore($score)
            ->setTotalReviews($progress->getTotalReviews() + 1);

        // Persist if new entity
        if ($isNew) {
            $this->repository->save($progress);
        }

        return $progress;
    }

    private function indexInLadder(int $days): int
    {
        $idx = array_search($days, self::LADDER, true);
        return $idx === false ? 0 : $idx;
    }

    public function isNewProgress(User $user, Card $card): bool
    {
        return $this->repository->findOneByUserAndCard($user, $card) === null;
    }
}
