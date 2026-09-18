@extends('layouts.app')
@section('title', 'Payments')
@section('content')
<div>
    <div class="mb-6"><h1 class="text-2xl font-bold text-gray-900">Payments</h1></div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($payments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $payment['paid_on'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $payment['invoice_number'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($payment['amount'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $payment['payment_method'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection