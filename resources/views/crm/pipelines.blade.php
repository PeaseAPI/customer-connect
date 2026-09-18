@extends('layouts.app')
@section('title', 'Pipelines')
@section('content')
<div x-data="{ showModal: false, editPipeline: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Pipelines</h1>
        <button @click="showModal = true; editPipeline = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ Add Pipeline</button>
    </div>
    <div class="grid grid-cols-1 gap-4">
        @foreach($pipelines as $pipeline)
        <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $pipeline['name'] ?? 'Pipeline' }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $pipeline['description'] ?? '' }}</p>
                @if(isset($pipeline['stages']) && count($pipeline['stages']) > 0)
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($pipeline['stages'] as $stage)
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700">{{ $stage['name'] ?? $stage }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <button @click="editPipeline = {{ json_encode($pipeline) }}; showModal = true" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</button>
                <form method="POST" action="{{ route('crm.pipelines.destroy', $pipeline['id'] ?? 0) }}">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900 text-sm">Delete</button></form>
            </div>
        </div>
        @endforeach
        @if(count($pipelines) === 0)
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-sm text-gray-500">No pipelines found. Click Add Pipeline to create one.</div>
        @endif
    </div>
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editPipeline ? 'Edit Pipeline' : 'Add Pipeline'"></h3>
                <form method="POST" :action="editPipeline ? '{{ route('crm.pipelines.update', ['id' => 'PID']) }}'.replace('PID', editPipeline.id) : '{{ route('crm.pipelines.store') }}'">
                    <input type="hidden" name="_method" :value="editPipeline ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Name *</label><input name="name" :value="editPipeline?.name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
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
