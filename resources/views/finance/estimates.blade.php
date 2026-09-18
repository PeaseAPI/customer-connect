@extends('layouts.app')
@section('title', 'Estimates')
@section('content')
<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Estimates</h1>
        <a href="#" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ New Estimate</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estimate #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($estimates as $estimate)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $estimate['estimate_number'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $estimate['client_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($estimate['total'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">{{ $estimate['status'] ?? 'draft' }}</span></td>
                    <td class="px-6 py-4 text-right text-sm"><a href="#" class="text-indigo-600 hover:text-indigo-900">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection