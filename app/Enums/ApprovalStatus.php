<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '待审批',
            self::Approved => '已通过',
            self::Rejected => '已驳回',
            self::Canceled => '已撤销',
        };
    }
}
