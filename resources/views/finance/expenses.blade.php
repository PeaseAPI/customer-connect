@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editExpense: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
        <button @click="showModal = true; editExpense = null" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">+ New Expense</button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('finance.expenses') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="search" type="text" value="{{ request('search') }}" placeholder="Search expenses..." class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
        </select>
        <select name="category_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Categories</option>
            @foreach($expenseCategories ?? [] as $category)
            <option value="{{ $category['id'] }}" {{ request('category_id') == $category['id'] ? 'selected' : '' }}>{{ $category['category_name'] }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Filter</button>
        @if(request('search') || request('status') || request('category_id'))
        <a href="{{ route('finance.expenses') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
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
    @if(isset($pagination) && ($pagination['last_page'] ?? 1) > 1)
    <x-pagination :pagination="$pagination" />
    @endif
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click="showModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="editExpense ? 'Edit Expense' : 'New Expense'"></h3>
                <form method="POST" :action="editExpense ? '{{ route('finance.expenses.update', ['id' => 'EID']) }}'.replace('EID', editExpense.id) : '{{ route('finance.expenses.store') }}'">
                    <input type="hidden" name="_method" :value="editExpense ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label><input name="item_name" @if(old('item_name')) value="{{ old('item_name') }}" @else :value="editExpense?.item_name" @endif class="w-full rounded-lg border {{ $errors->has('item_name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('item_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Category</label><select name="category_id" class="w-full rounded-lg border {{ $errors->has('category_id') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm"><option value="">-- None --</option>@foreach($expenseCategories ?? [] as $category)<option value="{{ $category['id'] }}" :selected="(editExpense?.category_id ?? '{{ old('category_id') }}') == {{ $category['id'] }}">{{ $category['category_name'] }}</option>@endforeach</select>
                            @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label><input name="amount" type="number" step="0.01" @if(old('amount')) value="{{ old('amount') }}" @else :value="editExpense?.amount" @endif class="w-full rounded-lg border {{ $errors->has('amount') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label><input name="purchase_date" type="date" @if(old('purchase_date')) value="{{ old('purchase_date') }}" @else :value="editExpense?.purchase_date" @endif class="w-full rounded-lg border {{ $errors->has('purchase_date') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('purchase_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
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