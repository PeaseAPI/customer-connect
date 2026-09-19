@extends('layouts.app')
@section('title', 'Clients')
@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }}, editClient: null }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Clients</h1>
        <button @click="showModal = true; editClient = null" class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">+ Add Client</button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('crm.clients') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="search" type="text" value="{{ request('search') }}" placeholder="Search clients..." class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <button type="submit" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">Filter</button>
        @if(request('search'))
        <a href="{{ route('crm.clients') }}" class="text-sm text-bodytext hover:text-ink">Clear</a>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-bodytext uppercase">Industry</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-bodytext uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-stroke">
                @foreach($clients as $client)
                <tr class="hover:bg-surface">
                    <td class="px-6 py-4 text-sm font-medium text-ink">{{ $client['name'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $client['email'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-bodytext">{{ $client['industry'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-right text-sm space-x-2">
                        <button @click="editClient = {{ json_encode($client) }}; showModal = true" class="text-primary-500 hover:text-primary-800">Edit</button>
                        <form method="POST" action="{{ route('crm.clients.destroy', $client['id'] ?? 0) }}" class="inline">@method('DELETE')@csrf<button onclick="return confirm('Delete?')" class="text-red-600 hover:text-red-900">Delete</button></form>
                    </td>
                </tr>
                @endforeach
                @if(count($clients) === 0)
                <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-bodytext">No clients found</td></tr>
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
                <h3 class="text-lg font-semibold text-ink mb-4" x-text="editClient ? 'Edit Client' : 'Add Client'"></h3>
                <form method="POST" :action="editClient ? '{{ route('crm.clients.update', ['id' => 'CID']) }}'.replace('CID', editClient.id) : '{{ route('crm.clients.store') }}'">
                    <input type="hidden" name="_method" :value="editClient ? 'PUT' : 'POST'">
                    @csrf
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-ink mb-1">Name *</label><input name="name" @if(old('name')) value="{{ old('name') }}" @else :value="editClient?.name" @endif class="w-full rounded-lg border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Email *</label><input name="email" type="email" @if(old('email')) value="{{ old('email') }}" @else :value="editClient?.email" @endif class="w-full rounded-lg border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm" required>
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-sm font-medium text-ink mb-1">Industry</label><input name="industry" @if(old('industry')) value="{{ old('industry') }}" @else :value="editClient?.industry" @endif class="w-full rounded-lg border {{ $errors->has('industry') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('industry')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <div x-show="!editClient"><label class="block text-sm font-medium text-ink mb-1">Password *</label><input name="password" type="password" class="w-full rounded-lg border {{ $errors->has('password') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm">
                            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
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
