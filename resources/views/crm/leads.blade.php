@extends('layouts.app')
@section('title', 'Leads')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editLead: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Leads</h1>
        <button @click="showModal = true; editLead = null" class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Lead
        </button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('crm.leads') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="search" type="text" value="{{ request('search') }}" placeholder="Search leads..." class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <select name="status_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            @foreach($leadStages ?? [] as $stage)
            <option value="{{ $stage['id'] }}" {{ request('status_id') == $stage['id'] ? 'selected' : '' }}>{{ $stage['stage_name'] }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">Filter</button>
        @if(request('search') || request('status_id'))
        <a href="{{ route('crm.leads') }}" class="text-sm text-bodytext hover:text-ink">Clear</a>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Company</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Value</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-bodytext uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-stroke">
                @foreach($leads as $lead)
                <tr class="hover:bg-surface">
                    <td class="px-6 py-4 text-sm font-medium text-ink">{{ $lead['lead_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $lead['lead_email'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $lead['company_name'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-surface text-ink">{{ $lead['status']['stage_name'] ?? '—' }}</span></td>
                    <td class="px-6 py-4 text-sm text-bodytext">${{ number_format($lead['value'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editLead = {{ json_encode($lead) }}; showModal = true" class="text-primary-500 hover:text-primary-800">Edit</button>
                        @if(!($lead['is_client'] ?? false))
                        <form method="POST" action="{{ route('crm.leads.convert', $lead['id'] ?? 0) }}" class="inline">@csrf<button class="text-green-600 hover:text-green-900">Convert</button></form>
                        @endif
                        <form method="POST" action="{{ route('crm.leads.destroy', $lead['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($leads) === 0)
                <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-bodytext">No leads found</td></tr>
                @endif
            </tbody>
        </table>
        </div>

    {{-- Pagination --}}
    @if(isset($pagination) && ($pagination['last_page'] ?? 1) > 1)
    <x-pagination :pagination="$pagination" />
    @endif

    {{-- Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-surface0/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-ink mb-4" x-text="editLead ? 'Edit Lead' : 'Add Lead'"></h3>
                <form method="POST" :action="editLead ? '{{ route('crm.leads.update', ['id' => 'LEAD_ID']) }}'.replace('LEAD_ID', editLead.id) : '{{ route('crm.leads.store') }}'">
                    <input type="hidden" name="_method" :value="editLead ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Name *</label>
                            <input name="lead_name" @if(old('lead_name')) value="{{ old('lead_name') }}" @else :value="editLead?.lead_name" @endif class="w-full rounded-lg border {{ $errors->has('lead_name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('lead_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Email</label>
                            <input name="lead_email" type="email" @if(old('lead_email')) value="{{ old('lead_email') }}" @else :value="editLead?.lead_email" @endif class="w-full rounded-lg border {{ $errors->has('lead_email') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('lead_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Phone</label>
                            <input name="lead_mobile" @if(old('lead_mobile')) value="{{ old('lead_mobile') }}" @else :value="editLead?.lead_mobile" @endif class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Company</label>
                            <input name="company_name" @if(old('company_name')) value="{{ old('company_name') }}" @else :value="editLead?.company_name" @endif class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Value</label>
                            <input name="value" type="number" step="0.01" @if(old('value')) value="{{ old('value') }}" @else :value="editLead?.value" @endif class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Status</label>
                            <select name="status_id" class="w-full rounded-lg border {{ $errors->has('status_id') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                                <option value="">-- None --</option>
                                @foreach($leadStages ?? [] as $stage)
                                <option value="{{ $stage['id'] }}" :selected="(editLead?.status_id ?? '{{ old('status_id') }}') == {{ $stage['id'] }}">{{ $stage['stage_name'] }}</option>
                                @endforeach
                            </select>
                            @error('status_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
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
