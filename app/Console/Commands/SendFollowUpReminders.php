<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\User;
use App\Notifications\EmployeeReminderNotification;
use Illuminate\Console\Command;

class SendFollowUpReminders extends Command
{
    protected $signature = 'cc:follow-up-reminders';
    protected $description = 'Send follow-up reminders for leads';

    public function handle(): int
    {
        $leads = Lead::whereNotNull('next_follow_up')
            ->whereDate('next_follow_up', '<=', today())
            ->get();

        $reminded = 0;
        foreach ($leads as $lead) {
            $agent = $lead->agent_id ? User::find($lead->agent_id) : null;
            if (! $agent) {
                continue;
            }

            $agent->notify(new EmployeeReminderNotification('客户跟进提醒', [
                'lead_id' => $lead->id,
                'lead_name' => $lead->lead_name,
                'follow_up_date' => (string) $lead->next_follow_up,
            ]));
            $reminded++;
        }

        $this->info("Follow-up reminders: {$reminded}");
        return self::SUCCESS;
    }
}
