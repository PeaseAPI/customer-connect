@props(['href', 'label'])
<a href="{{ $href }}" class="flex items-center gap-3 rounded-md py-2 pl-9 pr-3 text-sm {{ request()->url() === $href ? 'text-indigo-700 font-medium' : 'text-gray-600 hover:text-gray-900' }}">{{ $label }}</a>
