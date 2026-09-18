@extends('layouts.app')
@section('title', $title ?? 'Page')
@section('content')
<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
        @if(isset($createUrl))
        <a href="{{ $createUrl }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
            + Add New
        </a>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach($headers as $header)
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $header }}</th>
                        @endforeach
                        @if(isset($actions))
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rows as $row)
                    <tr class="hover:bg-gray-50">
                        @foreach($row['cells'] as $cell)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cell }}</td>
                        @endforeach
                        @if(isset($actions))
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <a href="#" class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                    @if(count($rows) === 0)
                    <tr>
                        <td colspan="{{ count($headers) + (isset($actions) ? 1 : 0) }}" class="px-6 py-12 text-center text-sm text-gray-500">
                            No records found
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
