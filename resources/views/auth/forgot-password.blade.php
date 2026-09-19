@extends('layouts.auth')
@section('title', 'Forgot Password')
@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <h3 class="mb-4 f-w-500">Forgot Password</h3>

        <div class="form-group text-left">
            <label for="email">Email</label>
            <input tabindex="1" type="email" name="email" id="email" autofocus value="{{ old('email') }}"
                   placeholder="Email" class="form-control height-50 f-15 light_text @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-primary f-w-500 rounded w-100 height-50 f-18">
            Send Reset Link
        </button>
    </form>
@endsection
@section('outside_box')
    <p class="my-2 f-12">Remembered it? <a href="{{ route('login') }}" class="text-dark-grey">Back to Sign In</a></p>
@endsection
