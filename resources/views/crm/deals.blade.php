@extends('layouts.app')
@section('title', 'Deals')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editDeal: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Deals</h1>
        <button @click="showModal = true; editDeal = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ Add Deal</button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('crm.deals') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="search" type="text" value="{{ request('search') }}" placeholder="Search deals..." class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <select name="pipeline_stage_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Stages</option>
            @foreach($pipelines ?? [] as $pipeline)
            @foreach($pipeline['stages'] ?? [] as $stage)
            <option value="{{ $stage['id'] }}" {{ request('pipeline_stage_id') == $stage['id'] ? 'selected' : '' }}>{{ $pipeline['name'] ?? 'Pipeline' }} › {{ $stage['name'] }}</option>
            @endforeach
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Filter</button>
        @if(request('search') || request('pipeline_stage_id'))
        <a href="{{ route('crm.deals') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>

    {{-- Validation Error Banner --}}
    @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
        <p class="text-sm font-medium text-red-800">Please fix the following errors:</p>
        <ul class="mt-1 list-disc list-inside text-sm text-red-700">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stage</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($deals as $deal)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $deal['title'] ?? $deal['deal_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($deal['value'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">{{ $deal['stage']['name'] ?? $deal['stage'] ?? 'new' }}</span></td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $deal['client_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editDeal = {{ json_encode($deal) }}; showModal = true" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        <form method="POST" action="{{ route('crm.deals.destroy', $deal['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($deals) === 0)
                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No deals found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    @if(isset($pagination) && ($pagination['last_page'] ?? 1) > 1)
    <x-pagination :pagination="$pagination" />
    @endif
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editDeal ? 'Edit Deal' : 'Add Deal'"></h3>
                <form method="POST" :action="editDeal ? '{{ route('crm.deals.update', ['id' => 'DID']) }}'.replace('DID', editDeal.id) : '{{ route('crm.deals.store') }}'">
                    <input type="hidden" name="_method" :value="editDeal ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Deal Name *</label><input name="deal_name" @if(old('deal_name')) value="{{ old('deal_name') }}" @else :value="editDeal?.deal_name" @endif class="w-full rounded-lg border {{ $errors->has('deal_name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('deal_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Value</label><input name="value" type="number" step="0.01" @if(old('value')) value="{{ old('value') }}" @else :value="editDeal?.value" @endif class="w-full rounded-lg border {{ $errors->has('value') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('value')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Client Name</label><input name="client_name" @if(old('client_name')) value="{{ old('client_name') }}" @else :value="editDeal?.client_name" @endif class="w-full rounded-lg border {{ $errors->has('client_name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('client_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
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
