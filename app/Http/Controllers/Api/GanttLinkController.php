<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\GanttLink;
use Illuminate\Http\Request;

class GanttLinkController extends BaseApiController
{
    public function index(Request $request)
    {
        $projectId = $request->input('project_id');
        $query = GanttLink::with(['sourceTask:id,title', 'targetTask:id,title']);

        if ($projectId) {
            $query->whereHas('sourceTask', fn($q) => $q->where('project_id', $projectId));
        }

        return $this->paginated($query, $request);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'source_task_id' => 'required|exists:tasks,id',
            'target_task_id' => 'required|exists:tasks,id|different:source_task_id',
            'type' => 'required|integer|in:0,1,2,3',
            'lag' => 'nullable|integer',
        ]);
        $data['company_id'] = app('App\Services\ContextService')->getCompanyId();

        return $this->success(GanttLink::create($data), '依赖关系已创建', 201);
    }

    public function destroy(GanttLink $ganttLink)
    {
        $ganttLink->delete();
        return $this->success(null, '依赖关系已删除');
    }
}
