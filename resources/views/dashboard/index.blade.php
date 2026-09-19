@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div>
    <div class="mb-7 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-[26px] font-bold text-ink">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h1>
        <p class="mt-1.5 text-sm text-bodytext">Here's what's happening across your workspace today.</p>
    </div>
    <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-white border border-stroke px-4 py-2 text-sm font-medium text-bodytext">
        <svg class="h-4 w-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
        {{ now()->format('l, F j, Y') }}
    </span>
</div>

    @php $overview = $data['overview'] ?? []; @endphp
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <x-stat-card icon="projects" label="Projects" :value="number_format($overview['projects_count'] ?? 0)" color="blue" />
        <x-stat-card icon="tasks" label="Completed Tasks" :value="number_format($overview['pending_tasks'] ?? 0)" color="green" />
        <x-stat-card icon="revenue" label="Revenue (YTD)" :value="'$' . number_format($overview['revenue'] ?? 0, 2)" color="purple" />
        <x-stat-card icon="clients" label="Clients" :value="number_format($overview['clients_count'] ?? 0)" color="amber" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-xl border border-stroke">
            <div class="border-b border-stroke px-6 py-4">
                <h2 class="text-lg font-semibold text-ink">Quick Actions</h2>
            </div>
            <div class="p-6 grid grid-cols-2 gap-3">
                <a href="{{ route('crm.leads') }}" class="flex items-center gap-3 rounded-lg border border-stroke p-4 hover:bg-surface transition-colors">
                    <div class="h-8 w-8 rounded bg-blue-50 flex items-center justify-center"><svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg></div>
                    <span class="text-sm font-medium text-ink">New Lead</span>
                </a>
                <a href="{{ route('projects.index') }}" class="flex items-center gap-3 rounded-lg border border-stroke p-4 hover:bg-surface transition-colors">
                    <div class="h-8 w-8 rounded bg-green-50 flex items-center justify-center"><svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg></div>
                    <span class="text-sm font-medium text-ink">New Project</span>
                </a>
                <a href="{{ route('finance.invoices') }}" class="flex items-center gap-3 rounded-lg border border-stroke p-4 hover:bg-surface transition-colors">
                    <div class="h-8 w-8 rounded bg-purple-50 flex items-center justify-center"><svg class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg></div>
                    <span class="text-sm font-medium text-ink">New Invoice</span>
                </a>
                <a href="{{ route('hrm.employees') }}" class="flex items-center gap-3 rounded-lg border border-stroke p-4 hover:bg-surface transition-colors">
                    <div class="h-8 w-8 rounded bg-amber-50 flex items-center justify-center"><svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg></div>
                    <span class="text-sm font-medium text-ink">Add Employee</span>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-stroke">
            <div class="border-b border-stroke px-6 py-4">
                <h2 class="text-lg font-semibold text-ink">Attention Needed</h2>
            </div>
            <div class="p-6 space-y-3">
                @foreach(($data['alerts'] ?? [['type'=>'invoice','label'=>'Overdue invoices','count'=>0],['type'=>'task','label'=>'Overdue tasks','count'=>0],['type'=>'ticket','label'=>'Pending tickets','count'=>0]]) as $alert)
                <div class="flex items-center justify-between rounded-lg border border-stroke p-3">
                    <div class="flex items-center gap-3">
                        <div class="h-2 w-2 rounded-full @if($alert['type']==='invoice') bg-amber-400 @elseif($alert['type']==='task') bg-red-400 @else bg-blue-400 @endif"></div>
                        <span class="text-sm text-ink">{{ $alert['label'] ?? '' }}</span>
                    </div>
                    <span class="text-sm font-semibold text-ink">{{ $alert['count'] ?? 0 }}</span>
                </div>
                @endforeach
                @if(empty($data['alerts']))
                <p class="text-sm text-bodytext text-center py-4">Everything looks good! No items need attention.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
