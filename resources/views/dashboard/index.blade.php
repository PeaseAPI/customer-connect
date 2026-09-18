@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Welcome back, {{ auth()->user()->name ?? 'User' }}!</p>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        @php $overview = $data['overview'] ?? []; @endphp
        <x-stat-card icon="projects" label="Projects" :value="$overview['projects_count'] ?? 0" color="blue" />
        <x-stat-card icon="tasks" label="Completed Tasks" :value="$overview['pending_tasks'] ?? 0" color="green" />
        <x-stat-card icon="revenue" label="Revenue (YTD)" :value="'$' . ($overview['revenue'] ?? '0')" color="purple" />
        <x-stat-card icon="clients" label="Clients" :value="$overview['clients_count'] ?? 0" color="amber" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
            </div>
            <div class="p-6 grid grid-cols-2 gap-3">
                <a href="{{ route('crm.leads') }}" class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 hover:bg-gray-50">
                    <div class="h-8 w-8 rounded bg-blue-50 flex items-center justify-center text-blue-600 font-bold">+</div>
                    <span class="text-sm font-medium text-gray-700">New Lead</span>
                </a>
                <a href="{{ route('projects.index') }}" class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 hover:bg-gray-50">
                    <div class="h-8 w-8 rounded bg-green-50 flex items-center justify-center text-green-600 font-bold">+</div>
                    <span class="text-sm font-medium text-gray-700">New Project</span>
                </a>
                <a href="{{ route('finance.invoices') }}" class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 hover:bg-gray-50">
                    <div class="h-8 w-8 rounded bg-purple-50 flex items-center justify-center text-purple-600 font-bold">+</div>
                    <span class="text-sm font-medium text-gray-700">New Invoice</span>
                </a>
                <a href="{{ route('hrm.employees') }}" class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 hover:bg-gray-50">
                    <div class="h-8 w-8 rounded bg-amber-50 flex items-center justify-center text-amber-600 font-bold">+</div>
                    <span class="text-sm font-medium text-gray-700">Add Employee</span>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Attention Needed</h2>
            </div>
            <div class="p-6 space-y-3">
                @foreach(($data['alerts'] ?? [['type'=>'invoice','label'=>'Overdue invoices','count'=>0],['type'=>'task','label'=>'Overdue tasks','count'=>0],['type'=>'ticket','label'=>'Pending tickets','count'=>0]]) as $alert)
                <div class="flex items-center gap-3 rounded-lg border border-gray-100 p-3">
                    <div class="h-2 w-2 rounded-full @if($alert['type']==='invoice') bg-amber-400 @elseif($alert['type']==='task') bg-red-400 @else bg-blue-400 @endif"></div>
                    <span class="text-sm text-gray-700">{{ $alert['label'] ?? '' }}: {{ $alert['count'] ?? 0 }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
