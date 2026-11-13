<?php

namespace App\Moderation;

use App\Entity\Review;
use App\Repository\ReviewRepository;

final class ReviewModerator
{
    private const STOP_WORDS = ['spam', 'casino', 'viagra', 'казино', 'ставки'];
    private const MAX_LINKS = 1;

    public function __construct(private ReviewRepository $reviews) {}

    public function moderate(Review $review): ModerationVerdict
    {
        $text = mb_strtolower((string) $review->getComment());

        // стоп слови
        foreach (self::STOP_WORDS as $word) {
            if (str_contains($text, $word)) {
                return ModerationVerdict::Rejected;
            }
        }

        // посилання
        if (preg_match_all('~https?://|www\.~i', $text) > self::MAX_LINKS) {
            return ModerationVerdict::Rejected;
        }

        // текст capslock
        $original = (string) $review->getComment();
        $letters = preg_replace('/[^\p{L}]/u', '', $original);
        if (mb_strlen($letters) > 15) {
            $upper = preg_replace('/[^\p{Lu}]/u', '', $letters);
            if (mb_strlen($upper) / mb_strlen($letters) > 0.7) {
                return ModerationVerdict::Rejected;
            }
        }

        return ModerationVerdict::Approved;
    }
}
