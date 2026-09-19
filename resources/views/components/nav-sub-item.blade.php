@props(['href', 'label'])
<a href="{{ $href }}" class="flex items-center gap-3 rounded-md py-2 pl-9 pr-3 text-sm {{ request()->url() === $href ? 'text-primary-600 font-medium' : 'text-bodytext hover:text-ink' }}">{{ $label }}</a>
