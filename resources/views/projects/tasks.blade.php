@extends('layouts.app')
@section('title', 'Tasks')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editTask: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Tasks</h1>
        <button @click="showModal = true; editTask = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ New Task</button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ $projectId ? route('projects.tasks', $projectId) : route('tasks.index') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="search" type="text" value="{{ request('search') }}" placeholder="Search tasks..." class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Review</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Canceled</option>
        </select>
        <select name="priority" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Priorities</option>
            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
        </select>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Filter</button>
        @if(request('search') || request('status') || request('priority'))
        <a href="{{ $projectId ? route('projects.tasks', $projectId) : route('tasks.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
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
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($tasks as $task)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $task['title'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($task['status'] ?? '') === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ $task['status'] ?? 'pending' }}</span></td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($task['priority'] ?? '') === 'high' ? 'bg-red-100 text-red-800' : (($task['priority'] ?? '') === 'medium' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800') }}">{{ $task['priority'] ?? 'low' }}</span></td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task['due_date'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editTask = {{ json_encode($task) }}; showModal = true" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        <form method="POST" action="{{ route('tasks.destroy', $task['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($tasks) === 0)
                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No tasks found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    @if(isset($pagination) && ($pagination['last_page'] ?? 1) > 1)
    <x-pagination :pagination="$pagination" />
    @endif
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editTask ? 'Edit Task' : 'New Task'"></h3>
                <form method="POST" :action="editTask ? '{{ route('tasks.update', ['id' => 'TID']) }}'.replace('TID', editTask.id) : '{{ route('tasks.store') }}'">
                    <input type="hidden" name="_method" :value="editTask ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Title *</label><input name="title" @if(old('title')) value="{{ old('title') }}" @else :value="editTask?.title" @endif class="w-full rounded-lg border {{ $errors->has('title') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label><select name="status" class="w-full rounded-lg border {{ $errors->has('status') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm"><option value="pending" :selected="(editTask?.status ?? '{{ old('status', 'pending') }}') === 'pending'">Pending</option><option value="in_progress" :selected="(editTask?.status ?? '{{ old('status', 'pending') }}') === 'in_progress'">In Progress</option><option value="completed" :selected="(editTask?.status ?? '{{ old('status', 'pending') }}') === 'completed'">Completed</option></select>
                            @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Priority</label><select name="priority" class="w-full rounded-lg border {{ $errors->has('priority') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm"><option value="low" :selected="(editTask?.priority ?? '{{ old('priority', 'low') }}') === 'low'">Low</option><option value="medium" :selected="(editTask?.priority ?? '{{ old('priority', 'low') }}') === 'medium'">Medium</option><option value="high" :selected="(editTask?.priority ?? '{{ old('priority', 'low') }}') === 'high'">High</option></select>
                            @error('priority')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label><input name="due_date" type="date" @if(old('due_date')) value="{{ old('due_date') }}" @else :value="editTask?.due_date" @endif class="w-full rounded-lg border {{ $errors->has('due_date') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('due_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
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
