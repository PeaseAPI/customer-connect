<?php

namespace App\Events;

use App\Models\ProjectNote;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectNoteAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ProjectNote $note) {}
}
