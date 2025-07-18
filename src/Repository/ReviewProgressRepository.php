<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\Deck;
use App\Entity\ReviewProgress;
use App\Entity\User;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReviewProgress>
 */
class ReviewProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReviewProgress::class);
    }

    public function findOneByUserAndCard(User $user, Card $card): ?ReviewProgress
    {
        return $this->findOneBy([
            'reviewer' => $user,
            'card' => $card,
        ]);
    }

    public function save(ReviewProgress $progress, bool $flush = false): void
    {
        $this->getEntityManager()->persist($progress);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findDueForUser(User $user, DateTimeInterface $beforeDate = null, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('rp')
            ->andWhere('rp.reviewer = :user')
            ->setParameter('user', $user);

        if ($beforeDate) {
            $qb->andWhere('rp.due_at <= :beforeDate')
                ->setParameter('beforeDate', $beforeDate);
        }

        $qb->orderBy('rp.due_at', 'ASC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function countDueForUser(User $user, DateTimeInterface $beforeDate = null): int
    {
        $qb = $this->createQueryBuilder('rp')
            ->select('COUNT(rp.id)')
            ->andWhere('rp.reviewer = :user')
            ->setParameter('user', $user);

        if ($beforeDate) {
            $qb->andWhere('rp.due_at <= :beforeDate')
                ->setParameter('beforeDate', $beforeDate);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findDueForUserInDeck(User $user, Deck $deck, DateTimeInterface $beforeDate = null, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('rp')
            ->join('rp.card', 'c')
            ->andWhere('rp.reviewer = :user')
            ->andWhere('c.deck = :deck')
            ->setParameter('user', $user)
            ->setParameter('deck', $deck);

        if ($beforeDate) {
            $qb->andWhere('rp.due_at <= :beforeDate')
               ->setParameter('beforeDate', $beforeDate);
        }

        $qb->orderBy('rp.due_at', 'ASC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function countDueForUserInDeck(User $user, Deck $deck, DateTimeInterface $beforeDate = null): int
    {
        $qb = $this->createQueryBuilder('rp')
            ->select('COUNT(rp.id)')
            ->join('rp.card', 'c')
            ->andWhere('rp.reviewer = :user')
            ->andWhere('c.deck = :deck')
            ->setParameter('user', $user)
            ->setParameter('deck', $deck);

        if ($beforeDate) {
            $qb->andWhere('rp.due_at <= :beforeDate')
                ->setParameter('beforeDate', $beforeDate);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countDueForUserGroupedByDeck(User $user, DateTimeInterface $beforeDate = null): array
    {
        $qb = $this->createQueryBuilder('rp')
            ->select('d.id as deck_id, d.title as deck_name, COUNT(rp.id) as count')
            ->join('rp.card', 'c')
            ->join('c.deck', 'd')
            ->andWhere('rp.reviewer = :user')
            ->setParameter('user', $user)
            ->groupBy('d.id, d.title')
            ->orderBy('d.title', 'ASC');

        if ($beforeDate) {
            $qb->andWhere('rp.due_at <= :beforeDate')
                ->setParameter('beforeDate', $beforeDate);
        }

        $results = $qb->getQuery()->getResult();

        // Transform to a more convenient format
        return array_map(fn($result) => [
            'deck_id' => $result['deck_id'],
            'deck_name' => $result['deck_name'],
            'count' => (int) $result['count']
        ], $results);
    }

    public function countDueCardsForDeck(Deck $deck, User $user): int
    {
        return $this->createQueryBuilder('rp')
            ->select('COUNT(rp.id)')
            ->join('rp.card', 'c')
            ->where('rp.reviewer = :user')
            ->andWhere('c.deck = :deck')
            ->andWhere('rp.due_at <= :now')
            ->setParameter('user', $user)
            ->setParameter('deck', $deck)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
