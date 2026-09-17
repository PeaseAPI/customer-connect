<?php

namespace App\Enums;

enum MilestoneStatus: string
{
    case Incomplete = 'incomplete';
    case Complete = 'complete';

    public function label(): string
    {
        return match ($this) {
            self::Incomplete => '未完成',
            self::Complete => '已完成',
        };
    }
}
