<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Sent => '已发送',
            self::Accepted => '已接受',
            self::Declined => '已拒绝',
            self::Expired => '已过期',
        };
    }
}