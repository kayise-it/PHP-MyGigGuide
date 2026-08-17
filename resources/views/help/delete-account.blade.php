@extends('layouts.app')

@section('title', 'Delete your account — ' . ($siteBrand->name ?? 'My Gig Guide'))
@section('description', 'How to request deletion of your My Gig Guide or 919 FM app account and associated personal data.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} py-12 lg:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <header class="mb-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-400 mb-2">
                Account · Data deletion
            </p>
            <h1 class="{{ $siteBrand->heroTitleClass() }} text-3xl sm:text-4xl mb-4">
                Delete your account
            </h1>
            <p class="{{ $siteBrand->bodyTextClass() }} text-lg leading-relaxed">
                This page explains how to delete your account for
                <strong>{{ $siteBrand->name }}</strong>
                @if($siteBrand->isFm919)
                    (919 FM mobile app and {{ parse_url(config('app.url'), PHP_URL_HOST) }})
                @else
                    (My Gig Guide website and mobile app)
                @endif
                and what happens to your data.
            </p>
        </header>

        <div class="space-y-8">
            <section class="{{ $siteBrand->listingCardShellClass() }} p-6 sm:p-8">
                <h2 class="{{ $siteBrand->headingClass() }} text-2xl mb-4">Option 1 — Delete in the app or website (signed in)</h2>
                <ol class="list-decimal list-inside space-y-3 {{ $siteBrand->bodyTextClass() }}">
                    <li>Sign in with your username and password (same account on the app and website).</li>
                    <li>On the website: open <strong>Profile</strong> → scroll to <strong>Delete account</strong> → confirm with your password.</li>
                    <li>In the app: open <strong>Me</strong> → account / profile on the website (signed-in link) if you manage settings there, or use Option 2 below.</li>
                </ol>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mt-4 text-sm">
                    Self-service deletion removes your account promptly when password confirmation succeeds.
                </p>
            </section>

            <section class="{{ $siteBrand->listingCardShellClass() }} p-6 sm:p-8">
                <h2 class="{{ $siteBrand->headingClass() }} text-2xl mb-4">Option 2 — Email us (Play Store &amp; POPIA requests)</h2>
                <p class="{{ $siteBrand->bodyTextClass() }} mb-4">
                    Email
                    <a href="mailto:privacy@mygigguide.co.za" class="underline font-semibold">privacy@mygigguide.co.za</a>
                    from the address on your account (or tell us your username). Subject line:
                    <strong>Delete my account</strong>.
                </p>
                <p class="{{ $siteBrand->bodyTextClass() }} mb-2">Include:</p>
                <ul class="list-disc list-inside space-y-2 {{ $siteBrand->detailMutedTextClass() }}">
                    <li>Your username (and email if you know it)</li>
                    <li>Whether you use the <strong>919 FM</strong> app, <strong>My Gig Guide</strong> app, or the website only</li>
                </ul>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mt-4 text-sm">
                    We aim to confirm deletion within <strong>30 days</strong> (usually sooner). You may receive one verification reply before we delete.
                </p>
            </section>

            <section class="{{ $siteBrand->listingCardShellClass() }} p-6 sm:p-8">
                <h2 class="{{ $siteBrand->headingClass() }} text-2xl mb-4">What we delete</h2>
                <ul class="list-disc list-inside space-y-2 {{ $siteBrand->bodyTextClass() }}">
                    <li>Your user account (name, username, email, hashed password, API tokens)</li>
                    <li>Favourites, ratings, and notification preferences tied to your account</li>
                    <li>Profile and claim data linked only to you</li>
                    <li>Events, venues, or artist pages you <strong>own</strong> (including uploaded images), unless law or dispute handling requires retention</li>
                </ul>
            </section>

            <section class="{{ $siteBrand->listingCardShellClass() }} p-6 sm:p-8">
                <h2 class="{{ $siteBrand->headingClass() }} text-2xl mb-4">What we may keep</h2>
                <ul class="list-disc list-inside space-y-2 {{ $siteBrand->bodyTextClass() }}">
                    <li>Server backups for a limited time (typically up to <strong>90 days</strong>), then overwritten</li>
                    <li>Minimal logs needed for security or legal compliance (e.g. abuse prevention)</li>
                    <li>Public event listings you posted may remain visible in anonymised form if another organiser still manages the venue — we will remove your personal identifiers</li>
                </ul>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mt-4 text-sm">
                    Full policy:
                    <a href="{{ route('popia') }}" class="underline">Privacy policy (POPIA)</a>.
                </p>
            </section>
        </div>
    </div>
</div>
@endsection
