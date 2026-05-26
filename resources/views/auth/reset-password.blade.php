@extends('layouts.app')

@section('title', 'Reset Password - My Gig Guide')
@section('description', 'Reset your My Gig Guide account password')

@section('content')
<div class="{{ $siteBrand->authPageClass() }}">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="flex justify-center mb-6">
                <div class="{{ $siteBrand->authHeaderIconWrapClass() }}">
                    <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>
            <h2 class="{{ $siteBrand->pageTitleClass() }} mb-2">Reset Password</h2>
            <p class="{{ $siteBrand->pageSubtitleClass() }} mb-0">Enter your new password below</p>
        </div>

        <div class="{{ $siteBrand->authCardClass() }}">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-6" autocomplete="off">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="email" class="{{ $siteBrand->formLabelClass() }}">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input id="email" type="text" value="{{ $email }}" readonly autocomplete="off"
                            class="{{ $siteBrand->authInputClass() }} opacity-70 cursor-not-allowed" />
                    </div>
                </div>

                <div>
                    <label for="password" class="{{ $siteBrand->formLabelClass() }}">New Password</label>
                    <p class="text-xs {{ $siteBrand->detailMutedTextClass() }} mb-1">Choose a new password (min 8 characters). Do not use your email.</p>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" type="password" name="password" required autocomplete="new-password" autofocus
                            class="{{ $siteBrand->authInputClass() }} @error('password') border-red-400 @enderror" placeholder="Enter your new password" />
                    </div>
                    @error('password')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="{{ $siteBrand->formLabelClass() }}">Confirm New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            class="{{ $siteBrand->authInputClass() }} @error('password_confirmation') border-red-400 @enderror" placeholder="Confirm your new password" />
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-3 rounded-xl font-medium">Reset Password</button>

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
