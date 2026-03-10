@extends('layouts.app')

@section('title', 'Verify Email Address')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Verify Your Email Address
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                @if(auth()->check())
                    We've sent a verification link to <strong>{{ auth()->user()->email }}</strong>
                @else
                    Please check your email for a verification link
                @endif
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 mb-4">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    Check Your Email
                </h3>
                
                <p class="text-sm text-gray-600 mb-6">
                    Before proceeding, please check your email for a verification link. If you did not receive the email, you can request another one.
                </p>

                @if (session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.resend') }}" class="space-y-4">
                    @csrf
                    
                    @guest
                        <div class="text-left">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email address used for signup
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', session('pending_verification_email')) }}"
                                required
                                class="w-full px-4 py-3 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm text-gray-900 placeholder-gray-400"
                                placeholder="you@example.com"
                            >
                            <p class="mt-2 text-xs text-gray-500">
                                We use this to resend the verification link if you are not currently logged in.
                            </p>
                        </div>
                    @endguest

                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-200">
                        Resend Verification Email
                    </button>
                </form>

                @if(auth()->check())
                <div class="mt-6 text-center">
                    <a href="{{ route('profile.show') }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                        ← Back to Profile
                    </a>
                </div>
                @else
                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                        ← Back to Login
                    </a>
                </div>
                @endif
            </div>
        </div>

        <div class="text-center">
            <p class="text-xs text-gray-500">
                Didn't receive the email? Check your spam folder or try resending.
            </p>
        </div>
    </div>
</div>
@endsection
