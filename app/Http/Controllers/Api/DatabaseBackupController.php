<?php

namespace App\Http\Controllers\Api;

use App\Models\DatabaseBackup;
use App\Services\Company\DatabaseBackupService;
use Illuminate\Http\Request;

class DatabaseBackupController extends BaseApiController
{
    public function __construct(protected DatabaseBackupService $backupService) {}

    /**
     * 获取备份列表
     */
    public function index(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $backups = $this->backupService->list($companyId, $request->per_page ?? 15);
        return $this->paginated($backups);
    }

    /**
     * 创建新备份
     */
    public function store(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $backup = $this->backupService->create($companyId, $request->user()->id);
        return $this->success($backup, '备份任务已创建', 201);
    }

    /**
     * 查看备份详情
     */
    public function show(DatabaseBackup $databaseBackup)
    {
        return $this->success($databaseBackup->load('creator'));
    }

    /**
     * 下载备份文件
     */
    public function download(DatabaseBackup $databaseBackup)
    {
        if ($databaseBackup->status !== 'completed') {
            return $this->error('备份尚未完成', 400);
        }

        if (!\Illuminate\Support\Facades\Storage::exists($databaseBackup->path)) {
            return $this->error('备份文件不存在', 404);
        }

        return \Illuminate\Support\Facades\Storage::download(
            $databaseBackup->path,
            $databaseBackup->filename
        );
    }

    /**
     * 恢复备份
     */
    public function restore(DatabaseBackup $databaseBackup)
    {
        $result = $this->backupService->restore($databaseBackup);

        if (!$result) {
            return $this->error('无法恢复此备份', 400);
        }

        return $this->success(null, '恢复请求已提交');
    }

    /**
     * 删除备份
     */
    public function destroy(DatabaseBackup $databaseBackup)
    {
        $this->backupService->delete($databaseBackup);
        return $this->success(null, '备份已删除');
    }
}
