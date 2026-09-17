<?php

namespace App\Http\Controllers\Api;

use App\Models\Promotion;
use App\Services\HRM\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends BaseApiController
{
    public function __construct(protected PromotionService $promotionService) {}

    public function index(Request $request)
    {
        $promotions = $this->promotionService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($promotions);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'user_id' => 'required|exists:users,id',
            'designation_id' => 'nullable|exists:designations,id',
            'department_id' => 'nullable|exists:departments,id',
            'promotion_title' => 'required|string|max:255',
            'promotion_date' => 'required|date',
            'description' => 'nullable|string',
        ]);
        $v['added_by'] = $request->user()->id;
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->promotionService->create($v)->load(['user', 'designation', 'department', 'creator']), '晋升记录创建成功', 201);
    }

    public function show(Promotion $promotion) { return $this->success($promotion->load(['user', 'designation', 'department', 'creator'])); }

    public function update(Request $request, Promotion $promotion)
    {
        $v = $request->validate([
            'designation_id' => 'nullable|exists:designations,id',
            'department_id' => 'nullable|exists:departments,id',
            'promotion_title' => 'sometimes|string|max:255',
            'promotion_date' => 'sometimes|date',
            'description' => 'nullable|string',
        ]);
        $v['last_updated_by'] = $request->user()->id;
        $promotion = $this->promotionService->update($promotion, $v);
        return $this->success($promotion->load(['user', 'designation', 'department', 'creator']), '更新成功');
    }

    public function destroy(Promotion $promotion) { $this->promotionService->delete($promotion); return $this->success(null, '删除成功'); }
}

