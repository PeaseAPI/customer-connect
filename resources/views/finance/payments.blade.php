@extends('layouts.app')
@section('title', 'Payments')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editPayment: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Payments</h1>
        <button @click="showModal = true; editPayment = null" class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">+ Record Payment</button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('finance.payments') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="search" type="text" value="{{ request('search') }}" placeholder="Search payments..." class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <button type="submit" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">Filter</button>
        @if(request('search'))
        <a href="{{ route('finance.payments') }}" class="text-sm text-bodytext hover:text-ink">Clear</a>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Invoice</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Method</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-bodytext uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-stroke">
                @foreach($payments as $payment)
                <tr class="hover:bg-surface">
                    <td class="px-6 py-4 text-sm text-ink">{{ $payment['paid_on'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $payment['invoice_number'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-ink font-medium">${{ number_format($payment['amount'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-primary-100 text-primary-600">{{ $payment['payment_method'] ?? '-' }}</span></td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editPayment = {{ json_encode($payment) }}; showModal = true" class="text-primary-500 hover:text-primary-800">Edit</button>
                    </td>
                </tr>
                @endforeach
                @if(count($payments) === 0)
                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-bodytext">No payments found</td></tr>
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
                <h3 class="text-lg font-semibold text-ink mb-4" x-text="editPayment ? 'Edit Payment' : 'Record Payment'"></h3>
                <form method="POST" action="{{ route('finance.payments.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-ink mb-1">Invoice Number *</label><input name="invoice_number" @if(old('invoice_number')) value="{{ old('invoice_number') }}" @else :value="editPayment?.invoice_number" @endif class="w-full rounded-lg border {{ $errors->has('invoice_number') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('invoice_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Amount *</label><input name="amount" type="number" step="0.01" @if(old('amount')) value="{{ old('amount') }}" @else :value="editPayment?.amount" @endif class="w-full rounded-lg border {{ $errors->has('amount') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Payment Method</label><select name="payment_method" class="w-full rounded-lg border {{ $errors->has('payment_method') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm"><option value="cash" :selected="(editPayment?.payment_method ?? '{{ old('payment_method', 'cash') }}') === 'cash'">Cash</option><option value="check" :selected="(editPayment?.payment_method ?? '{{ old('payment_method', 'cash') }}') === 'check'">Check</option><option value="bank_transfer" :selected="(editPayment?.payment_method ?? '{{ old('payment_method', 'cash') }}') === 'bank_transfer'">Bank Transfer</option><option value="credit_card" :selected="(editPayment?.payment_method ?? '{{ old('payment_method', 'cash') }}') === 'credit_card'">Credit Card</option><option value="paypal" :selected="(editPayment?.payment_method ?? '{{ old('payment_method', 'cash') }}') === 'paypal'">PayPal</option><option value="other" :selected="(editPayment?.payment_method ?? '{{ old('payment_method', 'cash') }}') === 'other'">Other</option></select>
                            @error('payment_method')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Payment Date</label><input name="paid_on" type="date" @if(old('paid_on')) value="{{ old('paid_on') }}" @else :value="editPayment?.paid_on" @endif class="w-full rounded-lg border {{ $errors->has('paid_on') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('paid_on')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
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