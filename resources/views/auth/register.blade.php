@extends('layouts.auth')
@section('title', 'Register')
@section('content')
    <h4 class="mb-12">Create your account</h4>
    <p class="mb-32 text-secondary-light text-lg">Get started with Customer Connect CRM</p>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y"><i class="ri-user-3-line"></i></span>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required
                   class="form-control h-56-px bg-neutral-50 radius-12" placeholder="Full name">
        </div>
        @error('name') <p class="text-danger text-sm mb-16">{{ $message }}</p> @enderror

        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y"><i class="ri-mail-line"></i></span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   class="form-control h-56-px bg-neutral-50 radius-12" placeholder="you@company.com">
        </div>
        @error('email') <p class="text-danger text-sm mb-16">{{ $message }}</p> @enderror

        <div class="position-relative mb-16">
            <div class="icon-field">
                <span class="icon top-50 translate-middle-y"><i class="ri-lock-password-line"></i></span>
                <input id="password" type="password" name="password" required
                       class="form-control h-56-px bg-neutral-50 radius-12" placeholder="Password (min 8 characters)">
            </div>
            <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#password"></span>
            @error('password') <p class="text-danger text-sm mt-8 mb-0">{{ $message }}</p> @enderror
        </div>

        <div class="icon-field mb-20">
            <span class="icon top-50 translate-middle-y"><i class="ri-shield-keyhole-line"></i></span>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="form-control h-56-px bg-neutral-50 radius-12" placeholder="Confirm password">
        </div>

        <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12 mt-8">Create account</button>
    </form>
    <div class="mt-32 text-center text-sm">
        <p class="mb-0">Already have an account? <a href="{{ route('login') }}" class="text-primary-600 fw-semibold">Sign In</a></p>
    </div>
@endsection
