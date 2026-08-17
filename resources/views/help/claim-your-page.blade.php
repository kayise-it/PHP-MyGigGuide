@extends('layouts.app')

@section('title', 'Claim your page — My Gig Guide')
@section('description', 'How venue owners and artists claim their official page on My Gig Guide and the app.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} py-12 lg:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <header class="mb-10">
            <p class="text-sm font-semibold uppercase tracking-wide {{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }} mb-2">
                Help · Venue owners &amp; artists
            </p>
            <h1 class="{{ $siteBrand->heroTitleClass() }} text-3xl sm:text-4xl mb-4">
                Claim your page
            </h1>
            <p class="{{ $siteBrand->bodyTextClass() }} text-lg leading-relaxed">
                One account works on <a href="{{ route('home') }}" class="{{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }} underline">{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'mygigguide.co.za' }}</a>
                and the <strong>My Gig Guide app</strong>.
            </p>
        </header>

        <div class="space-y-8">
            <section class="{{ $siteBrand->listingCardShellClass() }} p-6 sm:p-8">
                <h2 class="{{ $siteBrand->headingClass() }} text-2xl mb-4">Venue owners</h2>
                <p class="{{ $siteBrand->bodyTextClass() }} mb-4">
                    Your venue may already be listed. <strong>Claiming</strong> links that listing to your account so it shows as official and you can manage it.
                </p>

                <h3 class="text-lg font-semibold text-white mb-2">Before you start</h3>
                <ol class="list-decimal list-inside space-y-2 {{ $siteBrand->detailMutedTextClass() }} mb-6">
                    <li>Find your venue on the site (<a href="{{ route('venues.index') }}" class="underline">search or browse venues</a>).</li>
                    <li>Note the <strong>contact email</strong> on the listing.</li>
                    <li>Use a free account with that same email — or use the manual route below.</li>
                </ol>

                <h3 class="text-lg font-semibold text-white mb-2">Option A — email matches (fastest)</h3>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-2 font-medium text-white/90">On the website</p>
                <ol class="list-decimal list-inside space-y-2 {{ $siteBrand->detailMutedTextClass() }} mb-4">
                    <li>Open your venue’s page.</li>
                    <li>If it says <strong>Unclaimed</strong>, use the amber <strong>“Claim this page”</strong> banner.</li>
                    <li><a href="{{ route('register') }}" class="underline">Create account</a> or <a href="{{ route('login') }}" class="underline">log in</a> with the <strong>same email</strong> as the listing.</li>
                    <li>Verify your email (check your inbox).</li>
                    <li>After verification, the page links to you automatically.</li>
                </ol>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-2 font-medium text-white/90">In the app</p>
                <ol class="list-decimal list-inside space-y-2 {{ $siteBrand->detailMutedTextClass() }} mb-6">
                    <li>Sign in with the same email as the venue listing.</li>
                    <li>If we find a match, tap <strong>Claim now</strong> on the banner.</li>
                    <li>Approved pages appear under <strong>Me → Pages</strong>.</li>
                </ol>

                <h3 class="text-lg font-semibold text-white mb-2">Option B — email does not match</h3>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-2">Use this if the listing has an old or wrong email.</p>
                <ol class="list-decimal list-inside space-y-2 {{ $siteBrand->detailMutedTextClass() }}">
                    <li>Sign in to the app.</li>
                    <li>Open your venue’s page.</li>
                    <li>Tap <strong>Request to manage this page</strong>.</li>
                    <li>Add a short note (optional) — e.g. “I’m the manager; contact email is outdated.”</li>
                    <li>Our team reviews it. You’ll see <strong>pending review</strong> until approved.</li>
                </ol>
            </section>

            <section class="{{ $siteBrand->listingCardShellClass() }} p-6 sm:p-8">
                <h2 class="{{ $siteBrand->headingClass() }} text-2xl mb-4">Artists</h2>
                <p class="{{ $siteBrand->bodyTextClass() }} mb-4">
                    Your band or stage name may already have a profile. <strong>Claiming</strong> links it to your account so fans see an official page.
                </p>

                <h3 class="text-lg font-semibold text-white mb-2">Option A — email matches (fastest)</h3>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-2 font-medium text-white/90">On the website</p>
                <ol class="list-decimal list-inside space-y-2 {{ $siteBrand->detailMutedTextClass() }} mb-4">
                    <li>Open your <a href="{{ route('artists.index') }}" class="underline">artist page</a>.</li>
                    <li>Use the amber <strong>“Claim this page”</strong> banner if the page is <strong>Unclaimed</strong>.</li>
                    <li>Register or log in with the <strong>same email</strong> as the artist listing.</li>
                    <li>Verify your email.</li>
                </ol>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-2 font-medium text-white/90">In the app</p>
                <ol class="list-decimal list-inside space-y-2 {{ $siteBrand->detailMutedTextClass() }} mb-6">
                    <li>Sign in with the same email as the artist listing.</li>
                    <li>Tap <strong>Claim now</strong> on the banner if it appears.</li>
                    <li>Check <strong>Me → Pages</strong>.</li>
                </ol>

                <h3 class="text-lg font-semibold text-white mb-2">Option B — email does not match</h3>
                <ol class="list-decimal list-inside space-y-2 {{ $siteBrand->detailMutedTextClass() }}">
                    <li>Sign in, open your artist page in the app.</li>
                    <li>Tap <strong>Request to manage this page</strong>.</li>
                    <li>Explain briefly why you should manage it (optional).</li>
                    <li>Wait for admin approval.</li>
                </ol>
            </section>

            <section class="{{ $siteBrand->listingCardShellClass() }} p-6 sm:p-8 overflow-x-auto">
                <h2 class="{{ $siteBrand->headingClass() }} text-2xl mb-4">Quick comparison</h2>
                <table class="w-full text-sm {{ $siteBrand->detailMutedTextClass() }}">
                    <thead>
                        <tr class="border-b border-white/10 text-left text-white/90">
                            <th class="py-2 pr-4 font-semibold"></th>
                            <th class="py-2 pr-4 font-semibold">Venue</th>
                            <th class="py-2 font-semibold">Artist</th>
                        </tr>
                    </thead>
                    <tbody class="align-top">
                        <tr class="border-b border-white/5">
                            <td class="py-2 pr-4 font-medium text-white/80">Automatic claim</td>
                            <td class="py-2 pr-4" colspan="2">Account email = listing contact email</td>
                        </tr>
                        <tr class="border-b border-white/5">
                            <td class="py-2 pr-4 font-medium text-white/80">Easy route</td>
                            <td class="py-2 pr-4" colspan="2">Website banner or app <strong>Claim now</strong></td>
                        </tr>
                        <tr class="border-b border-white/5">
                            <td class="py-2 pr-4 font-medium text-white/80">Wrong email?</td>
                            <td class="py-2 pr-4" colspan="2">App → <strong>Request to manage this page</strong></td>
                        </tr>
                        <tr>
                            <td class="py-2 pr-4 font-medium text-white/80">Your pages</td>
                            <td class="py-2 pr-4" colspan="2"><strong>Me → Pages</strong> (app) or dashboard (web)</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="{{ $siteBrand->listingCardShellClass() }} p-6 sm:p-8">
                <h2 class="{{ $siteBrand->headingClass() }} text-2xl mb-4">Troubleshooting</h2>
                <dl class="space-y-4 {{ $siteBrand->detailMutedTextClass() }}">
                    <div>
                        <dt class="font-semibold text-white/90">No claim banner</dt>
                        <dd class="mt-1">Page may already be official, or there is no contact email — use <strong>Request to manage</strong> or <a href="{{ route('contact.index') }}" class="underline">contact us</a>.</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-white/90">“No listings match your email”</dt>
                        <dd class="mt-1">Your sign-in email ≠ listing contact email — use manual request or ask us to update the listing email.</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-white/90">Claim stuck on pending</dt>
                        <dd class="mt-1">Verify email on the website, or wait for manual review.</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-white/90">Already have another artist page</dt>
                        <dd class="mt-1">Only one artist page per account on the automatic path — contact us if you manage multiple acts.</dd>
                    </div>
                </dl>
            </section>

            <p class="text-center">
                <a href="{{ route('home') }}" class="{{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }} underline text-sm">
                    ← Back to home
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
