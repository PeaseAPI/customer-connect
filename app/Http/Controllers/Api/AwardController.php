<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreAwardRequest;
use App\Http\Requests\UpdateAwardRequest;
use App\Models\Award;
use App\Services\HRM\AwardService;
use Illuminate\Http\Request;

class AwardController extends BaseApiController
{
    public function __construct(protected AwardService $awardService) {}

    public function index(Request $request)
    {
        $awards = $this->awardService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($awards);
    }

    public function store(StoreAwardRequest $request)
    {
        $v = $request->validated();
        $v['added_by'] = $request->user()->id;
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->awardService->create($v)->load(['user', 'awardIcon', 'creator']), '奖项创建成功', 201);
    }

    public function show(Award $award)
    {
        return $this->success($award->load(['user', 'awardIcon', 'creator']));
    }

    public function update(UpdateAwardRequest $request, Award $award)
    {
        $v = $request->validated();
        $v['last_updated_by'] = $request->user()->id;
        $award = $this->awardService->update($award, $v);
        return $this->success($award->load(['user', 'awardIcon', 'creator']), 'Updated successfully');
    }

    public function destroy(Award $award)
    {
        $this->awardService->delete($award);
        return $this->success(null, 'Deleted successfully');
    }
}

