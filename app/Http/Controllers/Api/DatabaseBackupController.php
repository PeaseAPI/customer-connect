<?php

namespace App\Http\Controllers\Api;

use App\Models\DatabaseBackup;
use App\Services\Company\DatabaseBackupService;
use Illuminate\Http\Request;

class DatabaseBackupController extends BaseApiController
{
    public function __construct(protected DatabaseBackupService $backupService) {}

    /**
     * Get backup list
     */
    public function index(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $backups = $this->backupService->list($companyId, $request->per_page ?? 15);
        return $this->paginated($backups);
    }

    /**
     * Create new backup
     */
    public function store(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $backup = $this->backupService->create($companyId, $request->user()->id);
        return $this->success($backup, 'Backup job created', 201);
    }

    /**
     * View backup details
     */
    public function show(DatabaseBackup $databaseBackup)
    {
        return $this->success($databaseBackup->load('creator'));
    }

    /**
     * Download backup file
     */
    public function download(DatabaseBackup $databaseBackup)
    {
        if ($databaseBackup->status !== 'completed') {
            return $this->error('Backup not yet completed', 400);
        }

        if (!\Illuminate\Support\Facades\Storage::exists($databaseBackup->path)) {
            return $this->error('Backup file not found', 404);
        }

        return \Illuminate\Support\Facades\Storage::download(
            $databaseBackup->path,
            $databaseBackup->filename
        );
    }

    /**
     * Restore backup
     */
    public function restore(DatabaseBackup $databaseBackup)
    {
        $result = $this->backupService->restore($databaseBackup);

        if (!$result) {
            return $this->error('Cannot restore this backup', 400);
        }

        return $this->success(null, 'Restore request submitted');
    }

    /**
     * Delete backup
     */
    public function destroy(DatabaseBackup $databaseBackup)
    {
        $this->backupService->delete($databaseBackup);
        return $this->success(null, 'Backup deleted');
    }
}
