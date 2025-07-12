<?php

namespace App\Service;

use App\Entity\ScoreEvent;
use App\Entity\User;
use App\Enum\ScoreEventType;
use Doctrine\ORM\EntityManagerInterface;

readonly class ScoreLogger
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    /**
     * Log multiple score events for a user
     *
     * @return ScoreEvent[]
     */
    public function logMultiple(User $user, array $events): array
    {
        $scoreEvents = [];

        foreach ($events as $event) {
            $type = $event['type'];
            $value = $event['value'] ?? null;

            $scoreEvents[] = $this->log($user, $type, $value);
        }

        return $scoreEvents;
    }

    /**
     * Log a score event for a user
     */
    public function log(User $user, ScoreEventType $type, ?int $value = null): ScoreEvent
    {
        // Use default points if value is not provided
        $points = $value ?? $type->getDefaultPoints();

        $scoreEvent = new ScoreEvent();
        $scoreEvent->setScorer($user);
        $scoreEvent->setType($type);
        $scoreEvent->setValue($points);

        $this->entityManager->persist($scoreEvent);
        $this->entityManager->flush();

        return $scoreEvent;
    }

    /**
     * Calculate total score for a user
     */
    public function calculateTotalScore(User $user): int
    {
        return $this->entityManager->getRepository(ScoreEvent::class)
            ->createQueryBuilder('se')
            ->select('SUM(se.value)')
            ->where('se.scorer = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult() ?: 0;
    }
}
