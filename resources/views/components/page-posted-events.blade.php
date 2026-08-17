@props([
    'events',
    'heading' => 'Gigs they posted',
    'empty' => 'No upcoming gigs listed by this page yet.',
])

<div id="gigs-they-posted" class="{{ $siteBrand->detailPanelClass('p-8 mb-8') }}">
    <h2 class="{{ $siteBrand->detailPanelHeadingClass() }} mb-6">{{ $heading }}</h2>
    @if($events->count() > 0)
        <div class="space-y-4">
            @foreach($events as $event)
                <a href="{{ route('events.show', $event) }}" class="block {{ $siteBrand->isRogues ? 'border border-slate-700' : 'border border-white/10' }} rounded-xl p-4 {{ $siteBrand->isRogues ? 'hover:bg-slate-800' : 'hover:bg-black/40' }} transition">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold text-white truncate">{{ $event->name }}</h3>
                            <p class="text-sm {{ $siteBrand->detailMutedTextClass() }}">
                                {{ $event->date->format('M d, Y') }}{{ $event->time ? ' · '.$event->time->format('H:i') : '' }}
                            </p>
                            @if($event->venue)
                                <p class="text-xs text-slate-500 truncate">{{ $event->venue->name }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs px-2 py-1 rounded-full {{ $siteBrand->isRogues ? 'bg-sky-500/20 text-sky-300' : 'bg-indigo-500/20 text-indigo-300' }}">
                            Listed
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 {{ $siteBrand->detailMutedTextClass() }}">
            <p>{{ $empty }}</p>
        </div>
    @endif
</div>
