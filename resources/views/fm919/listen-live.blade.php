@extends($embed ? 'layouts.rogues-embed' : 'layouts.app')

@section('title', 'Listen live — 919 FM')
@section('description', 'Stream 919 FM live — feel good anytime, all the time.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} {{ $embed ? 'py-8 min-h-screen flex items-center' : 'py-12 pb-16' }}">
    <div class="max-w-lg mx-auto px-4 sm:px-6 w-full text-center">
        @unless($embed)
        <p class="text-xs font-semibold uppercase tracking-widest text-yellow-400/90 mb-3">919 FM</p>
        @endunless

        <div class="flex justify-center mb-6">
            <img
                src="{{ $siteBrand->logoUrl() }}"
                alt="{{ $siteBrand->name }}"
                class="h-16 w-auto max-w-[220px] object-contain"
            >
        </div>

        <h1 class="{{ $siteBrand->pageTitleClass() }} mb-2">Listen live</h1>
        <p class="{{ $siteBrand->pageSubtitleClass() }} mb-8">
            Feel good! Anytime. All the time. — tap play to start 919 FM.
        </p>

        <div class="{{ $siteBrand->detailPanelClass() }} text-left">
            <audio id="fm919-stream" preload="none" playsinline>
                <source src="{{ $streamUrl }}" type="audio/aac">
            </audio>

            <div class="flex flex-col items-center gap-4">
                <button
                    type="button"
                    id="fm919-play-toggle"
                    class="btn-primary inline-flex items-center justify-center gap-3 w-full py-4 rounded-xl font-semibold"
                    aria-live="polite"
                    aria-pressed="false"
                >
                    <span id="fm919-play-icon" aria-hidden="true">▶</span>
                    <span id="fm919-play-label">Play live stream</span>
                </button>

                <p id="fm919-stream-status" class="text-sm {{ $siteBrand->detailMutedTextClass() }} text-center mb-0 w-full">
                    Ready — press play to listen.
                </p>

                <div id="fm919-live-pulse" class="hidden flex items-center justify-center gap-2 text-yellow-300 text-sm font-medium">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-yellow-400"></span>
                    </span>
                    On air now
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap justify-center gap-3 text-sm">
            <a href="{{ route('fm919.poll') }}" class="text-yellow-400 hover:text-yellow-300">Listener poll</a>
            <span class="text-slate-600">·</span>
            <a href="{{ route('fm919.request') }}" class="text-yellow-400 hover:text-yellow-300">Send a request</a>
        </div>

        @if($publicSiteUrl)
        <p class="mt-6 text-sm {{ $siteBrand->detailMutedTextClass() }}">
            More shows &amp; hosts at
            <a href="{{ $publicSiteUrl }}" class="text-yellow-400 hover:text-yellow-300" target="_blank" rel="noopener">{{ parse_url($publicSiteUrl, PHP_URL_HOST) ?: '919.co.za' }}</a>
        </p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const audio = document.getElementById('fm919-stream');
    const toggle = document.getElementById('fm919-play-toggle');
    const label = document.getElementById('fm919-play-label');
    const icon = document.getElementById('fm919-play-icon');
    const status = document.getElementById('fm919-stream-status');
    const pulse = document.getElementById('fm919-live-pulse');

    if (!audio || !toggle) return;

    function setPlaying(playing) {
        toggle.setAttribute('aria-pressed', playing ? 'true' : 'false');
        label.textContent = playing ? 'Pause stream' : 'Play live stream';
        icon.textContent = playing ? '❚❚' : '▶';
        pulse.classList.toggle('hidden', !playing);
    }

    toggle.addEventListener('click', function () {
        if (audio.paused) {
            status.textContent = 'Connecting…';
            audio.play().catch(function () {
                status.textContent = 'Could not start playback. Check your connection and try again.';
                setPlaying(false);
            });
        } else {
            audio.pause();
        }
    });

    audio.addEventListener('playing', function () {
        status.textContent = 'Streaming 919 FM.';
        setPlaying(true);
    });

    audio.addEventListener('pause', function () {
        status.textContent = 'Paused — tap play to resume.';
        setPlaying(false);
    });

    audio.addEventListener('waiting', function () {
        status.textContent = 'Buffering…';
    });

    audio.addEventListener('error', function () {
        status.textContent = 'Stream unavailable right now. Please try again in a moment.';
        setPlaying(false);
    });
})();
</script>
@endpush
