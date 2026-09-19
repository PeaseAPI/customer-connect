<?php

namespace App\Console\Commands;

use App\Models\EmployeeDetail;
use App\Models\User;
use App\Notifications\EmployeeReminderNotification;
use Illuminate\Console\Command;

class EmployeeDocumentExpiryReminder extends Command
{
    protected $signature = 'cc:employee-document-expiry';
    protected $description = 'Remind about expiring employee documents';

    public function handle(): int
    {
        $limit = now()->addDays(30)->toDateString();

        $docs = \DB::table('employee_documents')
            ->whereDate('expiry_date', '<=', $limit)
            ->get();

        $reminded = 0;
        foreach ($docs as $doc) {
            $detail = EmployeeDetail::find($doc->employee_detail_id);
            $user = ($detail && $detail->user_id) ? User::find($detail->user_id) : null;
            if (! $user) {
                continue;
            }

            $user->notify(new EmployeeReminderNotification('员工证件到期提醒', [
                'document_name' => $doc->document_name,
                'document_type' => $doc->document_type,
                'expiry_date' => (string) $doc->expiry_date,
            ]));
            $reminded++;
        }

        $this->info("Employee document expiry reminders sent: {$reminded}");
        return self::SUCCESS;
    }
}
