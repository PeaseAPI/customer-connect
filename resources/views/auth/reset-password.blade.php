@extends('layouts.app')
@section('title', 'Reset Password')
@section('content')
<div class="flex min-h-[calc(100vh-8rem)] items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-ink text-center mb-6">Set New Password</h2>
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="email" class="block text-sm font-medium text-ink mb-1">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-ink focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-ink mb-1">New Password</label>
                    <input id="password" type="password" name="password" required
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-ink focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-ink mb-1">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-ink focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm">
                </div>
                <button type="submit" class="w-full rounded-lg bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Reset Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
