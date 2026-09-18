<div class="{{ $cardClass ?? 'bg-white shadow-sm rounded-xl border border-gray-200' }}">
    <div class="{{ $headerClass ?? 'px-6 py-4 border-b border-gray-200' }}">
        <h3 class="{{ $headingClass ?? 'text-lg font-medium text-gray-900' }}">App / site access</h3>
        <p class="mt-1 text-xs text-gray-500">First-party only — which skin they used, Android/iOS/website, first seen and last access. Not Mixpanel or Google Analytics.</p>
    </div>
    <div class="{{ $bodyClass ?? 'px-6 py-4' }}">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">First seen</dt>
                <dd class="mt-1 text-gray-900">
                    @if($user->first_seen_at)
                        {{ $user->first_seen_at->format('M j, Y g:i A') }}
                        <span class="text-gray-500">({{ $user->first_seen_at->diffForHumans() }})</span>
                    @else
                        Not recorded yet
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Last access</dt>
                <dd class="mt-1 text-gray-900">
                    @if($user->last_access_at)
                        {{ $user->last_access_at->format('M j, Y g:i A') }}
                        <span class="text-gray-500">({{ $user->last_access_at->diffForHumans() }})</span>
                    @else
                        Not recorded yet
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">First app / site</dt>
                <dd class="mt-1 text-gray-900">{{ \App\Support\ClientAccess::clientLabel($user->first_client) }} · {{ \App\Support\ClientAccess::platformLabel($user->first_platform) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Last app / site</dt>
                <dd class="mt-1 text-gray-900">{{ \App\Support\ClientAccess::clientLabel($user->last_client) }} · {{ \App\Support\ClientAccess::platformLabel($user->last_platform) }}</dd>
            </div>
        </dl>

        @if($user->relationLoaded('tokens') && $user->tokens->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">App tokens (name + last used)</p>
                <ul class="space-y-1 text-sm text-gray-700">
                    @foreach($user->tokens->sortByDesc('last_used_at')->take(8) as $token)
                        <li>
                            <span class="font-mono text-xs bg-gray-100 px-1 rounded">{{ $token->name }}</span>
                            <span class="text-gray-500">
                                @if($token->last_used_at)
                                    · last used {{ $token->last_used_at->diffForHumans() }}
                                @else
                                    · never used after login
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
