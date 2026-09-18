<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case NotStarted = 'not_started';
    case Planning = 'planning';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Canceled = 'canceled';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::Planning => 'Planning',
            self::InProgress => 'In Progress',
            self::OnHold => 'Suspended',
            self::Completed => 'Completed',
            self::Canceled => 'Canceled',
            self::Finished => 'Finished',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NotStarted => '#C0C4CC',
            self::Planning => '#909399',
            self::InProgress => '#409EFF',
            self::OnHold => '#E6A23C',
            self::Completed => '#67C23A',
            self::Canceled => '#F56C6C',
            self::Finished => '#6F42C1',
        };
    }
}
