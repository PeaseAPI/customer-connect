<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">

    <!-- Original-system (Worksuite) public/auth styling -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/helper.css') }}">
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('assets/front/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">

    <title>@yield('title', 'Login') - {{ \App\Support\Branding::appName() }}</title>

    <style>
        .login_header {
            background-color: {{ \App\Support\Branding::logoBackgroundColor() }} !important;
        }
        .login_header img {
            height: 42px;
        }
    </style>
    @stack('styles')
</head>

<body>
<header class="px-4 bg-white sticky-top d-flex justify-content-center align-items-center login_header">
    <img class="mr-2 rounded" src="{{ \App\Support\Branding::logoUrl() }}" alt="Logo"/>
    <h3 class="mb-0 pl-1">{{ \App\Support\Branding::appName() }}</h3>
</header>

<section class="py-5 bg-grey login_section"
         @if(\App\Support\Branding::loginBackgroundUrl()) style="background: url('{{ \App\Support\Branding::loginBackgroundUrl() }}') center center/cover no-repeat;" @endif>
    <div class="container">
        <div class="row">
            <div class="text-center col-md-12">

                <div class="mx-auto text-center bg-white rounded login_box">
                    @if(session('status'))
                        <div class="alert alert-success text-left">{{ session('status') }}</div>
                    @endif
                    @yield('content')
                </div>

                @yield('outside_box')

            </div>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.closest('.input-group').querySelector('input');
            var icon = btn.querySelector('i');
            if (!input) { return; }
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) { icon.classList.replace('ri-eye-line', 'ri-eye-off-line'); }
            } else {
                input.type = 'password';
                if (icon) { icon.classList.replace('ri-eye-off-line', 'ri-eye-line'); }
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>
