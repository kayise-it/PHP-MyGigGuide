@extends('layouts.app')

@section('title', 'Login with username - My Gig Guide')
@section('description', 'Sign in with your username to access your My Gig Guide account.')

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
            <h2 class="{{ $siteBrand->pageTitleClass() }} mb-2">Login with username</h2>
            <p class="{{ $siteBrand->pageSubtitleClass() }} mb-0">Sign in with your username and password</p>
        </div>

        <div class="{{ $siteBrand->authCardClass() }}">
            @if(\App\Models\SiteSetting::isFacebookLoginEnabled())
            <div class="mb-6">
                <a href="{{ route('facebook.login', request()->has('continue') ? ['continue' => request('continue')] : []) }}"
                   class="{{ $siteBrand->authSecondaryButtonClass() }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                    </svg>
                    Continue with Facebook
                </a>
            </div>

            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t {{ $siteBrand->authDividerLineClass() }}"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="{{ $siteBrand->authDividerLabelClass() }}">Or sign in with username</span>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                @if(request()->has('continue'))
                    <input type="hidden" name="continue" value="{{ request()->get('continue') }}">
                @endif

                <div>
                    <label for="username" class="{{ $siteBrand->formLabelClass() }}">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autocomplete="username"
                            autofocus
                            class="{{ $siteBrand->authInputClass() }} @error('username') border-red-400 @enderror"
                            placeholder="Enter your username (not email)"
                        />
                    </div>
                    <p class="mt-1 text-xs {{ $siteBrand->detailMutedTextClass() }}">Use your username to sign in, not your email address.</p>
                    @error('username')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="{{ $siteBrand->formLabelClass() }}">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="{{ $siteBrand->authInputClass() }} @error('password') border-red-400 @enderror"
                            placeholder="Enter your password"
                        />
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="{{ $siteBrand->authCheckboxClass() }}" />
                        <label for="remember" class="ml-2 block text-sm {{ $siteBrand->detailBodyTextClass() }}">Remember me</label>
                    </div>
                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="{{ $siteBrand->isRogues ? 'font-medium text-sky-400 hover:text-sky-300' : 'font-medium text-indigo-400 hover:text-indigo-300' }}">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-3 rounded-xl font-medium">
                    Sign In
                </button>

                <div class="text-center">
                    <p class="text-sm {{ $siteBrand->detailMutedTextClass() }}">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="{{ $siteBrand->isRogues ? 'font-medium text-sky-400 hover:text-sky-300' : 'font-medium text-indigo-400 hover:text-indigo-300' }}">
                            Sign up here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
