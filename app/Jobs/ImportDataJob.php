<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        public object $import,
        public string $filePath,
        public int $userId
    ) {}

    public function handle(): void
    {
        try {
            $user = User::find($this->userId);

            Excel::import($this->import, $this->filePath, 'local');

            if ($user && method_exists($this->import, 'getImportedCount')) {
                $user->notify(new \App\Notifications\ImportCompletedNotification(
                    $this->import->getImportedCount(),
                    $this->import->getFailedCount(),
                    $this->import->getErrors()
                ));
            }
        } catch (\Exception $e) {
            Log::error('导入数据Job失败', [
                'import_class' => get_class($this->import),
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }
}
