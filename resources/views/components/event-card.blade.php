@props(['event'])

<a href="{{ route('events.show', $event) }}" class="group relative bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden cursor-pointer hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 block">
    <!-- Event Image Background -->
    <div class="relative h-48 w-full overflow-hidden">
        @if($event->poster)
            <img 
                src="{{ Storage::url($event->poster) }}" 
                alt="{{ $event->name }}" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            >
        @else
            <div class="w-full h-full bg-gradient-to-br from-purple-500 via-pink-500 to-red-500 flex items-center justify-center">
                <svg class="h-12 w-12 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
        
        <!-- Gradient Overlay (like artist card) -->
        <div style="position:absolute; inset:0; pointer-events:none; background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.7) 35%, rgba(0,0,0,0.0) 100%);"></div>
        
        <!-- Favorite Button -->
        @auth
        @php
            $isFavorited = false;
            if (auth()->user()) {
                $isFavorited = auth()->user()->favoriteEvents()->where('event_id', $event->id)->exists();
            }
        @endphp
        <button 
            class="absolute top-3 right-3 p-2 bg-white/20 hover:bg-white/30 rounded-full transition-all duration-200 backdrop-blur-sm z-10 favorite-toggle {{ $isFavorited ? 'favorited' : '' }}" 
            data-event-id="{{ $event->id }}"
            onclick="event.preventDefault(); event.stopPropagation(); toggleEventFavorite(this)"
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
            <svg class="h-5 w-5 text-white hover:text-red-400 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
        </button>
        @endauth
        
        <!-- Status Badge -->
        <div class="absolute top-3 left-3">
            <span class="px-2 py-1 text-xs font-medium bg-purple-500/90 text-white rounded-full backdrop-blur-sm">
                {{ ucfirst($event->status) }}
            </span>
        </div>
        
        <!-- Event Name at Bottom (Hidden on hover) -->
        <div class="absolute bottom-0 left-0 right-0 pb-6 pt-16 px-4 group-hover:opacity-0 transition-opacity duration-300">
            <h3 class="ml-4 text-white font-bold text-3xl truncate transform scale-110 drop-shadow-lg">{{ $event->name }}</h3>
        </div>
        
        <!-- Hover Details (Shown on hover, replaces event name) -->
        <div class="absolute bottom-0 left-0 right-0 p-6 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0">
            <div class="text-white">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="font-medium text-lg">{{ $event->venue->name ?? 'Venue TBD' }}</span>
                    </div>
                    
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="font-medium text-lg">{{ $event->date->format('M j, Y') }}</span>
                    </div>
                    
                    @if($event->time)
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium text-lg">{{ $event->time->format('g:i A') }}</span>
                    </div>
                    @endif
                    
                    @if($event->price)
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        <span class="font-bold text-xl text-yellow-300">R{{ number_format($event->price, 2) }}</span>
                    </div>
                    @else
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="font-bold text-xl text-green-300">Free Event</span>
                    </div>
                    @endif
                    
                    @if($event->description)
                    <div class="pt-2 border-t border-white/20">
                        <p class="text-sm text-white/90 line-clamp-2">{{ Str::limit(strip_tags($event->description), 100) }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</a>
