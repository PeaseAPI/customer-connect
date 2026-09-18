@extends('layouts.app')
@section('title', 'Project Details')
@section('content')
<div>
    <div class="mb-6">
        <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Back to Projects</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $project['project_name'] ?? 'Project' }}</h1>
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Details</h3>
            <dl class="space-y-3">
                <div><dt class="text-sm font-medium text-gray-500">Status</dt><dd class="mt-1 text-sm text-gray-900">{{ $project['status'] ?? '-' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Deadline</dt><dd class="mt-1 text-sm text-gray-900">{{ $project['deadline'] ?? '-' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Description</dt><dd class="mt-1 text-sm text-gray-900">{{ $project['description'] ?? '-' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Info</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Created</span><span class="text-gray-900">{{ $project['created_at'] ?? '-' }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
