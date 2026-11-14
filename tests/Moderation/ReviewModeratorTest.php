<?php

namespace App\Tests\Moderation;

use App\Entity\Review;
use App\Moderation\ModerationVerdict;
use App\Moderation\ReviewModerator;
use App\Repository\ReviewRepository;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ReviewModeratorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testReviewIsApprovedWhenClean(): void
    {
        $reviewRepositoryMock = $this->createMock(ReviewRepository::class);
        $moderator = new ReviewModerator($reviewRepositoryMock);
        $review = new Review();
        $review->setComment('Чудова поїздка, все дуже сподобалося!');

        $verdict = $moderator->moderate($review);

        $this->assertSame(ModerationVerdict::Approved, $verdict, 'Відгук схвалений');
    }

    /**
     * @throws Exception
     */
    public function testReviewIsRejectedWhenContainsForbiddenWords(): void
    {
        $reviewRepositoryMock = $this->createMock(ReviewRepository::class);
        $moderator = new ReviewModerator($reviewRepositoryMock);
        $review = new Review();
        $review->setComment('Це повний спам та казино!');

        $verdict = $moderator->moderate($review);

        $this->assertSame(ModerationVerdict::Rejected, $verdict, 'Відгук із забороненими словами має бути відхилений');
    }

    /**
     * @throws Exception
     */
    public function testReviewIsRejectedWhenCapslockIsTooHigh(): void
    {
        $reviewRepositoryMock = $this->createMock(ReviewRepository::class);
        $moderator = new ReviewModerator($reviewRepositoryMock);

        $review = new Review();
        $review->setComment('ЖАХЛИВИЙ СЕРВІС ВЗАГАЛІ НЕ РЕКОМЕНДУЮ НІКОМУ');

        $verdict = $moderator->moderate($review);

        $this->assertSame(ModerationVerdict::Rejected, $verdict);
    }
}
