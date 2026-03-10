@props([
    'type' => 'event', // 'event', 'venue', 'artist', 'organiser'
    'item' => null, // The model instance (Event, Venue, Artist, etc.)
    'showBack' => true, // Whether to show back button
])

@php
    // Define routes and labels for different types
    $routes = [
        'event' => ['index' => 'events.index', 'label' => 'Events'],
        'venue' => ['index' => 'venues.index', 'label' => 'Venues'],
        'artist' => ['index' => 'artists.index', 'label' => 'Artists'],
        'organiser' => ['index' => 'organisers.index', 'label' => 'Organisers'],
    ];
    
    $routeInfo = $routes[$type] ?? $routes['event'];
    $indexRoute = $routeInfo['index'];
    $typeLabel = $routeInfo['label'];
    
    // Get the item name per type
    if ($type === 'artist') {
        $itemName = $item->stage_name ?? ($item->user->name ?? 'Unknown');
    } elseif ($type === 'venue') {
        $itemName = $item->name ?? 'Unknown';
    } elseif ($type === 'event') {
        $itemName = $item->name ?? $item->title ?? 'Unknown';
    } elseif ($type === 'organiser') {
        $itemName = $item->organisation_name ?? ($item->user->name ?? 'Unknown');
    } else {
        $itemName = $item->name ?? $item->title ?? 'Unknown';
    }
@endphp

<div class="relative z-20 pointer-events-auto">
    <div class="inline-flex items-center bg-white/90 rounded-lg shadow px-4 py-2 space-x-2 text-xs font-medium text-gray-700"
        style="backdrop-filter: blur(2px); font-size: 0.8rem;">
        
        @if($showBack)
            <a href="{{ route($indexRoute) }}" class="flex items-center hover:underline text-purple-600">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
            <span class="text-gray-400">/</span>
        @endif
        
        <svg class="h-4 w-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($type === 'event')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            @elseif($type === 'venue')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            @elseif($type === 'artist')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            @elseif($type === 'organiser')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            @else
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            @endif
        </svg>
        
        <a href="{{ route($indexRoute) }}" class="hover:underline text-purple-600">{{ $typeLabel }}</a>
        <span class="text-gray-400">/</span>
        <span class="truncate max-w-[120px]" title="{{ $itemName }}">
            {{ \Illuminate\Support\Str::limit($itemName, 30) }}
        </span>
    </div>
</div>



