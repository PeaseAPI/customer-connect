<?php

namespace App\Http\Controllers\Api;

use App\Models\Shift;
use App\Services\HRM\ShiftService;
use Illuminate\Http\Request;

class ShiftController extends BaseApiController
{
    public function __construct(protected ShiftService $shiftService) {}

    public function index(Request $request)
    {
        $shifts = $this->shiftService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($shifts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_name' => 'required|string|max:191',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'half_day_mark_time' => 'nullable|date_format:H:i',
            'late_mark_after' => 'nullable|integer',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success($this->shiftService->create($validated), 'Shift created', 201);
    }

    public function show(Shift $shift)
    {
        return $this->success($shift);
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'shift_name' => 'sometimes|string|max:191',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
            'half_day_mark_time' => 'nullable|date_format:H:i',
            'late_mark_after' => 'nullable|integer',
        ]);

        $shift = $this->shiftService->update($shift, $validated);

        return $this->success($shift, 'Updated successfully');
    }

    public function destroy(Shift $shift)
    {
        $this->shiftService->delete($shift);

        return $this->success(null, 'Deleted successfully');
    }
}

