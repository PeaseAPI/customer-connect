@props(['label', 'value', 'color'])
@php
    $bgClass = match($color) {
        'blue' => 'bg-blue-50 text-blue-600',
        'green' => 'bg-green-50 text-green-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'amber' => 'bg-amber-50 text-amber-600',
        default => 'bg-gray-50 text-gray-600',
    };
@endphp
<div class="bg-white overflow-hidden rounded-xl border border-gray-200 p-6">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <div class="h-10 w-10 rounded-lg {{ $bgClass }} flex items-center justify-center">
                {{ $slot }}
            </div>
        </div>
        <div class="ml-4 w-0 flex-1">
            <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">{{ $label }}</dt>
                <dd class="text-2xl font-bold text-gray-900">{{ $value }}</dd>
            </dl>
        </div>
    </div>
</div>
