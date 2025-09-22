<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getLeaderboard(string $period, int $page, int $size): array
    {
        $offset = ($page - 1) * $size;

        $queryBuilderUser = $this->createQueryBuilder('u')
            ->select('u.id AS user')
            ->addSelect('u.username')
            ->addSelect('u.avatar_url AS avatar')
            ->addSelect('SUM(e.value) AS score')
            ->join('u.scoreEvents', 'e')
            ->groupBy('u.id')
            ->addgroupBy('e.value')
            ->orderBy('e.value', 'DESC')
            ->addOrderBy('u.username', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($size)
        ;

        switch ($period) {
            case 'day':
                $period = new \DateTimeImmutable('today midnight')->format('c');

                $queryBuilderUser->andWhere('e.created_at >= :d')
                    ->setParameter('d', $period)
                ;

                $queryBuilderTotal = $this->createQueryBuilder('u')
                    ->select('COUNT(DISTINCT u.id)')
                    ->join('u.scoreEvents', 'e')
                    ->andWhere('e.created_at >= :d')
                    ->setParameter('d', $period)
                ;
                break;
            case 'week':
                $period = new \DateTimeImmutable('monday this week')->format('c');

                $queryBuilderUser->andWhere('e.created_at >= :d')
                    ->setParameter('d', $period)
                ;

                $queryBuilderTotal = $this->createQueryBuilder('u')
                    ->select('COUNT(DISTINCT u.id)')
                    ->join('u.scoreEvents', 'e')
                    ->andWhere('e.created_at >= :d')
                    ->setParameter('d', $period)
                ;
                break;
            case 'month':
                $period = new \DateTimeImmutable('first day of this month')->format('c');

                $queryBuilderUser->andWhere('e.created_at >= :d')
                    ->setParameter('d', $period)
                ;

                $queryBuilderTotal = $this->createQueryBuilder('u')
                    ->select('COUNT(DISTINCT u.id)')
                    ->join('u.scoreEvents', 'e')
                    ->andWhere('e.created_at >= :d')
                    ->setParameter('d', $period)
                ;
                break;
            case 'year':
                $period = new \DateTimeImmutable('first day of january')->format('c');

                $queryBuilderUser->andWhere('e.created_at >= :d')
                    ->setParameter('d', $period)
                ;

                $queryBuilderTotal = $this->createQueryBuilder('u')
                    ->select('COUNT(DISTINCT u.id)')
                    ->join('u.scoreEvents', 'e')
                    ->andWhere('e.created_at >= :d')
                    ->setParameter('d', $period)
                ;
                break;
            case 'all':
                $queryBuilderTotal = $this->createQueryBuilder('u')
                    ->select('COUNT(DISTINCT u.id)')
                    ->join('u.scoreEvents', 'e')
                ;
        }

        $entries = $queryBuilderUser->getQuery()
            ->getResult()
        ;

        $total = $queryBuilderTotal->getQuery()
            ->getResult()[0][1]
        ;

        foreach ($entries as $k => &$row) {
            $row['rank'] = $offset + $k + 1;
        }
    
        return [$entries, $total];
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
