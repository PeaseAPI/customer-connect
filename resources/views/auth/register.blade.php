@extends('layouts.auth')
@section('title', 'Register')
@section('content')
<div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-ink text-center">Create your account</h2>
        <p class="mt-2 text-sm text-bodytext text-center">Get started with Customer Connect CRM</p>
        <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-ink mb-1">Full name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-ink focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm"
                       placeholder="John Doe">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-ink mb-1">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-ink focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm"
                       placeholder="you@company.com">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-ink mb-1">Password</label>
                <input id="password" type="password" name="password" required
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-ink focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm"
                       placeholder="Min 8 characters">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-ink mb-1">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-ink focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm"
                       placeholder="Repeat your password">
            </div>
            <button type="submit" class="w-full rounded-lg bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                Create account
            </button>
        </form>
        <p class="mt-6 text-center text-sm text-bodytext">
            Already have an account? <a href="{{ route('login') }}" class="font-medium text-primary-500 hover:text-primary-500">Sign in</a>
        </p>
    </div>
</div>
@endsection
