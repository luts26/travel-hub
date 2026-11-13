<?php

namespace App\Enum;

enum ReviewStatus: string
{
    case Submitted = 'submitted';
    case Published = 'published';
    case Rejected  = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'На перевірці',
            self::Published => 'Опубліковано',
            self::Rejected  => 'Відхилено',
        };
    }
}
