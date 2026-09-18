@extends('layouts.app')
@section('title', 'Employees')
@section('content')
<div x-data="{ showModal: false, editEmployee: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
        <button @click="showModal = true; editEmployee = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ Add Employee</button>
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
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editEmployee = {{ json_encode($employee) }}; showModal = true" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        <form method="POST" action="{{ route('hrm.employees.destroy', $employee['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($employees) === 0)
                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No employees found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editEmployee ? 'Edit Employee' : 'Add Employee'"></h3>
                <form method="POST" :action="editEmployee ? '{{ route('hrm.employees.update', ['id' => 'EID']) }}'.replace('EID', editEmployee.id) : '{{ route('hrm.employees.store') }}'">
                    <input type="hidden" name="_method" :value="editEmployee ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label><input name="name" :value="editEmployee?.name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Email *</label><input name="email" type="email" :value="editEmployee?.email" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Department</label><input name="department" :value="editEmployee?.department" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label><select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div x-show="!editEmployee"><label class="block text-sm font-medium text-gray-700 mb-1">Password *</label><input name="password" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
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