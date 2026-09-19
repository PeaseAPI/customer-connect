@props(['pagination'])
@php
    $currentPage = $pagination['current_page'] ?? 1;
    $lastPage = $pagination['last_page'] ?? 1;
    $total = $pagination['total'] ?? 0;
    if ($lastPage <= 1) return;
    $queryParams = array_filter(request()->except('page'));
@endphp
<div class="mt-4 flex items-center justify-between border-t border-stroke pt-4">
    <p class="text-sm text-bodytext">
        Showing page {{ $currentPage }} of {{ $lastPage }} <span class="text-gray-400">({{ $total }} total)</span>
    </p>
    <div class="flex items-center gap-1">
        @if($currentPage > 1)
        <a href="{{ request()->url() . '?' . http_build_query(array_merge($queryParams, ['page' => 1])) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-ink hover:bg-surface">&laquo;</a>
        <a href="{{ request()->url() . '?' . http_build_query(array_merge($queryParams, ['page' => $currentPage - 1])) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-ink hover:bg-surface">&lsaquo; Prev</a>
        @endif

        @php
            $start = max(1, $currentPage - 2);
            $end = min($lastPage, $currentPage + 2);
            if ($start > 1) echo '<span class="px-2 text-gray-400">...</span>';
        @endphp

        @for($i = $start; $i <= $end; $i++)
        <a href="{{ request()->url() . '?' . http_build_query(array_merge($queryParams, ['page' => $i])) }}" class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $i === $currentPage ? 'bg-primary-500 text-white' : 'border border-gray-300 text-ink hover:bg-surface' }}">{{ $i }}</a>
        @endfor

        @if($end < $lastPage)
        <span class="px-2 text-gray-400">...</span>
        @endif

        @if($currentPage < $lastPage)
        <a href="{{ request()->url() . '?' . http_build_query(array_merge($queryParams, ['page' => $currentPage + 1])) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-ink hover:bg-surface">Next &rsaquo;</a>
        <a href="{{ request()->url() . '?' . http_build_query(array_merge($queryParams, ['page' => $lastPage])) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-ink hover:bg-surface">&raquo;</a>
        @endif
    </div>
</div>
