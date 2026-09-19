@extends('layouts.auth')
@section('title', 'Sign In')
@section('content')
    <h4 class="mb-12">Sign In to your Account</h4>
    <p class="mb-32 text-secondary-light text-lg">Welcome back! please enter your details</p>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y"><i class="ri-mail-line"></i></span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="form-control h-56-px bg-neutral-50 radius-12" placeholder="you@company.com">
        </div>
        @error('email') <p class="text-danger text-sm mb-16">{{ $message }}</p> @enderror

        <div class="position-relative mb-20">
            <div class="icon-field">
                <span class="icon top-50 translate-middle-y"><i class="ri-lock-password-line"></i></span>
                <input id="password" type="password" name="password" required
                       class="form-control h-56-px bg-neutral-50 radius-12" placeholder="Enter your password">
            </div>
            <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#password"></span>
            @error('password') <p class="text-danger text-sm mt-8 mb-0">{{ $message }}</p> @enderror
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-20">
            <div class="form-check style-check d-flex align-items-center">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label line-height-1 fw-medium text-secondary-light" for="remember">Remember me</label>
            </div>
            <a href="{{ route('password.request') }}" class="text-primary-600 fw-medium">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12 mt-16">Sign In</button>
    </form>
    <div class="mt-32 text-center text-sm">
        <p class="mb-0">Don't have an account? <a href="{{ route('register') }}" class="text-primary-600 fw-semibold">Sign Up</a></p>
    </div>
@endsection
