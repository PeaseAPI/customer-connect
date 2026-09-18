@extends('layouts.app')
@section('title', 'Leaves')
@section('content')
<div x-data="{ showModal: false, editLeave: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Leave Requests</h1>
        <button @click="showModal = true; editLeave = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ Request Leave</button>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($leaves as $leave)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $leave['employee_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $leave['leave_type'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $leave['start_date'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $leave['end_date'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($leave['status'] ?? '') === 'approved' ? 'bg-green-100 text-green-800' : (($leave['status'] ?? '') === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ $leave['status'] ?? 'pending' }}</span></td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editLeave = {{ json_encode($leave) }}; showModal = true" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        <form method="POST" action="{{ route('hrm.leaves.destroy', $leave['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($leaves) === 0)
                <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No leave requests found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editLeave ? 'Edit Leave' : 'Request Leave'"></h3>
                <form method="POST" :action="editLeave ? '{{ route('hrm.leaves.update', ['id' => 'LID']) }}'.replace('LID', editLeave.id) : '{{ route('hrm.leaves.store') }}'">
                    <input type="hidden" name="_method" :value="editLeave ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Employee Name *</label><input name="employee_name" :value="editLeave?.employee_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Leave Type *</label><select name="leave_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="annual">Annual</option><option value="sick">Sick</option><option value="personal">Personal</option><option value="maternity">Maternity</option><option value="unpaid">Unpaid</option></select></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label><input name="start_date" type="date" :value="editLeave?.start_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label><input name="end_date" type="date" :value="editLeave?.end_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Reason</label><textarea name="reason" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea></div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Cancel</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-500">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection