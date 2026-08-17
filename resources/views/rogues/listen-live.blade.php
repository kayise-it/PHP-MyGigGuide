@extends($embed ? 'layouts.rogues-embed' : 'layouts.app')

@section('title', 'Listen live — Rogues on Radio')
@section('description', 'Stream Rogues on Radio live — 24/7 online radio and local gigs.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} {{ $embed ? 'py-8 min-h-screen flex items-center' : 'py-12 pb-16' }}">
    <div class="max-w-lg mx-auto px-4 sm:px-6 w-full text-center">
        @unless($embed)
        <p class="text-xs font-semibold uppercase tracking-widest text-sky-400/90 mb-3">Rogues on Radio</p>
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
            24/7 streaming — tap play to start Rogues on Radio.
        </p>

        <div class="{{ $siteBrand->detailPanelClass() }} text-left">
            <audio id="rogues-stream" preload="none" playsinline>
                <source src="{{ $streamUrl }}" type="audio/aac">
            </audio>

            <div class="flex flex-col items-center gap-4">
                <button
                    type="button"
                    id="rogues-play-toggle"
                    class="inline-flex items-center justify-center gap-3 w-full py-4 rounded-xl font-semibold text-white bg-sky-600 hover:bg-sky-500 transition-colors focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2 focus:ring-offset-slate-900"
                    aria-live="polite"
                    aria-pressed="false"
                >
                    <span id="rogues-play-icon" aria-hidden="true">▶</span>
                    <span id="rogues-play-label">Play live stream</span>
                </button>

                <p id="rogues-stream-status" class="text-sm text-slate-400 text-center mb-0 w-full">
                    Ready — press play to listen.
                </p>

                <div id="rogues-live-pulse" class="hidden flex items-center justify-center gap-2 text-sky-300 text-sm font-medium">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-sky-400"></span>
                    </span>
                    On air now
                </div>
            </div>
        </div>

        @if($publicSiteUrl)
        <p class="mt-8 text-sm {{ $siteBrand->detailMutedTextClass() }}">
            More shows &amp; hosts at
            <a href="{{ $publicSiteUrl }}" class="text-sky-400 hover:text-sky-300" target="_blank" rel="noopener">{{ parse_url($publicSiteUrl, PHP_URL_HOST) ?: 'roguesonradio.co.za' }}</a>
        </p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const audio = document.getElementById('rogues-stream');
    const toggle = document.getElementById('rogues-play-toggle');
    const label = document.getElementById('rogues-play-label');
    const icon = document.getElementById('rogues-play-icon');
    const status = document.getElementById('rogues-stream-status');
    const pulse = document.getElementById('rogues-live-pulse');

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
        status.textContent = 'Streaming Rogues on Radio.';
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
