<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') - Customer Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="h-full bg-gray-50">
<div class="min-h-screen flex flex-col items-center justify-center px-4">
    <div class="flex items-center gap-3 mb-8">
        <div class="h-10 w-10 rounded-lg bg-indigo-600 flex items-center justify-center">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349"/></svg>
        </div>
        <span class="text-xl font-bold text-gray-900">Customer Connect</span>
    </div>
    @if(session('status'))
        <div class="mb-4 w-full max-w-md rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    @yield('content')
</div>
</body>
</html>