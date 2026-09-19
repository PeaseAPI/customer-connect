@extends('layouts.app')
@section('title', 'Estimates')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editEstimate: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Estimates</h1>
        <button @click="showModal = true; editEstimate = null" class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">+ New Estimate</button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('finance.estimates') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="search" type="text" value="{{ request('search') }}" placeholder="Search estimates..." class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
            <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
            <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
        </select>
        <button type="submit" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">Filter</button>
        @if(request('search') || request('status'))
        <a href="{{ route('finance.estimates') }}" class="text-sm text-bodytext hover:text-ink">Clear</a>
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
    <div class="bg-white rounded-xl border border-stroke overflow-hidden">
        <table class="min-w-full divide-y divide-stroke">
            <thead class="bg-surface"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Estimate #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-bodytext uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-stroke">
                @foreach($estimates as $estimate)
                <tr class="hover:bg-surface">
                    <td class="px-6 py-4 text-sm font-medium text-ink">{{ $estimate['estimate_number'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $estimate['client_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-ink">${{ number_format($estimate['total'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($estimate['status'] ?? '') === 'accepted' ? 'bg-green-100 text-green-800' : (($estimate['status'] ?? '') === 'declined' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">{{ $estimate['status'] ?? 'draft' }}</span></td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editEstimate = {{ json_encode($estimate) }}; showModal = true" class="text-primary-500 hover:text-primary-800">Edit</button>
                        <form method="POST" action="{{ route('finance.estimates.destroy', $estimate['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($estimates) === 0)
                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-bodytext">No estimates found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    @if(isset($pagination) && ($pagination['last_page'] ?? 1) > 1)
    <x-pagination :pagination="$pagination" />
    @endif
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-surface0/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-ink mb-4" x-text="editEstimate ? 'Edit Estimate' : 'New Estimate'"></h3>
                <form method="POST" :action="editEstimate ? '{{ route('finance.estimates.update', ['id' => 'ESID']) }}'.replace('ESID', editEstimate.id) : '{{ route('finance.estimates.store') }}'">
                    <input type="hidden" name="_method" :value="editEstimate ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-ink mb-1">Client Name *</label><input name="client_name" @if(old('client_name')) value="{{ old('client_name') }}" @else :value="editEstimate?.client_name" @endif class="w-full rounded-lg border {{ $errors->has('client_name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('client_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Amount *</label><input name="total" type="number" step="0.01" @if(old('total')) value="{{ old('total') }}" @else :value="editEstimate?.total" @endif class="w-full rounded-lg border {{ $errors->has('total') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('total')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Status</label><select name="status" class="w-full rounded-lg border {{ $errors->has('status') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm"><option value="draft" :selected="(editEstimate?.status ?? '{{ old('status', 'draft') }}') === 'draft'">Draft</option><option value="sent" :selected="(editEstimate?.status ?? '{{ old('status', 'draft') }}') === 'sent'">Sent</option><option value="accepted" :selected="(editEstimate?.status ?? '{{ old('status', 'draft') }}') === 'accepted'">Accepted</option><option value="declined" :selected="(editEstimate?.status ?? '{{ old('status', 'draft') }}') === 'declined'">Declined</option></select>
                            @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
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