<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    private CacheInterface $cache;

    public function __construct(ManagerRegistry $registry, CacheInterface $cache)
    {
        parent::__construct($registry, Review::class);
        $this->cache = $cache;
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
        $cacheKey = 'trip_review_count_' . $trip->getId();
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($trip) {
            $item->expiresAfter(3600);

            return $this->count(['trip' => $trip]);
        });
    }

    public function getAverageRatingForTrip(Trip $trip): float
    {
        $cacheKey = 'trip_avg_rating_' . $trip->getId();

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($trip) {

            $item->expiresAfter(3600);

            $result = $this->createQueryBuilder('r')
                ->select('AVG(r.rating) as avgRating')
                ->andWhere('r.trip = :trip')
                ->setParameter('trip', $trip)
                ->getQuery()
                ->getSingleScalarResult();

            return $result ? round((float) $result, 1) : 0.0;
        });
    }

    public function getStatsForTrips(array $trips): array
    {
        if (empty($trips)) {
            return [];
        }

        $tripIds = array_map(fn($trip) => $trip->getId(), $trips);

        $results = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.trip) as tripId', 'AVG(r.rating) as avgRating', 'COUNT(r.id) as reviewCount')
            ->andWhere('r.trip IN (:trips)')
            ->setParameter('trips', $tripIds)
            ->groupBy('r.trip')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($results as $row) {
            $stats[$row['tripId']] = [
                'avgRating' => round((float) $row['avgRating'], 1),
                'reviewCount' => (int) $row['reviewCount'],
            ];
        }

        return $stats;
    }

    public function clearTripCache(Trip $trip): void
    {
        $this->cache->delete('trip_avg_rating_' . $trip->getId());
        $this->cache->delete('trip_review_count_' . $trip->getId());
    }
}
