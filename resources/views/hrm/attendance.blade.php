@extends('layouts.app')
@section('title', 'Attendance')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editRecord: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Attendance</h1>
        <button @click="showModal = true; editRecord = null" class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">+ Record Attendance</button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('hrm.attendance') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="search" type="text" value="{{ request('search') }}" placeholder="Search attendance..." class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <input name="date" type="date" value="{{ request('date') }}" placeholder="Filter by date" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <button type="submit" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">Filter</button>
        @if(request('search') || request('date'))
        <a href="{{ route('hrm.attendance') }}" class="text-sm text-bodytext hover:text-ink">Clear</a>
        @endif
    </form>

    {{-- Validation Error Banner --}}
    @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
        <p class="text-sm font-medium text-red-800">Please fix the following errors:</p>
        <ul class="mt-1 list-disc list-inside text-sm text-red-700">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif
    <div class="bg-white rounded-xl border border-stroke overflow-hidden">
        <table class="min-w-full divide-y divide-stroke">
            <thead class="bg-surface"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Clock In</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Clock Out</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-bodytext uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-stroke">
                @foreach($attendance as $record)
                <tr class="hover:bg-surface">
                    <td class="px-6 py-4 text-sm font-medium text-ink">{{ $record['employee_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $record['date'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $record['clock_in'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $record['clock_out'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($record['status'] ?? '') === 'present' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $record['status'] ?? 'absent' }}</span></td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editRecord = {{ json_encode($record) }}; showModal = true" class="text-primary-500 hover:text-primary-800">Edit</button>
                        <form method="POST" action="{{ route('hrm.attendance.destroy', $record['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($attendance) === 0)
                <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-bodytext">No attendance records found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    @if(isset($pagination) && ($pagination['last_page'] ?? 1) > 1)
    <x-pagination :pagination="$pagination" />
    @endif
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-surface0/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-ink mb-4" x-text="editRecord ? 'Edit Attendance' : 'Record Attendance'"></h3>
                <form method="POST" :action="editRecord ? '{{ route('hrm.attendance.update', ['id' => 'AID']) }}'.replace('AID', editRecord.id) : '{{ route('hrm.attendance.store') }}'">
                    <input type="hidden" name="_method" :value="editRecord ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-ink mb-1">Employee Name *</label><input name="employee_name" @if(old('employee_name')) value="{{ old('employee_name') }}" @else :value="editRecord?.employee_name" @endif class="w-full rounded-lg border {{ $errors->has('employee_name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('employee_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Date *</label><input name="date" type="date" @if(old('date')) value="{{ old('date') }}" @else :value="editRecord?.date" @endif class="w-full rounded-lg border {{ $errors->has('date') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Clock In</label><input name="clock_in" type="time" @if(old('clock_in')) value="{{ old('clock_in') }}" @else :value="editRecord?.clock_in" @endif class="w-full rounded-lg border {{ $errors->has('clock_in') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('clock_in')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Clock Out</label><input name="clock_out" type="time" @if(old('clock_out')) value="{{ old('clock_out') }}" @else :value="editRecord?.clock_out" @endif class="w-full rounded-lg border {{ $errors->has('clock_out') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('clock_out')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Status</label><select name="status" class="w-full rounded-lg border {{ $errors->has('status') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm"><option value="present" :selected="(editRecord?.status ?? '{{ old('status', 'present') }}') === 'present'">Present</option><option value="absent" :selected="(editRecord?.status ?? '{{ old('status', 'present') }}') === 'absent'">Absent</option><option value="late" :selected="(editRecord?.status ?? '{{ old('status', 'present') }}') === 'late'">Late</option><option value="half_day" :selected="(editRecord?.status ?? '{{ old('status', 'present') }}') === 'half_day'">Half Day</option></select>
                            @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Cancel</button>
                        <button type="submit" class="rounded-lg bg-primary-500 px-4 py-2 text-sm text-white hover:bg-primary-500">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection