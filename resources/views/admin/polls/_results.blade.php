{{-- Poll results bars — used on index (live card), show, and edit. --}}
@php
    $large = $large ?? false;
    $results = $poll->resultsArray();
    $leaderVotes = collect($results)->max('votes') ?? 0;
@endphp

@if($totalVotes === 0)
    <p class="text-sm text-slate-500">No votes yet — results will appear here as listeners vote in the app.</p>
@else
    <div class="space-y-{{ $large ? '4' : '3' }}">
        @foreach($results as $index => $result)
            @php
                $isLeader = $leaderVotes > 0 && $result['votes'] === $leaderVotes;
            @endphp
            <div>
                <div class="flex justify-between gap-4 {{ $large ? 'text-base' : 'text-sm' }} mb-1">
                    <span class="{{ $isLeader ? 'text-white font-semibold' : 'text-slate-200' }}">
                        @if($isLeader)<span class="text-amber-400 mr-1" title="Leading">★</span>@endif
                        {{ $result['label'] }}
                    </span>
                    <span class="text-slate-400 whitespace-nowrap font-medium tabular-nums">
                        {{ $result['votes'] }} {{ Str::plural('vote', $result['votes']) }}
                        · {{ $result['percent'] }}%
                    </span>
                </div>
                <div class="{{ $large ? 'h-3' : 'h-2' }} rounded-full bg-slate-700 overflow-hidden">
                    <div
                        class="h-full rounded-full {{ $isLeader ? 'bg-amber-500' : 'bg-indigo-500' }} transition-all duration-300"
                        style="width: {{ max($result['percent'], $result['votes'] > 0 ? 2 : 0) }}%"
                    ></div>
                </div>
            </div>
        @endforeach
    </div>
@endif
