<?php

namespace App\Http\Controllers\Api;

use App\Models\Holiday;
use App\Services\HRM\HolidayService;
use Illuminate\Http\Request;

class HolidayController extends BaseApiController
{
    public function __construct(protected HolidayService $holidayService) {}

    public function index(Request $request)
    {
        $holidays = $this->holidayService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($holidays);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'holiday_name' => 'required|string|max:191',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success($this->holidayService->create($validated), 'Holiday created', 201);
    }

    public function show(Holiday $holiday)
    {
        return $this->success($holiday);
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'holiday_name' => 'sometimes|string|max:191',
            'date' => 'sometimes|date',
            'description' => 'nullable|string',
        ]);

        $holiday = $this->holidayService->update($holiday, $validated);

        return $this->success($holiday, 'Updated successfully');
    }

    public function destroy(Holiday $holiday)
    {
        $this->holidayService->delete($holiday);

        return $this->success(null, 'Deleted successfully');
    }
}

