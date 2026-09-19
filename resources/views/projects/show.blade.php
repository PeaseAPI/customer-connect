@extends('layouts.app')
@section('title', 'Project Details')
@section('content')
<div x-data="{ showEdit: {{ $errors->any() ? 'true' : 'false' }} }">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('projects.index') }}" class="text-sm text-primary-500 hover:text-primary-500">&larr; Back to Projects</a>
            <h1 class="text-2xl font-bold text-ink mt-2">{{ $project['project_name'] ?? 'Project' }}</h1>
        </div>
        <div class="flex gap-2">
            <button @click="showEdit = true" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-ink hover:bg-surface">Edit</button>
            <form method="POST" action="{{ route('projects.destroy', $project['id'] ?? 0) }}">@method('DELETE')@csrf<button onclick="return confirm('Delete this project?')" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Delete</button></form>
        </div>
    </div>

    {{-- Validation Error Banner --}}
    @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
        <p class="text-sm font-medium text-red-800">Please fix the following errors:</p>
        <ul class="mt-1 list-disc list-inside text-sm text-red-700">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white rounded-xl border border-stroke p-6">
            <h3 class="text-lg font-semibold text-ink mb-4">Details</h3>
            <dl class="space-y-3">
                <div><dt class="text-sm font-medium text-bodytext">Status</dt><dd class="mt-1"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($project['status'] ?? '') === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ $project['status'] ?? '-' }}</span></dd></div>
                <div><dt class="text-sm font-medium text-bodytext">Deadline</dt><dd class="mt-1 text-sm text-ink">{{ $project['deadline'] ?? '-' }}</dd></div>
                <div><dt class="text-sm font-medium text-bodytext">Description</dt><dd class="mt-1 text-sm text-ink">{{ $project['description'] ?? '-' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-stroke p-6">
            <h3 class="text-lg font-semibold text-ink mb-4">Quick Info</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-bodytext">Created</span><span class="text-ink">{{ $project['created_at'] ?? '-' }}</span></div>
            </div>
            <div class="mt-6">
                <a href="{{ route('projects.tasks', $project['id'] ?? 0) }}" class="block w-full text-center rounded-lg bg-primary-100 px-4 py-2 text-sm font-medium text-primary-500 hover:bg-primary-200">View Tasks</a>
            </div>
        </div>
    </div>
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-surface0/75" @click="showEdit = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-ink mb-4">Edit Project</h3>
                <form method="POST" action="{{ route('projects.update', $project['id'] ?? 0) }}">
                    @method('PUT')@csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-ink mb-1">Project Name *</label><input name="project_name" @if(old('project_name')) value="{{ old('project_name') }}" @else value="{{ $project['project_name'] ?? '' }}" @endif class="w-full rounded-lg border {{ $errors->has('project_name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('project_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Status</label><select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="active" {{ old('status', $project['status'] ?? 'active') === 'active' ? 'selected' : '' }}>Active</option><option value="completed" {{ old('status', $project['status'] ?? 'active') === 'completed' ? 'selected' : '' }}>Completed</option><option value="on_hold" {{ old('status', $project['status'] ?? 'active') === 'on_hold' ? 'selected' : '' }}>On Hold</option><option value="cancelled" {{ old('status', $project['status'] ?? 'active') === 'cancelled' ? 'selected' : '' }}>Cancelled</option></select></div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Deadline</label><input name="deadline" type="date" @if(old('deadline')) value="{{ old('deadline') }}" @else value="{{ $project['deadline'] ?? '' }}" @endif class="w-full rounded-lg border {{ $errors->has('deadline') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('deadline')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Description</label><textarea name="description" rows="3" class="w-full rounded-lg border {{ $errors->has('description') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">@if(old('description')){{ old('description') }}@else{{ $project['description'] ?? '' }}@endif</textarea>
                            @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showEdit = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Cancel</button>
                        <button type="submit" class="rounded-lg bg-primary-500 px-4 py-2 text-sm text-white hover:bg-primary-500">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
