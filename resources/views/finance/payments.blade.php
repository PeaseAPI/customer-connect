@extends('layouts.app')
@section('title', 'Payments')
@section('content')
<div x-data="{ showModal: false, editPayment: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
        <button @click="showModal = true; editPayment = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ Record Payment</button>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($payments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $payment['paid_on'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $payment['invoice_number'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">${{ number_format($payment['amount'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700">{{ $payment['payment_method'] ?? '-' }}</span></td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editPayment = {{ json_encode($payment) }}; showModal = true" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                    </td>
                </tr>
                @endforeach
                @if(count($payments) === 0)
                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No payments found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editPayment ? 'Edit Payment' : 'Record Payment'"></h3>
                <form method="POST" action="{{ route('finance.payments.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number *</label><input name="invoice_number" :value="editPayment?.invoice_number" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label><input name="amount" type="number" step="0.01" :value="editPayment?.amount" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label><select name="payment_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="cash">Cash</option><option value="check">Check</option><option value="bank_transfer">Bank Transfer</option><option value="credit_card">Credit Card</option><option value="paypal">PayPal</option><option value="other">Other</option></select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label><input name="paid_on" type="date" :value="editPayment?.paid_on" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
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