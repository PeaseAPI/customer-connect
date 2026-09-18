@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
        <a href="#" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ New Expense</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($expenses as $expense)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $expense['item_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $expense['category'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($expense['amount'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $expense['purchase_date'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection