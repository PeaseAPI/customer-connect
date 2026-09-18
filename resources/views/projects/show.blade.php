@extends('layouts.app')
@section('title', 'Project Details')
@section('content')
<div x-data="{ showEdit: false }">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Back to Projects</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $project['project_name'] ?? 'Project' }}</h1>
        </div>
        <div class="flex gap-2">
            <button @click="showEdit = true" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Edit</button>
            <form method="POST" action="{{ route('projects.destroy', $project['id'] ?? 0) }}">@method('DELETE')@csrf<button onclick="return confirm('Delete this project?')" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Delete</button></form>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Details</h3>
            <dl class="space-y-3">
                <div><dt class="text-sm font-medium text-gray-500">Status</dt><dd class="mt-1"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($project['status'] ?? '') === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ $project['status'] ?? '-' }}</span></dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Deadline</dt><dd class="mt-1 text-sm text-gray-900">{{ $project['deadline'] ?? '-' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Description</dt><dd class="mt-1 text-sm text-gray-900">{{ $project['description'] ?? '-' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Info</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Created</span><span class="text-gray-900">{{ $project['created_at'] ?? '-' }}</span></div>
            </div>
            <div class="mt-6">
                <a href="{{ route('projects.tasks', $project['id'] ?? 0) }}" class="block w-full text-center rounded-lg bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-100">View Tasks</a>
            </div>
        </div>
    </div>
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showEdit = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Project</h3>
                <form method="POST" action="{{ route('projects.update', $project['id'] ?? 0) }}">
                    @method('PUT')@csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Project Name *</label><input name="project_name" value="{{ $project['project_name'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label><select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="active" {{ ($project['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option><option value="completed" {{ ($project['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option><option value="on_hold" {{ ($project['status'] ?? '') === 'on_hold' ? 'selected' : '' }}>On Hold</option><option value="cancelled" {{ ($project['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option></select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label><input name="deadline" type="date" value="{{ $project['deadline'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Description</label><textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ $project['description'] ?? '' }}</textarea></div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showEdit = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Cancel</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-500">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
