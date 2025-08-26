<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

final class MineDecksFilter extends AbstractFilter
{
    public function __construct(
        private Security $security,
        RequestStack $requestStack
    )
    {
        parent::__construct($requestStack);
    }

    // A filter to get only the decks owned by the current user.
    public function filterProperty(
        string                      $property, 
                                    $value, 
        QueryBuilder                $queryBuilder,
        string                      $resourceClass
        ): void
    {
        // Otherwise filter is applied to order and page as well
        if (
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }
        
        // Only apply filter if the property is 'mine' and the value is '1'
        if ('mine' !== $property || '1' !== $value) {
            return;
        }

        // Get the current user and filter decks by owner
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $currentUser = $this->security->getUser();
        $queryBuilder
            ->andWhere(sprintf('%s.owner = :currentUser', $rootAlias))
            ->setParameter('currentUser', $currentUser);
    }

    // This function is only used to hook in documentation generators (supported by Swagger and Hydra).
    public function getDescription(string $resourceClass): array
    {
        return [
            'property' => 'mine',
            'type' => 'string',
            'required' => false,
            'description' => 'A filter to get only the decks owned by the current user. Use "mine=1" to apply this filter.',
        ];
    }
}

?>