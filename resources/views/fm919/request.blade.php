@extends('layouts.app')

@section('title', 'Send a request — 919 FM')
@section('description', 'Send a song request or shout-out to 919 FM.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} py-12 pb-16">
    <div class="max-w-lg mx-auto px-4 sm:px-6 w-full">
        <div class="flex justify-center mb-6">
            <img src="{{ $siteBrand->logoUrl() }}" alt="{{ $siteBrand->name }}" class="h-14 w-auto max-w-[200px] object-contain">
        </div>

        <h1 class="{{ $siteBrand->pageTitleClass() }} text-center mb-2">Send a request</h1>
        <p class="{{ $siteBrand->pageSubtitleClass() }} text-center mb-8">
            Song request, shout-out, or message for the studio — we'll open WhatsApp with your text ready to send.
        </p>

        <div class="{{ $siteBrand->detailPanelClass('p-6') }}">
            <form id="fm919-request-form" class="space-y-4">
                <div>
                    <label for="request-name" class="form-label block mb-2">Your name (optional)</label>
                    <input type="text" id="request-name" name="name" maxlength="80" class="form-input w-full" placeholder="e.g. Sam">
                </div>
                <div>
                    <label for="request-message" class="form-label block mb-2">Your request</label>
                    <textarea id="request-message" name="message" rows="4" maxlength="500" required class="form-input w-full" placeholder="Please play … / Shout-out to …"></textarea>
                </div>
                <button type="submit" class="btn-primary w-full py-3 rounded-xl font-semibold">
                    Send via WhatsApp
                </button>
            </form>
        </div>

        <p class="mt-6 text-sm text-center {{ $siteBrand->detailMutedTextClass() }}">
            Opens WhatsApp to 919's studio line. Standard data rates may apply.
        </p>

        <p class="mt-8 text-center text-sm {{ $siteBrand->detailMutedTextClass() }}">
            <a href="{{ route('home') }}" class="text-yellow-400 hover:text-yellow-300">← Back to gigs</a>
            ·
            <a href="{{ route('rogues.listen') }}" class="text-yellow-400 hover:text-yellow-300">Listen live</a>
            ·
            <a href="{{ route('fm919.poll') }}" class="text-yellow-400 hover:text-yellow-300">Listener poll</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('fm919-request-form');
    const digits = @json(preg_replace('/\D/', '', $whatsAppE164));

    if (!form || !digits) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const name = (document.getElementById('request-name').value || '').trim();
        const message = (document.getElementById('request-message').value || '').trim();
        if (!message) {
            alert('Please enter your request.');
            return;
        }
        let text = 'Hi 919 FM — request from the app:\n\n';
        if (name) text += 'From: ' + name + '\n';
        text += message;
        const url = 'https://wa.me/' + digits + '?text=' + encodeURIComponent(text);
        window.location.href = url;
    });
})();
</script>
@endpush
