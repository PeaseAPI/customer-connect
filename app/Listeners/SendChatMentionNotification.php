<?php

namespace App\Listeners;

use App\Events\ChatMention;
use App\Notifications\ChatMentionNotification;
use App\Models\User;

class SendChatMentionNotification
{
    public function handle(ChatMention $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new ChatMentionNotification($event));}
    }
}
