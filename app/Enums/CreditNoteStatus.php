<?php

namespace App\Enums;

enum CreditNoteStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Draft = 'draft';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Pending',
            self::Closed => 'Closed',
            self::Draft => 'Draft',
        };
    }
}