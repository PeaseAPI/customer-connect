<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Customer Connect') - Customer Connect CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full">
    <div class="min-h-screen flex" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-0 overflow-hidden'"
               class="bg-white border-r border-gray-200 transition-all duration-300 flex-shrink-0">
            <div class="flex h-full flex-col">
                <!-- Logo -->
                <div class="flex h-16 items-center gap-2 px-6 border-b border-gray-200">
                    <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016A3.001 3.001 0 0021 9.349m-18 0V6.75a3 3 0 013-3h12a3 3 0 013 3v2.599"/>
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="text-lg font-semibold text-gray-900">Customer Connect</span>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
                    <x-nav-item icon="dashboard" href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" label="Dashboard" />
                    <x-nav-item icon="users" href="{{ route('crm.leads') }}" :active="request()->routeIs('crm.*')" label="CRM">
                        <x-nav-sub-item href="{{ route('crm.leads') }}" label="Leads" />
                        <x-nav-sub-item href="{{ route('crm.clients') }}" label="Clients" />
                        <x-nav-sub-item href="{{ route('crm.deals') }}" label="Deals" />
                        <x-nav-sub-item href="{{ route('crm.pipelines') }}" label="Pipelines" />
                    </x-nav-item>
                    <x-nav-item icon="folder" href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*') || request()->routeIs('tasks.*')" label="Projects">
                        <x-nav-sub-item href="{{ route('projects.index') }}" label="All Projects" />
                        <x-nav-sub-item href="{{ route('tasks.index') }}" label="All Tasks" />
                    </x-nav-item>
                    <x-nav-item icon="currency" href="{{ route('finance.invoices') }}" :active="request()->routeIs('finance.*')" label="Finance">
                        <x-nav-sub-item href="{{ route('finance.invoices') }}" label="Invoices" />
                        <x-nav-sub-item href="{{ route('finance.estimates') }}" label="Estimates" />
                        <x-nav-sub-item href="{{ route('finance.payments') }}" label="Payments" />
                        <x-nav-sub-item href="{{ route('finance.expenses') }}" label="Expenses" />
                    </x-nav-item>
                    <x-nav-item icon="people" href="{{ route('hrm.employees') }}" :active="request()->routeIs('hrm.*')" label="HRM">
                        <x-nav-sub-item href="{{ route('hrm.employees') }}" label="Employees" />
                        <x-nav-sub-item href="{{ route('hrm.attendance') }}" label="Attendance" />
                        <x-nav-sub-item href="{{ route('hrm.leaves') }}" label="Leaves" />
                    </x-nav-item>
                    <x-nav-item icon="cog" href="{{ route('settings.index') }}" :active="request()->routeIs('settings.*')" label="Settings" />
                </nav>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top bar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ auth()->user()->name ?? 'Guest' }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Sign out</button>
                    </form>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('status'))
                    <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
