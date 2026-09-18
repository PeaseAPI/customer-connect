@extends('layouts.app')
@section('title', 'Notification Settings')
@section('content')
<div>
    <div class="mb-6">
        <a href="{{ route('settings.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Back to Settings</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Notification Settings</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl space-y-4">
        <label class="flex items-center gap-3"><input type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-indigo-600"><span class="text-sm text-gray-700">Email notifications for new tasks</span></label>
        <label class="flex items-center gap-3"><input type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-indigo-600"><span class="text-sm text-gray-700">Email notifications for task assignments</span></label>
        <label class="flex items-center gap-3"><input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600"><span class="text-sm text-gray-700">Email notifications for leave requests</span></label>
        <label class="flex items-center gap-3"><input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600"><span class="text-sm text-gray-700">Email notifications for expense approvals</span></label>
        <label class="flex items-center gap-3"><input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600"><span class="text-sm text-gray-700">Push notifications for chat messages</span></label>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Save Preferences</button>
    </div>
</div>
@endsection