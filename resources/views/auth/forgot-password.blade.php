@extends('layouts.auth')
@section('title', 'Forgot Password')
@section('content')
    <h4 class="mb-12">Forgot Password</h4>
    <p class="mb-32 text-secondary-light text-lg">Enter your email and we'll send you a reset link</p>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="icon-field mb-20">
            <span class="icon top-50 translate-middle-y"><i class="ri-mail-line"></i></span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   class="form-control h-56-px bg-neutral-50 radius-12" placeholder="you@company.com">
        </div>
        @error('email') <p class="text-danger text-sm mb-16">{{ $message }}</p> @enderror
        <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12">Send Reset Link</button>
    </form>
    <div class="mt-32 text-center text-sm">
        <p class="mb-0">Remembered it? <a href="{{ route('login') }}" class="text-primary-600 fw-semibold">Back to Sign In</a></p>
    </div>
@endsection
