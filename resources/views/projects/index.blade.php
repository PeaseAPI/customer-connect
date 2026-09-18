@extends('layouts.app')
@section('title', 'Projects')
@section('content')
<div x-data="{ showModal: false, editProject: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
        <button @click="showModal = true; editProject = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ New Project</button>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($projects as $project)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $project['project_name'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($project['status'] ?? '') === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ $project['status'] ?? 'active' }}</span></td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $project['deadline'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <a href="{{ route('projects.show', $project['id'] ?? 0) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        <button @click="editProject = {{ json_encode($project) }}; showModal = true" class="text-amber-600 hover:text-amber-900">Edit</button>
                        <form method="POST" action="{{ route('projects.destroy', $project['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($projects) === 0)
                <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No projects found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editProject ? 'Edit Project' : 'New Project'"></h3>
                <form method="POST" :action="editProject ? '{{ route('projects.update', ['id' => 'PID']) }}'.replace('PID', editProject.id) : '{{ route('projects.store') }}'">
                    <input type="hidden" name="_method" :value="editProject ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Project Name *</label><input name="project_name" :value="editProject?.project_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label><select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="active">Active</option><option value="completed">Completed</option><option value="on_hold">On Hold</option><option value="cancelled">Cancelled</option></select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label><input name="deadline" type="date" :value="editProject?.deadline" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Description</label><textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea></div>
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
