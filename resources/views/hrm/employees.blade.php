@extends('layouts.app')
@section('title', 'Employees')
@section('content')
<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
        <a href="#" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ Add Employee</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($employees as $employee)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $employee['name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $employee['email'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $employee['department'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($employee['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ $employee['status'] ?? 'inactive' }}</span></td>
                    <td class="px-6 py-4 text-right text-sm"><a href="#" class="text-indigo-600 hover:text-indigo-900">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection