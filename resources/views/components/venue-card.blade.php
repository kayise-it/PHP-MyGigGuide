@props(['venue', 'isOwned' => false])

<a href="{{ route('venues.show', $venue) }}" 
   class="group relative bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden cursor-pointer transition-all duration-300 transform hover:-translate-y-1 block {{ $isOwned ? 'border-purple-500 ring-2 ring-purple-200 shadow-xl scale-[1.02]' : 'hover:shadow-lg' }}">
    <!-- Venue Image Background -->
    <div class="relative h-48 w-full overflow-hidden">
        @if($venue->main_picture)
            <img 
                src="{{ Storage::url($venue->main_picture) }}" 
                alt="{{ $venue->name }}" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            >
        @else
            <div class="w-full h-full bg-gradient-to-br from-purple-500 via-pink-500 to-red-500 flex items-center justify-center">
                <svg class="h-12 w-12 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        @endif
        
        <!-- Light Overlay for better image visibility -->
        <div class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-colors duration-300"></div>
        
        <!-- Favorite Button -->
        @auth
        @php
            $isFavorited = false;
            if (auth()->user()) {
                $isFavorited = auth()->user()->favoriteVenues()->where('venue_id', $venue->id)->exists();
            }
        @endphp
        <button 
            class="absolute top-3 right-3 p-2 bg-white/20 hover:bg-white/30 rounded-full transition-all duration-200 backdrop-blur-sm z-10 favorite-toggle {{ $isFavorited ? 'favorited' : '' }}" 
            data-venue-id="{{ $venue->id }}"
            onclick="event.preventDefault(); event.stopPropagation(); toggleVenueFavorite(this)"
        >
            <svg class="h-5 w-5 text-white {{ $isFavorited ? 'fill-red-500' : 'fill-none' }} transition-colors duration-200" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
        </button>
        @else
        <button 
            class="absolute top-3 right-3 p-2 bg-white/20 hover:bg-white/30 rounded-full transition-all duration-200 backdrop-blur-sm z-10" 
            onclick="event.preventDefault(); event.stopPropagation(); window.location.href='/login'"
            title="Login to add favorites"
        >
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
        </button>
        @endauth
        
        <!-- Venue Type / Ownership Badges -->
        <div class="absolute top-3 left-3 flex flex-col gap-1">
            <span class="px-2 py-1 text-xs font-medium bg-purple-500/90 text-white rounded-full backdrop-blur-sm">
                Venue
            </span>
            @if($isOwned)
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-gradient-to-r from-emerald-500 to-green-500 text-white rounded-full backdrop-blur-sm shadow-md border border-white/40">
                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 3l2.39 4.848 5.343.777-3.867 3.768.913 5.327L10 14.771l-4.779 2.949.913-5.327L2.267 8.625l5.343-.777L10 3z" />
                    </svg>
                    Your Venue
                </span>
            @endif
        </div>
        
        <!-- Venue Name at Bottom with Gradient Background (Hidden on hover) -->
        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/90 via-black/60 to-transparent group-hover:opacity-0 transition-opacity duration-300">
            <div class="text-white">
                <h3 class="font-bold text-lg mb-1 line-clamp-1">{{ $venue->name }}</h3>
                <p class="text-sm opacity-90">{{ $venue->address }}</p>
            </div>
        </div>
        
        <!-- Hover Details (Hidden by default) -->
        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/95 via-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
            <div class="text-white">
                <h3 class="font-bold text-lg mb-2 line-clamp-1">{{ $venue->name }}</h3>
                <p class="text-sm opacity-90 mb-3">{{ $venue->address }}</p>
                
                <div class="space-y-2 text-sm text-white/90">
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="truncate">{{ $venue->address }}</span>
                    </div>
                    
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="font-semibold">{{ number_format($venue->capacity) }} capacity</span>
                    </div>
                    
                    @if($venue->contact_phone)
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="truncate">{{ $venue->contact_phone }}</span>
                    </div>
                    @endif
                    
                    @if($venue->events->count() > 0)
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="font-semibold">{{ $venue->events->count() }} event{{ $venue->events->count() !== 1 ? 's' : '' }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</a>
