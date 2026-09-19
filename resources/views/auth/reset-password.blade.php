@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <h3 class="mb-4 f-w-500">Reset Password</h3>

        <div class="form-group text-left">
            <label for="email">Email</label>
            <input tabindex="1" type="email" name="email" id="email" autofocus value="{{ old('email') }}"
                   placeholder="Email" class="form-control height-50 f-15 light_text @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group text-left">
            <label for="password">New Password</label>
            <input tabindex="2" type="password" name="password" id="password" placeholder="Password"
                   class="form-control height-50 f-15 light_text @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group text-left">
            <label for="password_confirmation">Confirm Password</label>
            <input tabindex="3" type="password" name="password_confirmation" id="password_confirmation"
                   placeholder="Confirm Password"
                   class="form-control height-50 f-15 light_text @error('password_confirmation') is-invalid @enderror">
            @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-primary f-w-500 rounded w-100 height-50 f-18">
            Reset Password
        </button>
    </form>
@endsection
@section('outside_box')
    <p class="my-2 f-12">Remembered it? <a href="{{ route('login') }}" class="text-dark-grey">Back to Sign In</a></p>
@endsection
