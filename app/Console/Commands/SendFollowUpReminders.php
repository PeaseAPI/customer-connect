<?php
namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class SendFollowUpReminders extends Command
{
    protected $signature = 'cc:follow-up-reminders';
    protected $description = 'Send follow-up reminders for leads';

    public function handle(): int
    {
        $leads = Lead::whereNotNull("next_follow_up_date")->where("next_follow_up_date","<=",now())->where("status","open")->get(); $this->info("Follow-up reminders: {$leads->count()}");
        return self::SUCCESS;
    }
}
