@props(['field', 'errors' => null])
@php
    $errorList = $errors ?? $errors ?? [];
    $message = is_array($errorList) ? ($errorList[$field] ?? null) : null;
    if (!$message && session('errors')) {
        $errorBag = session('errors');
        $message = $errorBag->has($field) ? $errorBag->first($field) : null;
    }
@endphp
@if($message)
<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@endif
