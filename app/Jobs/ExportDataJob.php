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

class ExportDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        public object $export,
        public string $filePath,
        public int $userId
    ) {}

    public function handle(): void
    {
        try {
            $user = User::find($this->userId);

            Excel::store($this->export, $this->filePath, 'local');

            $fileName = class_basename($this->export) . '_' . now()->format('YmdHis') . '.xlsx';

            // Notify user that export is ready
            if ($user) {
                $user->notify(new \App\Notifications\ExportReadyNotification($this->filePath, $fileName));
            }
        } catch (\Exception $e) {
            Log::error('导出数据Job失败', [
                'export_class' => get_class($this->export),
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }
}
