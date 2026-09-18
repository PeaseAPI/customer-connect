<?php

namespace App\Enums;

enum MilestoneStatus: string
{
    case Incomplete = 'incomplete';
    case Complete = 'complete';

    public function label(): string
    {
        return match ($this) {
            self::Incomplete => 'Incomplete',
            self::Complete => 'Completed',
        };
    }
}
