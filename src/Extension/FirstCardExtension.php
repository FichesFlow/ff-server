<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Deck;
use App\Enum\CardSides;
use Doctrine\ORM\QueryBuilder;

final class FirstCardExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    
    public function applyToCollection(
        QueryBuilder                $queryBuilder, 
        QueryNameGeneratorInterface $queryNameGenerator, 
        string                      $resourceClass, 
        ?Operation                  $operation = null, 
        array                       $context = []
    ) : void
    {
        $this->addWhere($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public function applyToItem(
        QueryBuilder                $queryBuilder, 
        QueryNameGeneratorInterface $queryNameGenerator, 
        string                      $resourceClass, 
        array                       $identifiers, 
        ?Operation                  $operation = null, 
        array                       $context = []
    ): void
    {
        $this->addWhere($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public function addWhere(
        QueryBuilder                $queryBuilder, 
        QueryNameGeneratorInterface $queryNameGenerator, 
        string                      $resourceClass
    ): void
    {
        if (Deck::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $subquery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('cb.content')
            ->from('App\Entity\CardBlock', 'cb')
            ->join('cb.card_side', 'cs')
            ->join('cs.card', 'c')
            ->where('c.deck = ' . $rootAlias . '.id')
            ->andWhere('c.position = 0')
            ->andWhere('cs.side = \'' . CardSides::FRONT->value . '\'')
            ->setMaxResults(1)
            ->getDQL()
        ;

        $queryBuilder->addSelect('(' . $subquery . ') AS firstCardFront');
    }
}
