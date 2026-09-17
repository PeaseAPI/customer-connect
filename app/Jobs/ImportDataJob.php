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
        public string $importClass,
        public string $filePath,
        public int $userId
    ) {}

    public function handle(): void
    {
        try {
            $user = User::find($this->userId);
            $import = new $this->importClass;

            Excel::import($import, $this->filePath, 'local');

            if ($user) {
                $user->notify(new \App\Notifications\ImportCompletedNotification(
                    $import->getImportedCount(),
                    $import->getFailedCount(),
                    $import->getErrors()
                ));
            }
        } catch (\Exception $e) {
            Log::error('导入数据Job失败', [
                'import_class' => $this->importClass,
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }
}
