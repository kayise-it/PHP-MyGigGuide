@props([
    'entity',
])

@if(is_object($entity) && method_exists($entity, 'getPublicOwnershipStatus') && $entity->getPublicOwnershipStatus() === 'unclaimed')
@php
    $pageName = $entity->getDisplayName();
    $pageType = match ($entity->getClaimableType()) {
        'artist' => 'artist',
        'venue' => 'venue',
        'organiser' => 'organiser',
        default => 'page',
    };
    $claimEmail = $entity->getClaimEmail();
    $usableEmail = filled($claimEmail)
        && ! str_contains(strtolower((string) $claimEmail), '@example.local');
    $returnUrl = url()->current();
    $registerParams = array_filter([
        'email' => $usableEmail ? $claimEmail : null,
        'continue' => $returnUrl,
    ]);
    $registerUrl = route('register', $registerParams);
    $loginUrl = route('login', ['continue' => $returnUrl]);
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-amber-200 bg-amber-50 p-6 md:p-8 mb-8']) }}>
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex-1">
            <h2 class="text-lg font-semibold text-amber-950">
                Are you the official {{ $pageType }} for {{ $pageName }}?
            </h2>
            <p class="mt-2 text-sm text-amber-900/90 leading-relaxed">
                This listing is not yet linked to a verified account.
                @guest
                    Create a free account (or log in) using the same email as this {{ $pageType }}&rsquo;s contact address
                    @if($usableEmail)
                        (<span class="font-medium">{{ $claimEmail }}</span>)
                    @endif
                    — after you verify your email, we can link this page to you automatically.
                @else
                    If you manage this {{ $pageType }}, make sure your account email
                    @if($usableEmail)
                        matches <span class="font-medium">{{ $claimEmail }}</span>
                    @else
                        matches the contact email on this listing
                    @endif
                    so the claim can complete when your email is verified.
                @endguest
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 shrink-0">
            @guest
                <a href="{{ $registerUrl }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition-colors">
                    Claim this page
                </a>
                <a href="{{ $loginUrl }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white border border-amber-300 text-amber-950 text-sm font-semibold hover:bg-amber-100 transition-colors">
                    Log in
                </a>
            @else
                @if(! auth()->user()->hasVerifiedEmail())
                    <a href="{{ route('verification.notice') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition-colors">
                        Verify your email
                    </a>
                @endif
                <a href="{{ route('profile.show') }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white border border-amber-300 text-amber-950 text-sm font-semibold hover:bg-amber-100 transition-colors">
                    My account
                </a>
            @endguest
        </div>
    </div>
</div>
@endif
