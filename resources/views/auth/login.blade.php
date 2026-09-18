@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="flex min-h-[calc(100vh-8rem)] items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="mx-auto h-12 w-12 rounded-xl bg-indigo-600 flex items-center justify-center mb-4">
                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
                <p class="mt-2 text-sm text-gray-500">Sign in to your Customer Connect account</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 sm:text-sm"
                           placeholder="you@company.com">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" type="password" name="password" required
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 sm:text-sm"
                           placeholder="Enter your password">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Forgot password?</a>
                </div>

                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                    Sign in
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Don't have an account? <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Sign up</a>
            </p>
        </div>
    </div>
</div>
@endsection
