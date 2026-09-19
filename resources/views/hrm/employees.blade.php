@extends('layouts.app')
@section('title', 'Employees')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editEmployee: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Employees</h1>
        <button @click="showModal = true; editEmployee = null" class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">+ Add Employee</button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('hrm.employees') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="search" type="text" value="{{ request('search') }}" placeholder="Search employees..." class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="deactive" {{ request('status') === 'deactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <select name="department_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Departments</option>
            @foreach($departments ?? [] as $department)
            <option value="{{ $department['id'] }}" {{ request('department_id') == $department['id'] ? 'selected' : '' }}>{{ $department['department_name'] }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">Filter</button>
        @if(request('search') || request('status') || request('department_id'))
        <a href="{{ route('hrm.employees') }}" class="text-sm text-bodytext hover:text-ink">Clear</a>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-bodytext uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-stroke">
                @foreach($employees as $employee)
                <tr class="hover:bg-surface">
                    <td class="px-6 py-4 text-sm font-medium text-ink">{{ $employee['name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $employee['email'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $employee['department'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($employee['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-surface text-ink' }}">{{ $employee['status'] ?? 'inactive' }}</span></td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editEmployee = {{ json_encode($employee) }}; showModal = true" class="text-primary-500 hover:text-primary-800">Edit</button>
                        <form method="POST" action="{{ route('hrm.employees.destroy', $employee['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($employees) === 0)
                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-bodytext">No employees found</td></tr>
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
                <h3 class="text-lg font-semibold text-ink mb-4" x-text="editEmployee ? 'Edit Employee' : 'Add Employee'"></h3>
                <form method="POST" :action="editEmployee ? '{{ route('hrm.employees.update', ['id' => 'EID']) }}'.replace('EID', editEmployee.id) : '{{ route('hrm.employees.store') }}'">
                    <input type="hidden" name="_method" :value="editEmployee ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-ink mb-1">Full Name *</label><input name="name" @if(old('name')) value="{{ old('name') }}" @else :value="editEmployee?.name" @endif class="w-full rounded-lg border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Email *</label><input name="email" type="email" @if(old('email')) value="{{ old('email') }}" @else :value="editEmployee?.email" @endif class="w-full rounded-lg border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Department *</label><select name="department_id" class="w-full rounded-lg border {{ $errors->has('department_id') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required><option value="">-- Select --</option>@foreach($departments ?? [] as $department)<option value="{{ $department['id'] }}" :selected="(editEmployee?.employeeDetail?.department_id ?? '{{ old('department_id') }}') == {{ $department['id'] }}">{{ $department['department_name'] }}</option>@endforeach</select>
                            @error('department_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Designation *</label><select name="designation_id" class="w-full rounded-lg border {{ $errors->has('designation_id') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required><option value="">-- Select --</option>@foreach($designations ?? [] as $designation)<option value="{{ $designation['id'] }}" :selected="(editEmployee?.employeeDetail?.designation_id ?? '{{ old('designation_id') }}') == {{ $designation['id'] }}">{{ $designation['designation_name'] }}</option>@endforeach</select>
                            @error('designation_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Status</label><select name="status" class="w-full rounded-lg border {{ $errors->has('status') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm"><option value="active" :selected="(editEmployee?.status ?? '{{ old('status', 'active') }}') === 'active'">Active</option><option value="deactive" :selected="(editEmployee?.status ?? '{{ old('status', 'active') }}') === 'deactive'">Inactive</option></select>
                            @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div x-show="!editEmployee"><label class="block text-sm font-medium text-ink mb-1">Password *</label><input name="password" type="password" class="w-full rounded-lg border {{ $errors->has('password') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
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