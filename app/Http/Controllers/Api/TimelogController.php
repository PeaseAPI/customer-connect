<?php

namespace App\Http\Controllers\Api;

use App\Models\Timelog;
use App\Services\PM\TimelogService;
use Illuminate\Http\Request;

class TimelogController extends BaseApiController
{
    public function __construct(protected TimelogService $timelogService) {}

    public function index(Request $request)
    {
        $timelogs = $this->timelogService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($timelogs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'project_id' => 'nullable|exists:projects,id',
            'user_id' => 'required|exists:users,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'total_hours' => 'nullable|numeric',
            'memo' => 'nullable|string',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->timelogService->create($validated)->load(['user', 'task', 'project']),
            '创建成功',
            201
        );
    }

    public function show(Timelog $timelog)
    {
        return $this->success($timelog->load(['user', 'task', 'project', 'editor']));
    }

    public function update(Request $request, Timelog $timelog)
    {
        $validated = $request->validate([
            'end_time' => 'nullable|date|after:start_time',
            'total_hours' => 'nullable|numeric',
            'memo' => 'nullable|string',
        ]);

        $validated['edited_by'] = $request->user()->id;

        $timelog = $this->timelogService->update($timelog, $validated);

        return $this->success($timelog->load(['user', 'task', 'project', 'editor']), '更新成功');
    }

    public function destroy(Timelog $timelog)
    {
        $this->timelogService->delete($timelog);
        return $this->success(null, '删除成功');
    }
}
