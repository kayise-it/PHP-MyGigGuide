@extends('layouts.app')

@section('title', 'Forgot Password - My Gig Guide')
@section('description', 'Reset your My Gig Guide account password')

@section('content')
<div class="{{ $siteBrand->authPageClass() }}">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="flex justify-center mb-6">
                <div class="{{ $siteBrand->authHeaderIconWrapClass() }}">
                    <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
            </div>
            <h2 class="{{ $siteBrand->pageTitleClass() }} mb-2">Forgot Password?</h2>
            <p class="{{ $siteBrand->pageSubtitleClass() }} mb-0">No worries! Enter your email and we'll send you a reset link</p>
        </div>

        <div class="{{ $siteBrand->authCardClass() }}">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-300 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="{{ $siteBrand->formLabelClass() }}">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                            class="{{ $siteBrand->authInputClass() }} @error('email') border-red-400 @enderror" placeholder="Enter your email address" />
                    </div>
                    @error('email')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary w-full py-3 rounded-xl font-medium">Send Reset Link</button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm font-medium {{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }}">
                        ← Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
