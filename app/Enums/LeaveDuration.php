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
}
