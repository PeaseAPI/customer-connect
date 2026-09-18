@extends('layouts.app')
@section('title', 'Leaves')
@section('content')
<div>
    <div class="mb-6"><h1 class="text-2xl font-bold text-gray-900">Leave Requests</h1></div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($leaves as $leave)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $leave['employee_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $leave['leave_type'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $leave['start_date'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $leave['end_date'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($leave['status'] ?? '') === 'approved' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $leave['status'] ?? 'pending' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection