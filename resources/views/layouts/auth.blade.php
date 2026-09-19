<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') - Customer Connect</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                colors: {
                    primary: { DEFAULT: '#4880FF', 50: '#F0F5FF', 100: '#ECF1F9', 200: '#D8E2FF', 300: '#B3C7FF', 400: '#7EA1FF', 500: '#4880FF', 600: '#3565D9', 700: '#2A50AE', 800: '#203C82', 900: '#16274F' },
                    surface: '#FAFAFB', stroke: '#E9EAEC', ink: '#29343D', bodytext: '#5A6A85',
                } } }
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
    @stack('scripts')
</head>
<body>
<section class="auth bg-base d-flex flex-wrap">
    <div class="auth-left d-lg-block d-none">
        <div class="d-flex align-items-center flex-column h-100 justify-content-center">
            <img src="{{ asset('assets/images/auth/auth-img.png') }}" alt="">
        </div>
    </div>
    <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
        <div class="max-w-464-px mx-auto w-100">
            <div>
                <a href="{{ url('/') }}" class="mb-40 max-w-290-px d-inline-block">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Customer Connect">
                </a>
            </div>
            @if(session('status'))
                <div class="alert alert-success radius-8 px-24 py-16 mb-24">{{ session('status') }}</div>
            @endif
            @yield('content')
            <p class="text-center text-sm text-secondary-light mt-32 mb-0">&copy; {{ date('Y') }} Customer Connect. All rights reserved.</p>
        </div>
    </div>
</section>

<script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script>
    function initializePasswordToggle(toggleSelector) {
        $(toggleSelector).on('click', function () {
            $(this).toggleClass("ri-eye-off-line");
            var input = $($(this).attr("data-toggle"));
            if (input.attr("type") === "password") { input.attr("type", "text"); }
            else { input.attr("type", "password"); }
        });
    }
    initializePasswordToggle('.toggle-password');
</script>
@stack('scripts')
</body>
</html>
