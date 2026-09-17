<?php

namespace App\Enums;

enum CompanyStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => '正常',
            self::Inactive => '停用',
            self::Expired => '已过期',
        };
    }
}
