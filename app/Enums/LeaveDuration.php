<?php

namespace App\Enums;

enum LeaveDuration: string
{
    case Full = 'full';
    case HalfFirst = 'half_first';
    case HalfSecond = 'half_second';

    public function label(): string
    {
        return match ($this) {
            self::Full => 'Full Day',
            self::HalfFirst => 'Morning',
            self::HalfSecond => 'Afternoon',
        };
    }

    /**
     * Number of leave days consumed by this duration.
     */
    public function days(): float
    {
        return match ($this) {
            self::Full => 1.0,
            self::HalfFirst, self::HalfSecond => 0.5,
        };
    }
}
