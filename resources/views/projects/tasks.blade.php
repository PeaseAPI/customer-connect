@extends('layouts.app')
@section('title', 'Tasks')
@section('content')
<div x-data="{ showModal: false, editTask: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Tasks</h1>
        <button @click="showModal = true; editTask = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ New Task</button>
    </div>
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
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editTask ? 'Edit Task' : 'New Task'"></h3>
                                <form method="POST" :action="editTask ? '{{ route('tasks.update', ['id' => 'TID']) }}'.replace('TID', editTask.id) : '{{ route('tasks.store') }}'">
                    <input type="hidden" name="_method" :value="editTask ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Title *</label><input name="title" :value="editTask?.title" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label><select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Priority</label><select name="priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option></select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label><input name="due_date" type="date" :value="editTask?.due_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
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
