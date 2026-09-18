@extends('layouts.app')
@section('title', 'Company Settings')
@section('content')
<div>
    <div class="mb-6">
        <a href="{{ route('settings.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Back to Settings</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Company Settings</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
        <form method="POST" action="{{ route('settings.company.update') }}">
            @method('PUT')@csrf
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 sm:text-sm" placeholder="Customer Connect Technologies Ltd.">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Email</label>
                    <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email'] ?? '') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 sm:text-sm" placeholder="admin@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Phone</label>
                    <input type="tel" name="company_phone" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 sm:text-sm" placeholder="+1 (555) 123-4567">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="3" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 sm:text-sm" placeholder="123 Business Ave, Suite 100">{{ old('address', $settings['address'] ?? '') }}</textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection