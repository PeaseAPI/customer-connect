<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - Customer Connect</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    {{-- WowDash template core styles --}}
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    {{-- App Tailwind utilities (loaded last so content views keep working) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
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
    </style>
    @stack('styles')
    @stack('scripts')
</head>
<body>
<aside class="sidebar">
    <button type="button" class="sidebar-close-btn"><i class="ri-close-line"></i></button>
    <div>
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Customer Connect" class="light-logo">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="Customer Connect" class="dark-logo">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="Customer Connect" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active-page' : '' }}">
                    <i class="ri-home-smile-line menu-icon"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sidebar-menu-group-title">CRM</li>
            <li class="dropdown {{ request()->routeIs('crm.*') ? 'open dropdown-open show' : '' }}">
                <a href="javascript:void(0)">
                    <i class="ri-user-heart-line menu-icon"></i>
                    <span>Manage CRM</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('crm.leads') }}" class="{{ request()->routeIs('crm.leads') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Leads</a></li>
                    <li><a href="{{ route('crm.clients') }}" class="{{ request()->routeIs('crm.clients') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Clients</a></li>
                    <li><a href="{{ route('crm.deals') }}" class="{{ request()->routeIs('crm.deals') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Deals</a></li>
                    <li><a href="{{ route('crm.pipelines') }}" class="{{ request()->routeIs('crm.pipelines') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i> Pipelines</a></li>
                </ul>
            </li>

            <li class="sidebar-menu-group-title">Projects</li>
            <li class="dropdown {{ request()->routeIs('projects.*') || request()->routeIs('tasks.*') ? 'open dropdown-open show' : '' }}">
                <a href="javascript:void(0)">
                    <i class="ri-briefcase-line menu-icon"></i>
                    <span>Manage Projects</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Projects</a></li>
                    <li><a href="{{ route('tasks.index') }}" class="{{ request()->routeIs('tasks.*') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Tasks</a></li>
                </ul>
            </li>

            <li class="sidebar-menu-group-title">Finance</li>
            <li class="dropdown {{ request()->routeIs('finance.*') ? 'open dropdown-open show' : '' }}">
                <a href="javascript:void(0)">
                    <i class="ri-money-dollar-circle-line menu-icon"></i>
                    <span>Manage Finance</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('finance.invoices') }}" class="{{ request()->routeIs('finance.invoices') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Invoices</a></li>
                    <li><a href="{{ route('finance.estimates') }}" class="{{ request()->routeIs('finance.estimates') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Estimates</a></li>
                    <li><a href="{{ route('finance.payments') }}" class="{{ request()->routeIs('finance.payments') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Payments</a></li>
                    <li><a href="{{ route('finance.expenses') }}" class="{{ request()->routeIs('finance.expenses') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i> Expenses</a></li>
                </ul>
            </li>

            <li class="sidebar-menu-group-title">HRM</li>
            <li class="dropdown {{ request()->routeIs('hrm.*') ? 'open dropdown-open show' : '' }}">
                <a href="javascript:void(0)">
                    <i class="ri-team-line menu-icon"></i>
                    <span>Manage HRM</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('hrm.employees') }}" class="{{ request()->routeIs('hrm.employees') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Employees</a></li>
                    <li><a href="{{ route('hrm.attendance') }}" class="{{ request()->routeIs('hrm.attendance') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Attendance</a></li>
                    <li><a href="{{ route('hrm.leaves') }}" class="{{ request()->routeIs('hrm.leaves') ? 'active-page' : '' }}"><i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Leaves</a></li>
                </ul>
            </li>

            <li class="sidebar-menu-group-title">System</li>
            <li>
                <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active-page' : '' }}">
                    <i class="ri-settings-3-line menu-icon"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
<main class="dashboard-main">
    <div class="navbar-header">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <div class="d-flex flex-wrap align-items-center gap-4">
                    <button type="button" class="sidebar-toggle">
                        <i class="ri-menu-2-line icon text-2xl non-active"></i>
                        <i class="iconoir-arrow-right icon text-2xl active"></i>
                    </button>
                    <button type="button" class="sidebar-mobile-toggle">
                        <i class="ri-menu-2-line icon"></i>
                    </button>
                    <form class="navbar-search" action="#">
                        <input type="text" name="search" placeholder="Search">
                        <i class="ri-search-line icon"></i>
                    </form>
                </div>
            </div>
            <div class="col-auto">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="dropdown">
                        <button class="has-indicator w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center" type="button" data-bs-toggle="dropdown">
                            <i class="ri-notification-3-line text-primary-light text-xl"></i>
                        </button>
                        <div class="dropdown-menu to-top dropdown-menu-lg p-0">
                            <div class="m-16 py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                                <div>
                                    <h6 class="text-lg text-primary-light fw-semibold mb-0">Notifications</h6>
                                </div>
                                <span class="text-primary-600 fw-semibold text-lg w-40-px h-40-px rounded-circle bg-base d-flex justify-content-center align-items-center">0</span>
                            </div>
                            <div class="py-16 text-center">
                                <p class="mb-0 text-sm text-secondary-light">No new notifications</p>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="d-flex align-items-center gap-2 border-0 bg-transparent" type="button" data-bs-toggle="dropdown">
                            <span class="w-40-px h-40-px bg-primary-50 text-primary-600 rounded-circle d-flex justify-content-center align-items-center fw-semibold text-md">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</span>
                            <span class="d-none d-sm-inline text-md fw-semibold text-secondary-light">{{ auth()->user()->name ?? 'User' }}</span>
                            <i class="ri-arrow-down-s-line text-secondary-light"></i>
                        </button>
                        <div class="dropdown-menu to-top dropdown-menu-sm">
                            <div class="py-12 px-16 radius-8 bg-primary-50 mb-16">
                                <h6 class="text-lg text-primary-light fw-semibold mb-0">{{ auth()->user()->name ?? 'User' }}</h6>
                                <span class="text-sm text-secondary-light">{{ auth()->user()->email ?? '' }}</span>
                            </div>
                            <ul class="to-top-list">
                                <li>
                                    <a class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-primary d-flex align-items-center gap-3" href="{{ route('settings.index') }}">
                                        <i class="ri-settings-3-line icon text-xl"></i> Settings
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">@csrf
                                        <button type="submit" class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-danger d-flex align-items-center gap-3 bg-transparent border-0 w-100">
                                            <i class="ri-logout-box-r-line icon text-xl"></i> Log Out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-main-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
            <h6 class="fw-semibold mb-0">@yield('pageTitle', 'Dashboard')</h6>
            <ul class="d-flex align-items-center gap-2">
                <li class="fw-medium">
                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                        <i class="ri-home-smile-line icon text-lg"></i> Home
                    </a>
                </li>
                <li>-</li>
                <li class="fw-medium">@yield('pageTitle', 'Dashboard')</li>
            </ul>
        </div>

        @if(session('success'))
            <div class="alert alert-success radius-8 px-24 py-16 mb-24 d-flex align-items-center justify-content-between">
                <span>{{ session('success') }}</span>
                <button type="button" class="close-alert border-0 bg-transparent" data-bs-dismiss="alert" aria-label="Close"><i class="ri-close-line"></i></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger radius-8 px-24 py-16 mb-24 d-flex align-items-center justify-content-between">
                <span>{{ session('error') }}</span>
                <button type="button" class="close-alert border-0 bg-transparent" data-bs-dismiss="alert" aria-label="Close"><i class="ri-close-line"></i></button>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="d-footer">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <p class="mb-0">&copy; {{ date('Y') }} Customer Connect. All Rights Reserved.</p>
            </div>
            <div class="col-auto">
                <p class="mb-0">Powered by <span class="text-primary-600">Customer Connect</span></p>
            </div>
        </div>
    </footer>
</main>

{{-- jQuery library js --}}
<script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
{{-- Bootstrap js --}}
<script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
{{-- main js --}}
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
