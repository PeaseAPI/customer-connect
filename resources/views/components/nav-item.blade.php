@props(['icon', 'href', 'active', 'label'])
<a href="{{ $href }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium {{ $active ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100' }}">
    {{ $slot }}
    <span>{{ $label }}</span>
</a>
