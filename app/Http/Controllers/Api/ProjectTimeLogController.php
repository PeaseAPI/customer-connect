<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProjectTimeLogRequest;
use App\Http\Requests\UpdateProjectTimeLogRequest;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use App\Services\PM\ProjectTimeLogService;
use Illuminate\Http\Request;

class ProjectTimeLogController extends BaseApiController
{
    public function __construct(protected ProjectTimeLogService $projectTimeLogService) {}

    public function index(Request $request, $projectId)
    {
        $logs = $this->projectTimeLogService->list($projectId, $request->per_page ?? 15);
        return $this->paginated($logs);
    }

    public function store(StoreProjectTimeLogRequest $request, $projectId)
    {
        $v = $request->validated();
        $v['project_id'] = $projectId;
        $v['added_by'] = $request->user()->id;
        $v['editor'] = 'admin';
        return $this->success($this->projectTimeLogService->create($v)->load(['user', 'task', 'creator']), '时间记录创建成功', 201);
    }

    public function show($projectId, ProjectTimeLog $timeLog)
    {
        return $this->success($timeLog->load(['user', 'task', 'creator', 'breaks']));
    }

    public function update(UpdateProjectTimeLogRequest $request, $projectId, ProjectTimeLog $timeLog)
    {
        $v = $request->validated();
        $timeLog = $this->projectTimeLogService->update($timeLog, $v);
        return $this->success($timeLog->load(['user', 'task', 'creator', 'breaks']), '更新成功');
    }

    public function destroy($projectId, ProjectTimeLog $timeLog)
    {
        $this->projectTimeLogService->delete($timeLog);
        return $this->success(null, '删除成功');
    }

    // Break management
    public function startBreak(Request $request, $projectId, ProjectTimeLog $timeLog)
    {
        $break = $this->projectTimeLogService->startBreak($timeLog, $request->user()->company_id);
        return $this->success($break, '休息开始', 201);
    }

    public function endBreak(Request $request, $projectId, ProjectTimeLog $timeLog, ProjectTimeLogBreak $breakLog)
    {
        $breakLog = $this->projectTimeLogService->endBreak($breakLog);
        return $this->success($breakLog, '休息结束');
    }
}
