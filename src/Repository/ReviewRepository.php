<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\Trip;
use App\Enum\ReviewStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    private CacheItemPoolInterface $cache;

    public function __construct(ManagerRegistry $registry, CacheItemPoolInterface $cache)
    {
        parent::__construct($registry, Review::class);
        $this->cache = $cache;
    }

    public function getPaginationPaginated(Trip $trip, int $offset, int $limit): array
    {
        return $this->findBy(
            ['trip' => $trip, 'status' => ReviewStatus::Published],
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );
    }

    public function getTripStats(Trip $trip): array
    {
        return $this->getStatsForTrips([$trip])[$trip->getId()];
    }

    public function getStatsForTrips(array $trips): array
    {
        if (empty($trips)) {
            return [];
        }

        $stats = [];
        $keyToTripId = [];

        foreach ($trips as $trip) {
            $keyToTripId['trip_stats_' . $trip->getId()] = $trip->getId();
        }

        $missingIds = [];
        foreach ($this->cache->getItems(array_keys($keyToTripId)) as $key => $item) {
            if ($item->isHit()) {
                $stats[$keyToTripId[$key]] = $item->get();
            } else {
                $missingIds[] = $keyToTripId[$key];
            }
        }

        if ($missingIds) {
            $results = $this->createQueryBuilder('r')
                ->select('IDENTITY(r.trip) as tripId', 'AVG(r.rating) as avgRating', 'COUNT(r.id) as reviewCount')
                ->andWhere('r.trip IN (:trips)')
                ->setParameter('trips', $missingIds)
                ->andWhere('r.status = :status')
                ->setParameter('status', ReviewStatus::Published)
                ->groupBy('r.trip')
                ->getQuery()
                ->getResult();

            $dbStats = [];
            foreach ($results as $row) {
                $dbStats[(int) $row['tripId']] = [
                    'avgRating'   => $row['avgRating'] ? round((float) $row['avgRating'], 1) : 0.0,
                    'reviewCount' => (int) $row['reviewCount'],
                ];
            }

            foreach ($missingIds as $tripId) {
                $tripStat = $dbStats[$tripId] ?? ['avgRating' => 0.0, 'reviewCount' => 0];
                $stats[$tripId] = $tripStat;

                $item = $this->cache->getItem('trip_stats_' . $tripId);
                $item->set($tripStat)->expiresAfter(3600);
                $this->cache->saveDeferred($item);
            }

            $this->cache->commit();
        }

        return $stats;
    }

    public function clearTripCache(Trip $trip): void
    {
        $this->cache->deleteItem('trip_stats_' . $trip->getId());
    }
}
