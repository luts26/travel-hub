<?php

namespace App\MessageHandler;

use App\Enum\ReviewStatus;
use App\Message\ReviewSubmitted;
use App\Moderation\ModerationVerdict;
use App\Moderation\ReviewModerator;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ReviewSubmittedHandler
{
    public function __construct(
        private ReviewRepository $reviews,
        private EntityManagerInterface $em,
        private CacheItemPoolInterface $cachePool,
        private ReviewModerator $moderator,
    ) {}

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __invoke(ReviewSubmitted $message): void
    {
        $review = $this->reviews->find($message->reviewId);
        if (!$review || $review->getStatus() !== ReviewStatus::Submitted) {
            return;
        }

        $verdict = $this->moderator->moderate($review);

        $review->setStatus(
            $verdict === ModerationVerdict::Approved
                ? ReviewStatus::Published
                : ReviewStatus::Rejected
        );

        $this->em->flush();

        if ($verdict === ModerationVerdict::Approved) {
            $this->cachePool->deleteItem('trip_stats_' . $review->getTrip()->getId());
        }
    }
}
