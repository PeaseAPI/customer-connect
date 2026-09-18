<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $importedCount,
        public int $failedCount,
        public array $errors = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'import_completed',
            'imported_count' => $this->importedCount,
            'failed_count' => $this->failedCount,
            'errors' => array_slice($this->errors, 0, 10),
            'message' => "Import completed: {$this->importedCount} succeeded, {$this->failedCount} failed",
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
