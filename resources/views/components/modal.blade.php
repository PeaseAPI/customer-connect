@props(['show', 'title', 'size' => 'md'])
<div x-show="{{ $show }}" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500/75" @click="{{ $show }} = false"></div>
        <div :class="{
            'sm:max-w-lg': $size === 'md',
            'sm:max-w-2xl': $size === 'lg',
            'sm:max-w-4xl': $size === 'xl',
        }" class="relative bg-white rounded-xl shadow-xl w-full p-6 transform transition-all">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                <button @click="{{ $show }} = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>