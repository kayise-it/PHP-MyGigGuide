@extends('layouts.app')

@section('title', 'Account Activation Required - My Gig Guide')
@section('description', 'Please activate your account to continue using My Gig Guide.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-16 w-16 bg-purple-100 rounded-full flex items-center justify-center mb-6">
                <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Check Your Email</h2>
            <p class="text-gray-600 mb-8">We've sent you an activation link to verify your account.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-8">
            <div class="text-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Account Activation Required</h3>
                <p class="text-gray-600 text-sm">
                    Please check your email inbox and click the activation link to complete your registration.
                </p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mt-0.5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-blue-900 mb-1">Didn't receive the email?</h4>
                            <p class="text-sm text-blue-700">
                                Check your spam folder or request a new activation email below.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('activation.resend') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('email') border-red-500 @enderror"
                               placeholder="Enter your email address">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" 
                            class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors duration-200 font-medium">
                        Resend Activation Email
                    </button>
                </form>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Already activated? 
                        <a href="{{ route('login') }}" class="text-purple-600 hover:text-purple-700 font-medium">
                            Sign in here
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="text-sm text-gray-500">
                Need help? 
                <a href="{{ route('contact') }}" class="text-purple-600 hover:text-purple-700">
                    Contact Support
                </a>
            </p>
        </div>
    </div>
</div>
@endsection

