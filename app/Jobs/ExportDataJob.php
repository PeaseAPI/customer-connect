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
        public string $exportClass,
        public array $filters,
        public int $userId
    ) {}

    public function handle(): void
    {
        try {
            $user = User::find($this->userId);
            $export = new $this->exportClass($this->filters);

            $fileName = class_basename($this->exportClass) . '_' . now()->format('YmdHis') . '.xlsx';
            $filePath = 'exports/' . $fileName;

            Excel::store($export, $filePath, 'local');

            // Notify user that export is ready
            if ($user) {
                $user->notify(new \App\Notifications\ExportReadyNotification($filePath, $fileName));
            }
        } catch (\Exception $e) {
            Log::error('导出数据Job失败', [
                'export_class' => $this->exportClass,
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }
}
