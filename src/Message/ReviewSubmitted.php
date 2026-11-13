<?php

namespace App\Message;

class ReviewSubmitted
{
    public function __construct(public readonly int $reviewId) {}
}
