@extends('layouts.app')
@section('title', 'Invoices')
@section('content')
<div x-data="{ showModal: false, editInvoice: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
        <button @click="showModal = true; editInvoice = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ New Invoice</button>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($invoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $invoice['invoice_number'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice['client_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($invoice['total'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($invoice['status'] ?? '') === 'paid' ? 'bg-green-100 text-green-800' : (($invoice['status'] ?? '') === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ $invoice['status'] ?? 'draft' }}</span></td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice['due_date'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editInvoice = {{ json_encode($invoice) }}; showModal = true" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        <form method="POST" action="{{ route('finance.invoices.destroy', $invoice['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($invoices) === 0)
                <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No invoices found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editInvoice ? 'Edit Invoice' : 'New Invoice'"></h3>
                <form method="POST" :action="editInvoice ? '{{ route('finance.invoices.update', ['id' => 'IID']) }}'.replace('IID', editInvoice.id) : '{{ route('finance.invoices.store') }}'">
                    <input type="hidden" name="_method" :value="editInvoice ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Client Name *</label><input name="client_name" :value="editInvoice?.client_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label><input name="total" type="number" step="0.01" :value="editInvoice?.total" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label><select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="draft">Draft</option><option value="sent">Sent</option><option value="paid">Paid</option><option value="overdue">Overdue</option></select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label><input name="due_date" type="date" :value="editInvoice?.due_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
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