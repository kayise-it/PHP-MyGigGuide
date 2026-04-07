@extends('layouts.app')

@section('title', $venue->name . ' - My Gig Guide')

@push('head')
@php
$description = \Illuminate\Support\Str::limit(strip_tags($venue->description ?? 'Discover this amazing venue on My Gig Guide.'), 160);
    $imageUrl = null;
    
    if ($venue->main_picture && \Illuminate\Support\Facades\Storage::disk('public')->exists($venue->main_picture)) {
        $storageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($venue->main_picture);
        // Ensure absolute URL - Storage::url() may return relative if APP_URL not set
        $imageUrl = (str_starts_with($storageUrl, 'http://') || str_starts_with($storageUrl, 'https://')) 
            ? $storageUrl 
            : url($storageUrl);
} elseif (isset($gallery) && is_array($gallery) && count($gallery) > 0) {
        $firstImage = $gallery[0];
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($firstImage)) {
            $storageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($firstImage);
            // Ensure absolute URL - Storage::url() may return relative if APP_URL not set
            $imageUrl = (str_starts_with($storageUrl, 'http://') || str_starts_with($storageUrl, 'https://')) 
                ? $storageUrl 
                : url($storageUrl);
}
    }
    if (!$imageUrl) {
        $fallbackUrl = asset('logos/logo1.jpeg');
        $imageUrl = url($fallbackUrl);
}
@endphp
<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ route('venues.show', $venue) }}">
<meta property="og:title" content="{{ $venue->name }} - My Gig Guide">
<meta property="og:description" content="{{ $description }}">
@if($imageUrl)
<meta property="og:image" content="{{ $imageUrl }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif
<meta property="og:site_name" content="My Gig Guide">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ route('venues.show', $venue) }}">
<meta name="twitter:title" content="{{ $venue->name }} - My Gig Guide">
<meta name="twitter:description" content="{{ $description }}">
@if($imageUrl)
<meta name="twitter:image" content="{{ $imageUrl }}">
@endif
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="relative h-96 md:h-[500px] overflow-hidden">
        @php
            // Use normalized $gallery provided by controller (array of paths)
            $galleryImages = [];
            if (isset($gallery) && is_array($gallery)) {
                $galleryImages = array_values(array_filter($gallery, function($path) {
                    return $path && !str_contains($path, '/tmp/php') && !str_contains($path, 'tmp.php');
                }));
            }

            // Use the actual column name stored when uploading: main_picture
            $mainImage = $venue->main_picture ?? ($galleryImages[0] ?? null);
            if ($mainImage && (str_contains($mainImage, '/tmp/php') || str_contains($mainImage, 'tmp.php'))) {
                $mainImage = null;
            }
        @endphp

        @if(count($galleryImages) > 1)
            <!-- Owl Carousel for multiple gallery images -->
            <div class="owl-carousel owl-theme venue-hero-carousel w-full h-full">
                @foreach($galleryImages as $index => $image)
                    <div class="item relative w-full h-full">
                        <img
                            src="{{ Storage::url($image) }}"
                            alt="{{ $venue->name }} - Image {{ $index + 1 }}"
                            class="w-full h-full object-cover"
                        >
                        <div class="absolute inset-0 bg-black/40"></div>
                    </div>
                @endforeach
            </div>
        @elseif($mainImage)
            <!-- Single image background -->
            <div class="relative w-full h-full">
                <img
                    src="{{ Storage::url($mainImage) }}"
                    alt="{{ $venue->name }}"
                    class="w-full h-full object-cover"
                >
                <div class="absolute inset-0 bg-black/40"></div>
            </div>
        @else
            <!-- Fallback gradient background -->
            <div class="w-full h-full bg-gradient-to-br from-purple-600 to-blue-600"></div>
        @endif
        
        <!-- Custom Navigation Controls (only for carousel) -->
        @if(count($galleryImages) > 1)
        <div class="absolute inset-0 pointer-events-none">
            <button class="owl-prev absolute left-4 top-1/2 -translate-y-1/2 p-2 bg-white/20 hover:bg-white/30 rounded-full transition-all pointer-events-auto">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button class="owl-next absolute right-4 top-1/2 -translate-y-1/2 p-2 bg-white/20 hover:bg-white/30 rounded-full transition-all pointer-events-auto">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            
            <!-- Dots Container -->
            <div class="owl-dots absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2"></div>
        </div>
        @endif

        <style>
            #hello {
                z-index: 10 !important;
                position: absolute !important;
            }
            
            /* Prevent carousel animations from affecting overlay */
            .venue-hero-carousel {
                z-index: 1;
            }
            
            .venue-hero-carousel .owl-item {
                z-index: 1;
            }
            
            /* Ensure overlay stays on top and doesn't inherit carousel animations */
            #hello * {
                animation: none !important;
                transition: none !important;
            }
        </style>
        
        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto">
            <x-hero-breadcrumb type="venue" :item="$venue" />
        </div>
        
        <!-- Hero Content - Now positioned outside carousel to prevent fading -->
        <div class="absolute inset-0 flex items-end pointer-events-none" id="hello">
            <div class="w-full bg-gradient-to-t from-black/80 to-transparent p-6 md:p-8 pointer-events-auto">
                <div class="max-w-7xl mx-auto">
                    <div class="flex items-end justify-between">
                        <div class="text-white">
                            <h1 class="text-4xl md:text-6xl font-bold mb-2">{{ $venue->name }}</h1>
                            <div class="flex items-center space-x-6 text-lg">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm font-semibold">★ {{ number_format($ratingAvg, 1) }}</span>
                                @if($venue->address)
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $venue->address }}</span>
                                </div>
                                @endif
                                @if($venue->phone)
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2.28a2 2 0 011.789 1.106l1.387 2.773a2 2 0 01-.217 2.18l-1.516 1.89a11.042 11.042 0 005.516 5.516l1.89-1.516a2 2 0 012.18-.217l2.773 1.387A2 2 0 0121 18.72V21a2 2 0 01-2 2h-1C9.163 23 1 14.837 1 5V4a2 2 0 012-2z"></path>
                                    </svg>
                                    <span>{{ $venue->phone }}</span>
                                </div>
                                @endif
                                @if($venue->website)
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <a href="{{ $venue->website }}" class="underline" target="_blank" rel="noopener">{{ $venue->website }}</a>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            @auth
                            @php
                                $isFavorited = false;
                                if (auth()->user()) {
                                    $isFavorited = auth()->user()->favoriteVenues()->where('venue_id', $venue->id)->exists();
                                }
                            @endphp
                            <button 
                                id="favorite-venue-btn" 
                                data-venue-id="{{ $venue->id }}"
                                class="favorite-toggle p-3 bg-white/20 hover:bg-white/30 rounded-full transition-all {{ $isFavorited ? 'favorited' : '' }}">
                                <svg class="h-6 w-6 text-white {{ $isFavorited ? 'fill-red-500' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                            @else
                            <button onclick="window.location.href='/login'" class="p-3 bg-white/20 hover:bg-white/30 rounded-full transition-all" title="Login to add favorites">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                            @endauth
                            @auth
                                @if((isset($isOwner) && $isOwner) || auth()->id() == $venue->owner_id || $venue->user_id === auth()->id())
                                    <a href="{{ route('venues.edit', $venue) }}" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-all">
                                        Edit Venue
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Main Info -->
            <div class="lg:col-span-2">
                <!-- Description -->
                @if($venue->description)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Venue</h2>
                    <div class="prose prose-gray max-w-none">
                        <p class="text-gray-600 leading-relaxed">{{ $venue->description }}</p>
                    </div>
                </div>
                @endif

                <!-- Gallery -->
                @if(!empty($gallery))
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Gallery</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($gallery as $image)
                        <div class="aspect-square rounded-lg overflow-hidden">
                            <img
                                src="{{ Storage::url($image) }}"
                                alt="{{ $venue->name }} - Gallery"
                                class="w-full h-full object-cover hover:scale-105 transition-transform duration-300 cursor-pointer"
                                onclick="openImageModal('{{ Storage::url($image) }}')"
                            >
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- YouTube Videos -->
                @if($venue->youtubeVideos->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Videos</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($venue->youtubeVideos as $video)
                        <x-youtube-video :video="$video" />
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Upcoming Events -->
                @if(count($upcomingEvents) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Upcoming Events</h2>
                    <div class="space-y-4">
                        @foreach($upcomingEvents as $event)
                        <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                            @if($event->poster)
                            <img
                                src="{{ Storage::url($event->poster) }}"
                                alt="{{ $event->name }}"
                                class="w-16 h-16 object-cover rounded-lg"
                            >
                            @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            @endif
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $event->name }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($event->date)->format('M j, Y') }} at {{ \Carbon\Carbon::parse($event->time)->format('g:i A') }}
                                </p>
                            </div>
                            <a
                                href="{{ route('events.show', $event) }}"
                                class="btn-primary text-sm"
                            >
                                View Event
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Upcoming Events</h2>
                        <a href="{{ route('events.create', ['venue_id' => $venue->id, 'return_to' => route('venues.show', $venue)]) }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                            Create event
                        </a>
                    </div>
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-gray-500">No upcoming events scheduled at this venue.</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column - Sidebar -->
            <div class="space-y-6">
                <!-- Contact Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                    <div class="space-y-3">
                        @if($venue->contact_email)
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:{{ $venue->contact_email }}" class="text-purple-600 hover:text-purple-700">
                                {{ $venue->contact_email }}
                            </a>
                        </div>
                        @endif
                        
                        @if($venue->contact_phone)
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a href="tel:{{ $venue->contact_phone }}" class="text-purple-600 hover:text-purple-700">
                                {{ $venue->contact_phone }}
                            </a>
                        </div>
                        @endif
                        
                        @if($venue->website)
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <a href="{{ $venue->website }}" target="_blank" rel="noopener noreferrer" class="text-purple-600 hover:text-purple-700">
                                Visit Website
                            </a>
                        </div>
                        @endif
                        
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-gray-600">{{ $venue->address }}</span>
                        </div>
                    </div>
                </div>

                <!-- Venue Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Venue Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Capacity:</span>
                            <span class="font-medium">{{ number_format($venue->capacity) }} people</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Rating:</span>
                            <span class="font-medium text-yellow-600">★ {{ number_format($ratingAvg, 1) }}</span>
                        </div>
                        
                        @if($venue->owner)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Managed by:</span>
                            <span class="font-medium">{{ $venue->owner->name ?? 'Unknown' }}</span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Added:</span>
                            <span class="font-medium">{{ $venue->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Show venue ownership management section -->
                @auth
                @if(isset($isOwner) && $isOwner)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        Venue Ownership Management
                    </h3>
                    
                    <!-- Add New Owner (Primary Owner only) -->
                    @if(isset($isPrimaryOwner) && $isPrimaryOwner)
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <button 
                            onclick="document.getElementById('add-owner-modal').classList.remove('hidden')"
                            class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Owner
                        </button>
                    </div>
                    @endif
                    
                    <!-- Current Owners List (Read) -->
                    @if(isset($venueOwners) && $venueOwners && $venueOwners->count() > 0)
                    <div class="space-y-3 mb-4">
                        @foreach($venueOwners as $owner)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-semibold">{{ substr($owner->name ?? $owner->email, 0, 1) }}</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $owner->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $owner->email }}</p>
                                    @php
                                        $ownerRole = null;
                                        if (isset($owner->pivot)) {
                                            $ownerRole = is_object($owner->pivot) ? ($owner->pivot->role ?? null) : (is_array($owner->pivot) ? ($owner->pivot['role'] ?? null) : null);
                                        }
                                    @endphp
                                    @if($ownerRole === 'primary')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-1">Primary Owner</span>
                                    @elseif($ownerRole === 'co_owner')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">Co-Owner</span>
                                    @elseif($ownerRole === 'manager')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">Manager</span>
                                    @endif
                                </div>
                            </div>
                            @if(isset($isPrimaryOwner) && $isPrimaryOwner && $owner->id !== auth()->id())
                            <div class="flex items-center gap-2">
                                <!-- Update Role (Primary Owner only) -->
                                <form action="{{ route('venues.update-owner-role', $venue) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="user_id" value="{{ $owner->id }}">
                                    <select name="role" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded px-2 py-1">
                                        <option value="co_owner" {{ $ownerRole === 'co_owner' ? 'selected' : '' }}>Co-Owner</option>
                                        <option value="manager" {{ $ownerRole === 'manager' ? 'selected' : '' }}>Manager</option>
                                    </select>
                                </form>
                                <!-- Delete Owner (Primary Owner only) -->
                                <form action="{{ route('venues.remove-owner', $venue) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this owner?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="user_id" value="{{ $owner->id }}">
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium px-2">
                                        Remove
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500 mb-4">No owners listed.</p>
                    @endif
                    
                    <!-- Pending Ownership Requests (Primary Owner only) -->
                    @if(isset($isPrimaryOwner) && $isPrimaryOwner)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">
                            Pending Ownership Requests
                            @if(isset($pendingRequests) && $pendingRequests)
                                <span class="text-xs text-gray-500">({{ $pendingRequests->count() }})</span>
                            @endif
                        </h4>
                        @if(isset($pendingRequests) && $pendingRequests && $pendingRequests->count() > 0)
                        <div class="space-y-3">
                            @foreach($pendingRequests as $request)
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $request->requester->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-600">{{ $request->requester->email ?? '' }}</p>
                                        @if($request->reason)
                                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($request->reason, 100) }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-1">Requested: {{ $request->requested_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="flex gap-2 ml-4">
                                        <form action="{{ route('venues.approve-request', [$venue, $request]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('venues.reject-request', [$venue, $request]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500">No pending ownership requests.</p>
                        @endif
                    </div>
                    @endif
                </div>
                
                <!-- Add Owner Modal (Primary Owner only) -->
                @if(isset($isPrimaryOwner) && $isPrimaryOwner)
                <div id="add-owner-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="if(event.target === this) this.classList.add('hidden')">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6" onclick="event.stopPropagation()">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Add New Owner</h3>
                            <button onclick="document.getElementById('add-owner-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <form action="{{ route('venues.add-owner', $venue) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="user_email" class="block text-sm font-medium text-gray-700 mb-2">User Email</label>
                                    <input 
                                        type="email" 
                                        id="user_email" 
                                        name="user_email" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                        placeholder="user@example.com"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Enter the email of the user to add as owner</p>
                                </div>
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                    <select 
                                        id="role" 
                                        name="role" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    >
                                        <option value="co_owner">Co-Owner</option>
                                        <option value="manager">Manager</option>
                                    </select>
                                </div>
                                <div class="flex gap-3 pt-2">
                                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                        Add Owner
                                    </button>
                                    <button type="button" onclick="document.getElementById('add-owner-modal').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
                @endif
                @endauth

                <!-- Social Sharing -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Share this venue</h3>
                    <div class="flex flex-wrap gap-3">
                        <!-- Facebook -->
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}&quote={{ urlencode($venue->name . ' - ' . $venue->description) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>

                        <!-- Twitter -->
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($venue->name . ' - Check out this amazing venue!') }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                            Twitter
                        </a>

                        <!-- WhatsApp -->
                        <a href="https://wa.me/?text={{ urlencode($venue->name . ' - ' . request()->url()) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                            </svg>
                            WhatsApp
                        </a>

                        <!-- LinkedIn -->
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-2 px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            LinkedIn
                        </a>

                        <!-- Copy Link -->
                        <button onclick="copyVenueLink()" 
                                class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Copy Link
                        </button>
                    </div>
                </div>

                <!-- Rating Form -->
                <x-rating-form :model="$venue" type="venue" />

                <!-- Owners Management Section (Only visible to venue owners) -->
                @auth
                @if(isset($isOwner) && $isOwner)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        Venue Owners
                    </h3>
                    
                    <!-- Current Owners List -->
                    @if(isset($venueOwners) && $venueOwners->count() > 0)
                    <div class="space-y-3 mb-4">
                        @foreach($venueOwners as $owner)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-semibold">{{ substr($owner->name ?? $owner->email, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $owner->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $owner->email }}</p>
                                    @if($owner->pivot && $owner->pivot->role === 'primary')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-1">Primary Owner</span>
                                    @elseif($owner->pivot && $owner->pivot->role === 'co_owner')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">Co-Owner</span>
                                    @elseif($owner->pivot && $owner->pivot->role === 'manager')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">Manager</span>
                                    @endif
                                </div>
                            </div>
                            @if(isset($isPrimaryOwner) && $isPrimaryOwner && $owner->id !== auth()->id())
                            <form action="{{ route('venues.remove-owner', $venue) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this owner?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="user_id" value="{{ $owner->id }}">
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Remove
                                </button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500 mb-4">No owners listed.</p>
                    @endif
                    
                    <!-- Pending Ownership Requests (Only visible to primary owner) -->
                    @if(isset($isPrimaryOwner) && $isPrimaryOwner && isset($pendingRequests) && $pendingRequests->count() > 0)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Pending Requests</h4>
                        <div class="space-y-3">
                            @foreach($pendingRequests as $request)
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $request->requester->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-600">{{ $request->requester->email ?? '' }}</p>
                                        @if($request->reason)
                                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($request->reason, 100) }}</p>
                                        @endif
                                    </div>
                                    <div class="flex gap-2 ml-4">
                                        <form action="{{ route('venues.approve-request', [$venue, $request]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('venues.reject-request', [$venue, $request]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif
                @endauth

                <!-- Action Buttons -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-3">
                        @auth
                            @if(isset($isOwner) && $isOwner)
                            <a href="{{ route('venues.edit', $venue) }}" class="btn-secondary w-full">
                                <div class="btn-content">
                                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit Venue
                                </div>
                            </a>
                            @elseif($venue->owner_id === auth()->id())
                            <a href="{{ route('venues.edit', $venue) }}" class="btn-secondary w-full">
                                <div class="btn-content">
                                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit Venue
                                </div>
                            </a>
                            @endif
                            
                            @php
                                $isFavorited = false;
                                if (auth()->user()) {
                                    $isFavorited = auth()->user()->favoriteVenues()->where('venue_id', $venue->id)->exists();
                                }
                            @endphp
                            <button 
                                id="venue-favorite-button"
                                class="btn-secondary w-full favorite-toggle {{ $isFavorited ? 'favorited bg-red-50 border-red-200 text-red-700' : '' }}" 
                                data-venue-id="{{ $venue->id }}"
                                onclick="toggleVenueFavorite(this)"
                            >
                                <div class="btn-content">
                                    <svg class="btn-icon {{ $isFavorited ? 'fill-red-500' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    {{ $isFavorited ? 'Remove from Favorites' : 'Add to Favorites' }}
                                </div>
                            </button>
                        @else
                            <button 
                                onclick="window.location.href='/login'" 
                                title="Login to add favorites"
                                class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm rounded-md text-white bg-purple-600 hover:bg-purple-700 transition-colors"
                            >
                                <svg class="btn-icon mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                Login to Add Favorites
                            </button>
                        @endauth
                        
                        <button class="btn-secondary w-full">
                            <div class="btn-content">
                                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                                </svg>
                                Share Venue
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-4xl max-h-full" onclick="event.stopPropagation()">
        <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 text-2xl font-bold">
            ×
        </button>
        <img id="modalImage" src="" alt="Gallery image" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hero carousel functionality
    const carouselContainer = document.getElementById('hero-carousel');
    if (carouselContainer) {
        const slides = carouselContainer.querySelectorAll('.hero-carousel-slide');
        const prevButton = document.getElementById('prev-slide');
        const nextButton = document.getElementById('next-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        
        let currentSlide = 0;
        let autoSlideInterval;
        
        function showSlide(index) {
            if (slides.length === 0) return;
            
            // Hide all slides
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                slide.style.opacity = '0';
            });
            
            // Show current slide
            const targetSlide = slides[index];
            if (targetSlide) {
                targetSlide.classList.add('active');
                targetSlide.style.opacity = '1';
            }
            
            // Update dots
            dots.forEach((dot, i) => {
                dot.classList.toggle('bg-white', i === index);
                dot.classList.toggle('bg-white/50', i !== index);
            });
            
            currentSlide = index;
        }
        
        function nextSlide() {
            const nextIndex = (currentSlide + 1) % slides.length;
            showSlide(nextIndex);
        }
        
        function prevSlide() {
            const prevIndex = currentSlide === 0 ? slides.length - 1 : currentSlide - 1;
            showSlide(prevIndex);
        }
        
        function autoSlide() {
            if (slides.length > 1) {
                autoSlideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
            }
        }
        
        function stopAutoSlide() {
            if (autoSlideInterval) {
                clearInterval(autoSlideInterval);
                autoSlideInterval = null;
            }
        }
        
        // Event listeners
        if (nextButton) nextButton.addEventListener('click', nextSlide);
        if (prevButton) prevButton.addEventListener('click', prevSlide);
        
        // Dot navigation
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showSlide(index));
        });
        
        // Pause auto-slide on hover
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', stopAutoSlide);
            carouselContainer.addEventListener('mouseleave', autoSlide);
        }
        
        // Start auto-slide
        autoSlide();
        
        // Initialize the first slide
        showSlide(0);
    }
    
    // For future, if we need to auto-favorite after auth
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('auth_just_logged_in') || urlParams.has('continue')) {
        console.log('User returned from authentication', urlParams.get('continue') ?? '');
        // Potentially auto-save favorite if intendedBy='action'Guard
    }
});
</script>

<style>
/* Hero carousel styles */
.hero-carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
}

.hero-carousel-slide.active {
    opacity: 1;
}

.carousel-container {
    position: relative;
    width: 100%;
    height: 100%;
}

/* Prevent FOUC (Flash of Unstyled Content) */
.hero-carousel-slide:not(.active) {
    opacity: 0;
}

.hero-carousel-slide.active {
    opacity: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Owl Carousel for venue hero
    if (document.querySelector('.venue-hero-carousel')) {
        $('.venue-hero-carousel').owlCarousel({
            items: 1,
            loop: true,
            autoplay: true,
            autoplayTimeout: 6000, // 6 seconds
            autoplayHoverPause: true,
            nav: true,
            navText: ['<i class="fas fa-chevron-left"></i>', '<i class="fas fa-chevron-right"></i>'],
            dots: true,
            animateOut: 'fadeOut',
            animateIn: 'fadeIn',
            smartSpeed: 1000,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 1
                }
            }
        });
    }
});

// Image modal functions
function openImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    
    if (modal && modalImage) {
        modalImage.src = imageSrc;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto'; // Restore scrolling
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
});

// Copy venue link function
function copyVenueLink() {
    const url = window.location.href;
    
    if (navigator.clipboard && window.isSecureContext) {
        // Use modern clipboard API
        navigator.clipboard.writeText(url).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            fallbackCopyTextToClipboard(url);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyTextToClipboard(url);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess();
        } else {
            showCopyError();
        }
    } catch (err) {
        showCopyError();
    }
    
    document.body.removeChild(textArea);
}

function showCopySuccess() {
    // Create a temporary toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300';
    toast.textContent = 'Link copied to clipboard!';
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

function showCopyError() {
    // Create a temporary error toast
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300';
    toast.textContent = 'Failed to copy link. Please try again.';
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}
</script>

@endsection