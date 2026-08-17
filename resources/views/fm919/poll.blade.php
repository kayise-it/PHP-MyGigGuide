@extends('layouts.app')

@section('title', 'Listener poll — 919 FM')
@section('description', 'Vote in the 919 FM listener poll.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} py-12 pb-16">
    <div class="max-w-lg mx-auto px-4 sm:px-6 w-full">
        <div class="flex justify-center mb-6">
            <img src="{{ $siteBrand->logoUrl() }}" alt="{{ $siteBrand->name }}" class="h-14 w-auto max-w-[200px] object-contain">
        </div>

        <h1 class="{{ $siteBrand->pageTitleClass() }} text-center mb-2">Listener poll</h1>
        <p class="{{ $siteBrand->pageSubtitleClass() }} text-center mb-8">
            What's your favourite 919 show slot?
        </p>

        <div class="{{ $siteBrand->detailPanelClass('p-6 mb-6') }}">
            <p class="text-sm {{ $siteBrand->detailMutedTextClass() }} mb-0">
                Demo poll for now — votes are saved on this device only. When 919 goes live with My Gig Guide, results can feed the station portal.
            </p>
        </div>

        <form id="fm919-poll-form" class="space-y-3">
            @foreach([
                'Rise & Shine (6–9am)',
                'The Feel Good Fix (9am–12pm)',
                'The Drive Train (3–6pm)',
                'Weekend shows',
            ] as $option)
            <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-700 hover:border-yellow-400/50 cursor-pointer transition">
                <input type="radio" name="show_slot" value="{{ $option }}" class="mt-1 text-yellow-400 focus:ring-yellow-400">
                <span class="text-slate-100 font-medium">{{ $option }}</span>
            </label>
            @endforeach

            <button type="submit" class="btn-primary w-full py-3 mt-4 rounded-xl font-semibold">
                Submit vote
            </button>
        </form>

        <p id="fm919-poll-thanks" class="hidden mt-6 text-center text-yellow-400 font-medium">
            Thanks for voting!
        </p>

        <p class="mt-10 text-center text-sm {{ $siteBrand->detailMutedTextClass() }}">
            <a href="{{ route('home') }}" class="text-yellow-400 hover:text-yellow-300">← Back to gigs</a>
            ·
            <a href="{{ route('rogues.listen') }}" class="text-yellow-400 hover:text-yellow-300">Listen live</a>
            ·
            <a href="{{ route('fm919.request') }}" class="text-yellow-400 hover:text-yellow-300">Send a request</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('fm919-poll-form');
    const thanks = document.getElementById('fm919-poll-thanks');
    const key = 'fm919_poll_vote_v1';

    if (!form) return;

    const saved = localStorage.getItem(key);
    if (saved) {
        const input = form.querySelector('input[value="' + saved.replace(/"/g, '\\"') + '"]');
        if (input) input.checked = true;
        form.querySelector('button[type=submit]').disabled = true;
        thanks.classList.remove('hidden');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const picked = form.querySelector('input[name=show_slot]:checked');
        if (!picked) {
            alert('Pick a show first.');
            return;
        }
        localStorage.setItem(key, picked.value);
        form.querySelector('button[type=submit]').disabled = true;
        thanks.classList.remove('hidden');
    });
})();
</script>
@endpush
