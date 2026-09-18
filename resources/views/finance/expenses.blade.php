@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div x-data="{ showModal: false, editExpense: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
        <button @click="showModal = true; editExpense = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ New Expense</button>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($expenses as $expense)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $expense['item_name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $expense['category'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($expense['amount'] ?? 0, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $expense['purchase_date'] ?? '-' }}</td>
                    <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($expense['status'] ?? '') === 'approved' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $expense['status'] ?? 'pending' }}</span></td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editExpense = {{ json_encode($expense) }}; showModal = true" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        @if(($expense['status'] ?? '') !== 'approved')
                        <form method="POST" action="{{ route('finance.expenses.approve', $expense['id'] ?? 0) }}" class="inline">@csrf<button class="text-green-600 hover:text-green-900">Approve</button></form>
                        @endif
                        <form method="POST" action="{{ route('finance.expenses.destroy', $expense['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($expenses) === 0)
                <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No expenses found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editExpense ? 'Edit Expense' : 'New Expense'"></h3>
                <form method="POST" :action="editExpense ? '{{ route('finance.expenses.update', ['id' => 'EID']) }}'.replace('EID', editExpense.id) : '{{ route('finance.expenses.store') }}'">
                    <input type="hidden" name="_method" :value="editExpense ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label><input name="item_name" :value="editExpense?.item_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Category</label><input name="category" :value="editExpense?.category" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label><input name="amount" type="number" step="0.01" :value="editExpense?.amount" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label><input name="purchase_date" type="date" :value="editExpense?.purchase_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
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