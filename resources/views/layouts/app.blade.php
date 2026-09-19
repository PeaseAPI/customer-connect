<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - Customer Connect</title>
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
    <style>
        [x-cloak]{display:none!important}
        ::-webkit-scrollbar{width:6px;height:6px}
        ::-webkit-scrollbar-thumb{background:#DDE2E9;border-radius:3px}
    </style>
    @stack('scripts')
</head>
<body class="h-full bg-surface font-sans text-bodytext antialiased">
<div class="min-h-screen flex" x-data="{ sidebarOpen: true, mobileNav: false }">
    {{-- ===== Sidebar (desktop) ===== --}}
    <aside :class="sidebarOpen ? 'w-64' : 'w-[76px]'" class="hidden lg:flex flex-col bg-white border-r border-stroke transition-all duration-200 flex-shrink-0 h-screen sticky top-0 overflow-hidden">
        <div class="flex h-[72px] items-center gap-3 px-5 flex-shrink-0">
            <div class="h-9 w-9 rounded-xl bg-primary-500 flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349"/></svg>
            </div>
            <span x-show="sidebarOpen" x-transition class="text-[17px] font-extrabold text-ink whitespace-nowrap">Customer Connect</span>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 pb-4 space-y-0.5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Dashboard</span>
            </a>

            <p x-show="sidebarOpen" class="px-3.5 pt-5 pb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">CRM</p>
            <a href="{{ route('crm.leads') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('crm.leads') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Leads</span>
            </a>
            <a href="{{ route('crm.clients') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('crm.clients') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Clients</span>
            </a>
            <a href="{{ route('crm.deals') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('crm.deals') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Deals</span>
            </a>
            <a href="{{ route('crm.pipelines') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('crm.pipelines') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M6 9.75h12M9.75 15h4.5M11.25 20.25h1.5"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Pipelines</span>
            </a>

            <p x-show="sidebarOpen" class="px-3.5 pt-5 pb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Projects</p>
            <a href="{{ route('projects.index') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('projects.*') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Projects</span>
            </a>
            <a href="{{ route('tasks.index') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('tasks.*') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Tasks</span>
            </a>

            <p x-show="sidebarOpen" class="px-3.5 pt-5 pb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Finance</p>
            <a href="{{ route('finance.invoices') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('finance.invoices') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Invoices</span>
            </a>
            <a href="{{ route('finance.estimates') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('finance.estimates') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Estimates</span>
            </a>
            <a href="{{ route('finance.payments') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('finance.payments') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Payments</span>
            </a>
            <a href="{{ route('finance.expenses') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('finance.expenses') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Expenses</span>
            </a>

            <p x-show="sidebarOpen" class="px-3.5 pt-5 pb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">HRM</p>
            <a href="{{ route('hrm.employees') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('hrm.employees') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Employees</span>
            </a>
            <a href="{{ route('hrm.attendance') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('hrm.attendance') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Attendance</span>
            </a>
            <a href="{{ route('hrm.leaves') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('hrm.leaves') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Leaves</span>
            </a>

            <p x-show="sidebarOpen" class="px-3.5 pt-5 pb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">System</p>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('settings.*') ? 'bg-primary-100 text-primary-600' : 'text-bodytext hover:bg-surface hover:text-ink' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Settings</span>
            </a>
        </nav>
    </aside>
    {{-- ===== Sidebar (mobile drawer) ===== --}}
    <div x-show="mobileNav" x-cloak class="fixed inset-0 z-40 lg:hidden">
        <div class="fixed inset-0 bg-gray-900/50" @click="mobileNav = false"></div>
        <div x-show="mobileNav" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 w-64 bg-white overflow-y-auto">
            <div class="flex h-[72px] items-center gap-3 px-5">
                <div class="h-9 w-9 rounded-xl bg-primary-500 flex items-center justify-center">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349"/></svg>
                </div>
                <span class="text-[17px] font-extrabold text-ink">Customer Connect</span>
            </div>
            <nav class="px-3 pb-4 space-y-0.5 text-sm">
                <a href="{{ route('dashboard') }}" class="block rounded-lg px-3.5 py-2.5 font-medium {{ request()->routeIs('dashboard') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Dashboard</a>
                <p class="px-3.5 pt-4 pb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">CRM</p>
                <a href="{{ route('crm.leads') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('crm.leads') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Leads</a>
                <a href="{{ route('crm.clients') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('crm.clients') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Clients</a>
                <a href="{{ route('crm.deals') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('crm.deals') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Deals</a>
                <a href="{{ route('crm.pipelines') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('crm.pipelines') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Pipelines</a>
                <p class="px-3.5 pt-4 pb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Projects</p>
                <a href="{{ route('projects.index') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('projects.*') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Projects</a>
                <a href="{{ route('tasks.index') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('tasks.*') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Tasks</a>
                <p class="px-3.5 pt-4 pb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Finance</p>
                <a href="{{ route('finance.invoices') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('finance.invoices') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Invoices</a>
                <a href="{{ route('finance.estimates') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('finance.estimates') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Estimates</a>
                <a href="{{ route('finance.payments') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('finance.payments') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Payments</a>
                <a href="{{ route('finance.expenses') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('finance.expenses') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Expenses</a>
                <p class="px-3.5 pt-4 pb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">HRM</p>
                <a href="{{ route('hrm.employees') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('hrm.employees') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Employees</a>
                <a href="{{ route('hrm.attendance') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('hrm.attendance') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Attendance</a>
                <a href="{{ route('hrm.leaves') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('hrm.leaves') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Leaves</a>
                <p class="px-3.5 pt-4 pb-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">System</p>
                <a href="{{ route('settings.index') }}" class="block rounded-lg px-3.5 py-2.5 {{ request()->routeIs('settings.*') ? 'bg-primary-100 text-primary-600' : 'text-bodytext' }}">Settings</a>
            </nav>
        </div>
    </div>
    {{-- ===== Main column ===== --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-[72px] bg-white border-b border-stroke flex items-center justify-between pl-4 pr-5 lg:px-6 flex-shrink-0 sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button @click="mobileNav = true" class="lg:hidden text-bodytext hover:text-ink">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <div class="hidden md:flex items-center w-72 lg:w-96 h-10 rounded-full bg-primary-100 px-4">
                    <svg class="h-4 w-4 text-bodytext/60 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" placeholder="Search..." class="bg-transparent border-0 outline-none focus:ring-0 text-sm text-ink placeholder:text-bodytext/60 w-full ml-2">
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button class="relative h-10 w-10 rounded-full hover:bg-surface flex items-center justify-center text-bodytext">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 border-2 border-white"></span>
                </button>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2.5 rounded-full hover:bg-surface py-1.5 pl-1.5 pr-3">
                        <div class="h-9 w-9 rounded-full bg-primary-500 flex items-center justify-center text-white font-semibold text-xs">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</div>
                        <div class="hidden sm:block text-left leading-tight">
                            <p class="text-sm font-semibold text-ink">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-[11px] text-bodytext">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                        <svg class="h-4 w-4 text-bodytext" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-stroke py-1.5 z-50">
                        <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-bodytext hover:bg-surface hover:text-ink">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Settings
                        </a>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        <main class="flex-1 p-5 lg:p-7">
            @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
