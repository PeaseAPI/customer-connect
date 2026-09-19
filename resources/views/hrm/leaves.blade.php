@extends('layouts.app')
@section('title', 'Leaves')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editLeave: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Leave Requests</h1>
        <button @click="showModal = true; editLeave = null" class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">+ Request Leave</button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('hrm.leaves') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="search" type="text" value="{{ request('search') }}" placeholder="Search leave requests..." class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
        </select>
        <button type="submit" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">Filter</button>
        @if(request('search') || request('status'))
        <a href="{{ route('hrm.leaves') }}" class="text-sm text-bodytext hover:text-ink">Clear</a>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">From</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">To</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-bodytext uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-stroke">
                @foreach($leaves as $leave)
                <tr class="hover:bg-surface">
                    <td class="px-6 py-4 text-sm text-ink">{{ $leave['employee_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $leave['leave_type'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $leave['start_date'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $leave['end_date'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($leave['status'] ?? '') === 'approved' ? 'bg-green-100 text-green-800' : (($leave['status'] ?? '') === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ $leave['status'] ?? 'pending' }}</span></td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editLeave = {{ json_encode($leave) }}; showModal = true" class="text-primary-500 hover:text-primary-800">Edit</button>
                        <form method="POST" action="{{ route('hrm.leaves.destroy', $leave['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($leaves) === 0)
                <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-bodytext">No leave requests found</td></tr>
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
                <h3 class="text-lg font-semibold text-ink mb-4" x-text="editLeave ? 'Edit Leave' : 'Request Leave'"></h3>
                <form method="POST" :action="editLeave ? '{{ route('hrm.leaves.update', ['id' => 'LID']) }}'.replace('LID', editLeave.id) : '{{ route('hrm.leaves.store') }}'">
                    <input type="hidden" name="_method" :value="editLeave ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-ink mb-1">Employee Name *</label><input name="employee_name" @if(old('employee_name')) value="{{ old('employee_name') }}" @else :value="editLeave?.employee_name" @endif class="w-full rounded-lg border {{ $errors->has('employee_name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('employee_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Leave Type *</label><select name="leave_type" class="w-full rounded-lg border {{ $errors->has('leave_type') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm"><option value="annual" :selected="(editLeave?.leave_type ?? '{{ old('leave_type', 'annual') }}') === 'annual'">Annual</option><option value="sick" :selected="(editLeave?.leave_type ?? '{{ old('leave_type', 'annual') }}') === 'sick'">Sick</option><option value="personal" :selected="(editLeave?.leave_type ?? '{{ old('leave_type', 'annual') }}') === 'personal'">Personal</option><option value="maternity" :selected="(editLeave?.leave_type ?? '{{ old('leave_type', 'annual') }}') === 'maternity'">Maternity</option><option value="unpaid" :selected="(editLeave?.leave_type ?? '{{ old('leave_type', 'annual') }}') === 'unpaid'">Unpaid</option></select>
                            @error('leave_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-ink mb-1">Start Date *</label><input name="start_date" type="date" @if(old('start_date')) value="{{ old('start_date') }}" @else :value="editLeave?.start_date" @endif class="w-full rounded-lg border {{ $errors->has('start_date') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('start_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                            <div><label class="block text-sm font-medium text-ink mb-1">End Date *</label><input name="end_date" type="date" @if(old('end_date')) value="{{ old('end_date') }}" @else :value="editLeave?.end_date" @endif class="w-full rounded-lg border {{ $errors->has('end_date') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('end_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        </div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Reason</label><textarea name="reason" rows="2" class="w-full rounded-lg border {{ $errors->has('reason') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">@if(old('reason')){{ old('reason') }}@endif</textarea>
                            @error('reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
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