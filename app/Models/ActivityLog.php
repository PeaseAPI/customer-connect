<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Context;

class ActivityLog extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'log_name', 'description',
        'subject_type', 'subject_id', 'event', 'properties',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 记录Activity log
     */
    public static function log(
        string $logName,
        string $description,
        ?Model $subject = null,
        string $event = null,
        array $properties = []
    ): self {
        $log = new static([
            'company_id' => Context::get('current_company_id'),
            'user_id' => auth()->id(),
            'log_name' => $logName,
            'description' => $description,
            'event' => $event,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if ($subject) {
            $log->subject_type = get_class($subject);
            $log->subject_id = $subject->getKey();
        }

        $log->save();

        return $log;
    }

    /**
     * 便捷方法：记录创建
     */
    public static function logCreated(Model $subject, string $description = null): self
    {
        return static::log(
            static::guessLogName($subject),
            $description ?? 'Created ' . class_basename($subject),
            $subject,
            'created',
            ['new' => $subject->getAttributes()]
        );
    }

    /**
     * 便捷方法：记录更新
     */
    public static function logUpdated(Model $subject, array $changes = null): self
    {
        return static::log(
            static::guessLogName($subject),
            'Updated ' . class_basename($subject),
            $subject,
            'updated',
            ['changes' => $changes ?? $subject->getChanges()]
        );
    }

    /**
     * 便捷方法：记录删除
     */
    public static function logDeleted(Model $subject): self
    {
        return static::log(
            static::guessLogName($subject),
            'Deleted ' . class_basename($subject),
            $subject,
            'deleted',
            ['old' => $subject->getAttributes()]
        );
    }

    protected static function guessLogName(Model $subject): string
    {
        $classMap = [
            'Invoice' => 'finance',
            'Payment' => 'finance',
            'Expense' => 'finance',
            'Proposal' => 'finance',
            'Estimate' => 'finance',
            'CreditNote' => 'finance',
            'Order' => 'finance',
            'Client' => 'crm',
            'Lead' => 'crm',
            'Deal' => 'crm',
            'Project' => 'pm',
            'Task' => 'pm',
            'Milestone' => 'pm',
            'Contract' => 'contract',
            'Employee' => 'hrm',
            'Leave' => 'hrm',
            'Attendance' => 'hrm',
            'Ticket' => 'ticket',
        ];

        $className = class_basename($subject);
        return $classMap[$className] ?? 'system';
    }
}
