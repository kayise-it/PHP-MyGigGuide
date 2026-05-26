@extends('layouts.app')

@section('title', 'Verify Email Address')

@section('content')
<div class="{{ $siteBrand->authPageClass() }}">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="{{ $siteBrand->pageTitleClass() }} mb-2">Verify Your Email Address</h2>
            <p class="{{ $siteBrand->pageSubtitleClass() }} mb-0">
                @if(auth()->check())
                    We've sent a verification link to <strong class="text-white">{{ auth()->user()->email }}</strong>
                @else
                    Please check your email for a verification link
                @endif
            </p>
        </div>

        <div class="{{ $siteBrand->authCardClass() }}">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full {{ $siteBrand->isRogues ? 'bg-sky-500/20' : 'bg-indigo-500/20' }} mb-4">
                    <svg class="h-6 w-6 {{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>

                <h3 class="text-lg font-medium text-white mb-2">Check Your Email</h3>
                <p class="text-sm {{ $siteBrand->detailMutedTextClass() }} mb-6">
                    Before proceeding, please check your email for a verification link. If you did not receive the email, you can request another one.
                </p>

                @if (session('success'))
                    <div class="mb-4 bg-green-500/10 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg">{{ session('success') }}</div>
                @endif
                @if (session('warning'))
                    <div class="mb-4 bg-amber-500/10 border border-amber-500/30 text-amber-200 px-4 py-3 rounded-lg">{{ session('warning') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('verification.resend') }}" class="space-y-4 text-left">
                    @csrf

                    @guest
                        @php $pendingEmail = old('email', session('pending_verification_email')); @endphp
                        @if($pendingEmail)
                            <div>
                                <p class="text-sm {{ $siteBrand->formLabelClass() }} mb-1">Resend verification to</p>
                                <p class="text-white font-medium">{{ $pendingEmail }}</p>
                                <input type="hidden" name="email" value="{{ $pendingEmail }}">
                            </div>
                        @else
                            <div>
                                <label for="email" class="{{ $siteBrand->formLabelClass() }}">Email address used for signup</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                    class="{{ $siteBrand->formInputClass() }}" placeholder="you@example.com">
                                <p class="mt-2 text-xs {{ $siteBrand->detailMutedTextClass() }}">
                                    Enter the email you used when signing up so we can resend the verification link.
                                </p>
                            </div>
                        @endif
                    @endguest

                    @if ($errors->any())
                        <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg text-sm">{{ $errors->first() }}</div>
                    @endif

                    <button type="submit" class="btn-primary w-full py-2 rounded-lg font-medium">Resend Verification Email</button>
                </form>

                <div class="mt-6 text-center">
                    @if(auth()->check())
                        <a href="{{ route('profile.show') }}" class="text-sm font-medium {{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }}">← Back to Profile</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium {{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }}">← Back to Login</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="text-xs {{ $siteBrand->detailMutedTextClass() }}">Didn't receive the email? Check your spam folder or try resending.</p>
        </div>
    </div>
</div>
@endsection
