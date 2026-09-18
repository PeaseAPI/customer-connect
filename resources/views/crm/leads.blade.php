@extends('layouts.app')
@section('title', 'Leads')
@section('content')
<div x-data="{ showModal: false, editLead: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Leads</h1>
        <button @click="showModal = true; editLead = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Lead
        </button>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($leads as $lead)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $lead['lead_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $lead['lead_email'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $lead['company_name'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">{{ $lead['status']['name'] ?? $lead['status'] ?? 'new' }}</span></td>
                    <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($lead['value'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editLead = {{ json_encode($lead) }}; showModal = true" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        @if(!($lead['is_client'] ?? false))
                        <form method="POST" action="{{ route('crm.leads.convert', $lead['id'] ?? 0) }}" class="inline">@csrf<button class="text-green-600 hover:text-green-900">Convert</button></form>
                        @endif
                        <form method="POST" action="{{ route('crm.leads.destroy', $lead['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($leads) === 0)
                <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No leads found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <!-- Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editLead ? 'Edit Lead' : 'Add Lead'"></h3>
                <form method="POST" :action="editLead ? '{{ route('crm.leads.update', ['id' => 'LEAD_ID']) }}'.replace('LEAD_ID', editLead.id) : '{{ route('crm.leads.store') }}'">
                    <input type="hidden" name="_method" :value="editLead ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Name *</label><input name="lead_name" :value="editLead?.lead_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input name="lead_email" type="email" :value="editLead?.lead_email" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label><input name="lead_mobile" :value="editLead?.lead_mobile" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Company</label><input name="company_name" :value="editLead?.company_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Value</label><input name="value" type="number" step="0.01" :value="editLead?.value" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
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
