<?php

namespace App\Enums;

enum EstimateRequestStatus: string
{
    case Pending = 'pending';
    case Converted = 'converted';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Converted => 'Converted',
            self::Declined => 'Declined',
        };
    }
}