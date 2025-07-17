<?php

namespace App\Repository;

use App\Entity\Card;
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

    public function findDueForUser(User $user, DateTimeInterface $beforeDate = null): array
    {
        $qb = $this->createQueryBuilder('rp')
            ->andWhere('rp.reviewer = :user')
            ->setParameter('user', $user);

        if ($beforeDate) {
            $qb->andWhere('rp.due_at <= :beforeDate')
                ->setParameter('beforeDate', $beforeDate);
        }

        return $qb->orderBy('rp.due_at', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
