@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<div>
    <div class="mb-6"><h1 class="text-2xl font-bold text-gray-900">Settings</h1></div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('settings.company') }}" class="bg-white rounded-xl border border-gray-200 p-6 hover:bg-gray-50 transition-colors">
            <h3 class="text-lg font-semibold text-gray-900">Company</h3>
            <p class="mt-1 text-sm text-gray-500">Manage company details, logo, and address</p>
        </a>
        <a href="{{ route('settings.notifications') }}" class="bg-white rounded-xl border border-gray-200 p-6 hover:bg-gray-50 transition-colors">
            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
            <p class="mt-1 text-sm text-gray-500">Configure email and push notification preferences</p>
        </a>
        <div class="bg-white rounded-xl border border-gray-200 p-6 opacity-50">
            <h3 class="text-lg font-semibold text-gray-900">Billing</h3>
            <p class="mt-1 text-sm text-gray-500">Manage subscription and payment methods</p>
        </div>
    </div>
</div>
@endsection