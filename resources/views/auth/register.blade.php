@extends('layouts.app')

@section('title', 'Register - My Gig Guide')
@section('description', 'Create your My Gig Guide account to discover amazing events and connect with the music community.')

@section('content')
<div class="{{ $siteBrand->authPageClass() }}">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="flex justify-center mb-6">
                <div class="{{ $siteBrand->authHeaderIconWrapClass() }}">
                    <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
            </div>
            <h2 class="{{ $siteBrand->pageTitleClass() }} mb-2">Join {{ $siteBrand->footerBrandTitle() }}</h2>
            <p class="{{ $siteBrand->pageSubtitleClass() }} mb-0">Create your account to start discovering amazing events</p>
        </div>

        <div class="{{ $siteBrand->authCardClass() }}">
            @if(\App\Models\SiteSetting::isFacebookLoginEnabled())
            <div class="mb-6">
                <a href="{{ route('facebook.login', ['role' => request('role')]) }}"
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
                    <span class="{{ $siteBrand->authDividerLabelClass() }}">Or continue with email</span>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="{{ $siteBrand->formLabelClass() }}">Full Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus
                            class="{{ $siteBrand->authInputClass() }} @error('name') border-red-400 @enderror" placeholder="Enter your full name" />
                    </div>
                    @error('name')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="username" class="{{ $siteBrand->formLabelClass() }}">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required autocomplete="username"
                            class="{{ $siteBrand->authInputClass() }} @error('username') border-red-400 @enderror" placeholder="Choose a username" />
                    </div>
                    @error('username')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="{{ $siteBrand->formLabelClass() }}">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email', request('email')) }}" required autocomplete="email"
                            class="{{ $siteBrand->authInputClass() }} @error('email') border-red-400 @enderror" placeholder="Enter your email" />
                    </div>
                    @error('email')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="{{ $siteBrand->formLabelClass() }}">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            class="{{ $siteBrand->authInputClass() }} @error('password') border-red-400 @enderror" placeholder="Create a password" />
                    </div>
                    @error('password')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="{{ $siteBrand->formLabelClass() }}">Confirm Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            class="{{ $siteBrand->authInputClass() }}" placeholder="Confirm your password" />
                    </div>
                </div>

                <div>
                    <label for="role" class="{{ $siteBrand->formLabelClass() }}">Account Type</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                            <svg class="{{ $siteBrand->authIconClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <select id="role" name="role" required class="{{ $siteBrand->authSelectClass() }} @error('role') border-red-400 @enderror">
                            <option value="">Select your account type</option>
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Fan - Discover events and connect with the community</option>
                            <option value="artist" {{ old('role') == 'artist' ? 'selected' : '' }}>Artist - Showcase your music and manage performances</option>
                            <option value="organiser" {{ old('role') == 'organiser' ? 'selected' : '' }}>Event Organiser - Create and manage events</option>
                        </select>
                    </div>
                    @error('role')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs {{ $siteBrand->detailMutedTextClass() }}">Choose the account type that best describes you</p>
                </div>

                <div class="flex items-start">
                    <input id="terms" name="terms" type="checkbox" required class="{{ $siteBrand->authCheckboxClass() }} mt-1" />
                    <label for="terms" class="ml-2 block text-sm {{ $siteBrand->detailBodyTextClass() }}">
                        I agree to the
                        <a href="#" class="{{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }}">Terms of Service</a>
                        and
                        <a href="#" class="{{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }}">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn-primary w-full py-3 rounded-xl font-medium">Create Account</button>

                <div class="text-center">
                    <p class="text-sm {{ $siteBrand->detailMutedTextClass() }}">
                        Already have an account?
                        <a href="{{ route('login') }}" class="{{ $siteBrand->isRogues ? 'font-medium text-sky-400 hover:text-sky-300' : 'font-medium text-indigo-400 hover:text-indigo-300' }}">Sign in here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
