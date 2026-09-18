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
            self::Open => '待处理',
            self::Closed => '已关闭',
            self::Draft => '草稿',
        };
    }
}