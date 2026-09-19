@props(['label', 'value', 'color', 'icon' => 'default', 'trend' => null, 'trendDir' => 'up'])
@php
    $bgClass = match($color) {
        'blue' => 'bg-primary-100 text-primary-500',
        'green' => 'bg-emerald-50 text-emerald-500',
        'purple' => 'bg-violet-50 text-violet-500',
        'amber' => 'bg-amber-50 text-amber-500',
        'red' => 'bg-red-50 text-red-500',
        default => 'bg-surface text-bodytext',
    };
    $iconSvg = match($icon) {
        'projects' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2 9.5V5.25A2.25 2.25 0 014.25 3h4.5A2.25 2.25 0 0111 5.25v4.5A2.25 2.25 0 018.75 12h-4.5A2.25 2.25 0 012 9.75v-.25zm11-4.25V5.25A2.25 2.25 0 0115.25 3h4.5A2.25 2.25 0 0122 5.25v4.5A2.25 2.25 0 0119.75 12h-4.5A2.25 2.25 0 0113 9.75v-.25zM2 18.75v-4.5A2.25 2.25 0 014.25 12h4.5A2.25 2.25 0 0111 14.25v4.5A2.25 2.25 0 018.75 21h-4.5A2.25 2.25 0 012 18.75zm11 0v-4.5A2.25 2.25 0 0115.25 12h4.5A2.25 2.25 0 0122 14.25v4.5A2.25 2.25 0 0119.75 21h-4.5A2.25 2.25 0 0113 18.75z"/>',
        'tasks' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'revenue' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'clients' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
        default => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
    };
@endphp
<div class="bg-white overflow-hidden rounded-xl border border-stroke p-5 transition-shadow hover:shadow-sm">
    <div class="flex items-center gap-4">
        <div class="flex-shrink-0">
            <div class="h-12 w-12 rounded-xl {{ $bgClass }} flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $iconSvg !!}</svg>
            </div>
        </div>
        <div class="w-0 flex-1">
            <dl>
                <dt class="text-sm font-normal text-bodytext truncate">{{ $label }}</dt>
                <dd class="text-[26px] leading-8 font-bold text-ink mt-0.5">{{ $value }}</dd>
            </dl>
        </div>
        @if($trend !== null)
        <span class="flex-shrink-0 inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold {{ $trendDir === 'up' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $trendDir === 'up' ? 'M4.5 15.75l7.5-7.5 7.5 7.5' : 'M19.5 8.25l-7.5 7.5-7.5-7.5' }}"/></svg>
            {{ $trend }}
        </span>
        @endif
    </div>
</div>