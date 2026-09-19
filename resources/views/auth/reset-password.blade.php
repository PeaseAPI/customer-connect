@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
    <h4 class="mb-12">Set New Password</h4>
    <p class="mb-32 text-secondary-light text-lg">Choose a strong password for your account</p>
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
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
                       class="form-control h-56-px bg-neutral-50 radius-12" placeholder="New password">
            </div>
            <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#password"></span>
            @error('password') <p class="text-danger text-sm mt-8 mb-0">{{ $message }}</p> @enderror
        </div>

        <div class="icon-field mb-20">
            <span class="icon top-50 translate-middle-y"><i class="ri-shield-keyhole-line"></i></span>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="form-control h-56-px bg-neutral-50 radius-12" placeholder="Confirm password">
        </div>

        <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12">Reset Password</button>
    </form>
@endsection
