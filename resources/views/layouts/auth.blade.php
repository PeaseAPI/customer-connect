<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') - Customer Connect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { DEFAULT: '#4880FF', 50: '#F0F5FF', 100: '#ECF1F9', 200: '#D8E2FF', 300: '#B3C7FF', 400: '#7EA1FF', 500: '#4880FF', 600: '#3565D9', 700: '#2A50AE', 800: '#203C82', 900: '#16274F' },
                        surface: '#FAFAFB',
                        stroke: '#E9EAEC',
                        ink: '#29343D',
                        bodytext: '#5A6A85',
                    },
                },
            },
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
    @stack('scripts')
</head>
<body class="h-full bg-surface font-sans text-bodytext antialiased">
<div class="min-h-full flex flex-col items-center justify-center px-4 py-12">
    <div class="flex items-center gap-3 mb-8">
        <div class="h-11 w-11 rounded-xl bg-primary-500 flex items-center justify-center shadow-sm">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349"/></svg>
        </div>
        <span class="text-xl font-extrabold text-ink">Customer Connect</span>
    </div>
    @if(session('status'))
        <div class="mb-4 w-full max-w-md rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm font-medium text-green-700">{{ session('status') }}</div>
    @endif
    @yield('content')
    <p class="mt-8 text-xs text-bodytext/70">&copy; {{ date('Y') }} Customer Connect. All rights reserved.</p>
</div>
</body>
</html>