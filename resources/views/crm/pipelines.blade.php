@extends('layouts.app')
@section('title', 'Pipelines')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editPipeline: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Pipelines</h1>
        <button @click="showModal = true; editPipeline = null" class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">+ Add Pipeline</button>
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
    <div class="grid grid-cols-1 gap-4">
        @foreach($pipelines as $pipeline)
        <div class="bg-white rounded-xl border border-stroke p-6 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-ink">{{ $pipeline['name'] ?? 'Pipeline' }}</h3>
                <p class="mt-1 text-sm text-bodytext">{{ $pipeline['description'] ?? '' }}</p>
                @if(isset($pipeline['stages']) && count($pipeline['stages']) > 0)
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($pipeline['stages'] as $stage)
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-primary-100 text-primary-600">{{ $stage['name'] ?? $stage }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <button @click="editPipeline = {{ json_encode($pipeline) }}; showModal = true" class="text-primary-500 hover:text-primary-800 text-sm">Edit</button>
                <form method="POST" action="{{ route('crm.pipelines.destroy', $pipeline['id'] ?? 0) }}">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900 text-sm">Delete</button></form>
            </div>
        </div>
        @endforeach
        @if(count($pipelines) === 0)
        <div class="bg-white rounded-xl border border-stroke p-12 text-center text-sm text-bodytext">No pipelines found. Click Add Pipeline to create one.</div>
        @endif
    </div>
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-surface0/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-ink mb-4" x-text="editPipeline ? 'Edit Pipeline' : 'Add Pipeline'"></h3>
                <form method="POST" :action="editPipeline ? '{{ route('crm.pipelines.update', ['id' => 'PID']) }}'.replace('PID', editPipeline.id) : '{{ route('crm.pipelines.store') }}'">
                    <input type="hidden" name="_method" :value="editPipeline ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-ink mb-1">Name *</label><input name="name" @if(old('name')) value="{{ old('name') }}" @else :value="editPipeline?.name" @endif class="w-full rounded-lg border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Description</label><textarea name="description" rows="3" class="w-full rounded-lg border {{ $errors->has('description') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">@if(old('description')){{ old('description') }}@endif</textarea>
                            @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
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
