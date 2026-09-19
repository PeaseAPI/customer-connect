@extends('layouts.auth')
@section('title', 'Sign Up')
@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <h3 class="mb-4 f-w-500">Sign Up</h3>

        <div class="form-group text-left">
            <label for="name">Full name</label>
            <input tabindex="1" type="text" name="name" id="name" autofocus value="{{ old('name') }}"
                   placeholder="Full name" class="form-control height-50 f-15 light_text @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group text-left">
            <label for="email">Email</label>
            <input tabindex="2" type="email" name="email" id="email" value="{{ old('email') }}"
                   placeholder="Email" class="form-control height-50 f-15 light_text @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group text-left">
            <label for="password">Password</label>
            <input tabindex="3" type="password" name="password" id="password"
                   placeholder="Password (min 8 characters)"
                   class="form-control height-50 f-15 light_text @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group text-left">
            <label for="password_confirmation">Confirm Password</label>
            <input tabindex="4" type="password" name="password_confirmation" id="password_confirmation"
                   placeholder="Confirm Password"
                   class="form-control height-50 f-15 light_text @error('password_confirmation') is-invalid @enderror">
            @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-primary f-w-500 rounded w-100 height-50 f-18">
            Sign Up
        </button>
    </form>
@endsection
@section('outside_box')
    <p class="my-2 f-12">Already have an account? <a href="{{ route('login') }}" class="text-dark-grey">Sign In</a></p>
@endsection
