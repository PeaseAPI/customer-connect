@extends('layouts.auth')
@section('title', 'Sign In')
@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <h3 class="mb-4 f-w-500">Sign In</h3>

        <div class="form-group text-left">
            <label for="email">Email</label>
            <input tabindex="1" type="email" name="email" id="email" autofocus value="{{ old('email') }}"
                   placeholder="Email" class="form-control height-50 f-15 light_text @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group text-left">
            <label for="password">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Password" tabindex="2"
                       class="form-control height-50 f-15 light_text @error('password') is-invalid @enderror">
                <div class="input-group-append">
                    <button type="button" title="View Password"
                            class="btn btn-outline-secondary border-grey height-50 toggle-password">
                        <i class="ri-eye-line"></i>
                    </button>
                </div>
            </div>
            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="forgot_pswd mb-3">
            <a href="{{ route('password.request') }}">Forgot Password</a>
        </div>

        <div class="form-group text-left">
            <input id="remember" class="cursor-pointer" type="checkbox" name="remember">
            <label for="remember" class="cursor-pointer">Remember Me</label>
        </div>

        <button type="submit" class="btn-primary f-w-500 rounded w-100 height-50 f-18">
            Login
        </button>
    </form>
@endsection
@section('outside_box')
    <p class="my-2 f-12">Don't have an account? <a href="{{ route('register') }}" class="text-dark-grey">Sign Up</a></p>
@endsection
