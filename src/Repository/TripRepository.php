<?php

namespace App\Repository;

use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trip>
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    public function findByFilters(?string $query): array
    {
        $db = $this->createQueryBuilder('t');

        if ($query !== null && trim($query) !== '') {
            $db->andWhere('t.title LIKE :query OR t.description LIKE :query OR t.city LIKE :query OR t.country LIKE :query')
                ->setParameter('query', '%' . trim($query) . '%');
        }

        return $db->getQuery()->getResult();
    }
}
