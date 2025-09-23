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
                ->setRepetitions(0)
                ->setDueAt((clone $when)->modify('+1 day'))
                ->setTotalReviews(0);
            $isNew = true;
        }

        // Get current values
        $n = $progress->getRepetitions();
        $ef = $progress->getEasiness();
        $interval = $progress->getIntervalDays();

        // Apply SM-2 algorithm
        if ($score >= 3) { // Correct response
            if ($n == 0) {
                $interval = 1;
            } elseif ($n == 1) {
                $interval = 6;
            } else {
                $interval = round($interval * $ef);
            }
            $n++; // Increment repetition number
        } else { // Incorrect response
            $n = 0;
            $interval = 1;
        }

        // Update easiness factor
        $ef = $ef + (0.1 - (5 - $score) * (0.08 + (5 - $score) * 0.02));
        if ($ef < 1.3) {
            $ef = 1.3;
        }

        // Calculate due date
        $dueAt = (clone $when)->modify("+{$interval} days");

        // Update all fields
        $progress
            ->setRepetitions($n)
            ->setEasiness($ef)
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

    public function isNewProgress(User $user, Card $card): bool
    {
        return $this->repository->findOneByUserAndCard($user, $card) === null;
    }
}
