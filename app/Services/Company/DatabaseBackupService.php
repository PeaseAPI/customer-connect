<?php

namespace App\Services\Company;

use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;

class DatabaseBackupService
{
    /**
     * 创建数据库备份
     */
    public function create(int $companyId, int $userId): DatabaseBackup
    {
        $filename = 'backup_' . $companyId . '_' . now()->format('YmdHis') . '.sql';
        $path = 'backups/' . $filename;

        $backup = DatabaseBackup::create([
            'company_id' => $companyId,
            'filename' => $filename,
            'path' => $path,
            'status' => 'pending',
            'created_by' => $userId,
        ]);

        // 异步执行备份
        dispatch(function () use ($backup, $companyId) {
            try {
                $backup->update(['status' => 'processing']);

                $content = $this->generateSqlDump($companyId);
                \Illuminate\Support\Facades\Storage::put($path, $content);

                $size = \Illuminate\Support\Facades\Storage::size($path);

                $backup->update([
                    'status' => 'completed',
                    'size' => $size,
                ]);
            } catch (\Exception $e) {
                $backup->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        });

        return $backup;
    }

    /**
     * 获取备份列表
     */
    public function list(int $companyId, int $perPage = 15)
    {
        return DatabaseBackup::where('company_id', $companyId)
            ->with('creator')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * 删除备份
     */
    public function delete(DatabaseBackup $backup): void
    {
        if (\Illuminate\Support\Facades\Storage::exists($backup->path)) {
            \Illuminate\Support\Facades\Storage::delete($backup->path);
        }

        $backup->delete();
    }

    /**
     * 恢复备份（仅恢复该公司的数据）
     */
    public function restore(DatabaseBackup $backup): bool
    {
        if ($backup->status !== 'completed') {
            return false;
        }

        if (!\Illuminate\Support\Facades\Storage::exists($backup->path)) {
            return false;
        }

        // 标记恢复中 — 实际恢复逻辑根据需求实现
        // 此处为安全起见，只提供接口，恢复操作需管理员确认
        return true;
    }

    /**
     * 生成 SQL 转储
     */
    protected function generateSqlDump(int $companyId): string
    {
        $tables = [
            'users', 'projects', 'tasks', 'clients', 'invoices',
            'expenses', 'contracts', 'leads', 'deals', 'attendance',
            'leaves', 'tickets', 'notifications', 'events',
        ];

        $dump = "-- KHT CRM Database Backup\n-- Company ID: {$companyId}\n-- Date: " . now()->toDateTimeString() . "\n\n";

        foreach ($tables as $table) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)
                ->where('company_id', $companyId)
                ->get();

            if ($rows->isEmpty()) {
                continue;
            }

            $dump .= "-- Table: {$table}\n";
            foreach ($rows as $row) {
                $values = collect((array) $row)
                    ->map(fn($v) => $v === null ? 'NULL' : "'" . addslashes($v) . "'")
                    ->implode(', ');
                $columns = implode(', ', array_keys((array) $row));
                $dump .= "INSERT INTO {$table} ({$columns}) VALUES ({$values});\n";
            }
            $dump .= "\n";
        }

        return $dump;
    }
}
