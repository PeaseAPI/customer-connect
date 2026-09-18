@extends('layouts.app')
@section('title', 'Pipelines')
@section('content')
<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Pipelines</h1>
        <a href="#" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ Add Pipeline</a>
    </div>
    <div class="grid grid-cols-1 gap-4">
        @foreach($pipelines as $pipeline)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900">{{ $pipeline['name'] ?? 'Pipeline' }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $pipeline['description'] ?? '' }}</p>
        </div>
        @endforeach
    </div>
</div>
@endsection
