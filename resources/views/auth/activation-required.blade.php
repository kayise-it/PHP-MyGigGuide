@extends('layouts.app')

@section('title', 'Account Activation Required - My Gig Guide')
@section('description', 'Please activate your account to continue using My Gig Guide.')

@section('content')
<div class="{{ $siteBrand->authPageClass() }}">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-16 w-16 {{ $siteBrand->isRogues ? 'bg-sky-500/20' : 'bg-indigo-500/20' }} rounded-full flex items-center justify-center mb-6">
                <svg class="h-8 w-8 {{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="{{ $siteBrand->pageTitleClass() }} mb-2">Check Your Email</h2>
            <p class="{{ $siteBrand->pageSubtitleClass() }} mb-0">We've sent you an activation link to verify your account.</p>
        </div>

        <div class="{{ $siteBrand->authCardClass() }}">
            <div class="text-center mb-6">
                <h3 class="text-lg font-semibold text-white mb-2">Account Activation Required</h3>
                <p class="{{ $siteBrand->detailMutedTextClass() }} text-sm">
                    Please check your email inbox and click the activation link to complete your registration.
                </p>
            </div>

            @if(session('success'))
                <div class="bg-green-500/10 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg mb-6">{{ session('error') }}</div>
            @endif

            <div class="space-y-4">
                <div class="{{ $siteBrand->searchResultsBannerClass() }} mb-0">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 {{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }} mt-0.5 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-white mb-1">Didn't receive the email?</h4>
                            <p class="text-sm {{ $siteBrand->searchResultsTextClass() }}">Check your spam folder or request a new activation email below.</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('activation.resend') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="{{ $siteBrand->formLabelClass() }}">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="{{ $siteBrand->formInputClass() }} @error('email') border-red-400 @enderror" placeholder="Enter your email address">
                        @error('email')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-primary w-full py-3 rounded-xl font-medium">Resend Activation Email</button>
                </form>

                <div class="text-center">
                    <p class="text-sm {{ $siteBrand->detailMutedTextClass() }}">
                        Already activated?
                        <a href="{{ route('login') }}" class="{{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }} font-medium">Sign in here</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="text-sm {{ $siteBrand->detailMutedTextClass() }}">
                Need help?
                <a href="{{ route('contact') }}" class="{{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }}">Contact Support</a>
            </p>
        </div>
    </div>
</div>
@endsection
