<?php

namespace App\Http\Controllers\Api;

use App\Models\Appreciation;
use App\Services\HRM\AppreciationService;
use Illuminate\Http\Request;

class AppreciationController extends BaseApiController
{
    public function __construct(protected AppreciationService $appreciationService) {}

    public function index(Request $request)
    {
        $appreciations = $this->appreciationService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($appreciations);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'user_id' => 'required|exists:users,id',
            'award_id' => 'nullable|exists:award_icons,id',
            'description' => 'nullable|string',
            'awarded_date' => 'nullable|date',
        ]);
        $v['awarded_by'] = $request->user()->id;
        $v['added_by'] = $request->user()->id;
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->appreciationService->create($v)->load(['user', 'creator']), 'Appreciation created successfully', 201);
    }

    public function show(Appreciation $appreciation) { return $this->success($appreciation->load(['user', 'creator'])); }

    public function update(Request $request, Appreciation $appreciation)
    {
        $v = $request->validate(['description' => 'nullable|string', 'awarded_date' => 'nullable|date']);
        $appreciation = $this->appreciationService->update($appreciation, $v);
        return $this->success($appreciation->load(['user', 'creator']), 'Updated successfully');
    }

    public function destroy(Appreciation $appreciation) { $this->appreciationService->delete($appreciation); return $this->success(null, 'Deleted successfully'); }
}

