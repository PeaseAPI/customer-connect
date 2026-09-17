<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '待审批',
            self::Approved => '已审批',
            self::Declined => '已驳回',
        };
    }
}
