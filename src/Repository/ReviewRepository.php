<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function getPaginationPaginated(Trip $trip, int $offset, int $limit): array
    {
        return $this->findBy(
            ['trip' => $trip],
            ['createdAt' => 'ASC'],
            $limit,
            $offset
        );
    }

    public function countByTrip(Trip $trip): int
    {
        return $this->count(['trip' => $trip]);
    }
}
