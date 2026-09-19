@props(['icon', 'href', 'active', 'label'])
<a href="{{ $href }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium {{ $active ? 'bg-primary-100 text-primary-600' : 'text-ink hover:bg-surface' }}">
    {{ $slot }}
    <span>{{ $label }}</span>
</a>
