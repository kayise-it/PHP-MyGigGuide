@extends('layouts.app')

@section('title', ($artist->stage_name ?? $artist->user->name) . ' - Artist')

@push('head')
@php
    $artistName = $artist->stage_name ?? $artist->user->name ?? 'Artist';
    $description = \Illuminate\Support\Str::limit(strip_tags($artist->bio ?? 'Discover this amazing artist on My Gig Guide.'), 160);
    $imageUrl = null;
    $profilePicture = $artist->profile_picture ?? $artist->user->profile_picture ?? null;
    if ($profilePicture && !str_contains($profilePicture, '/tmp/php') && !str_contains($profilePicture, 'tmp.php')) {
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($profilePicture)) {
            $imageUrl = url(\Illuminate\Support\Facades\Storage::disk('public')->url($profilePicture));
        }
    }
    if (!$imageUrl) {
        $imageUrl = url(asset('logos/logo2.jpeg'));
    }
@endphp
<!-- Open Graph / Facebook -->
<meta property="og:type" content="profile">
<meta property="og:url" content="{{ route('artists.show', $artist) }}">
<meta property="og:title" content="{{ $artistName }} - My Gig Guide">
<meta property="og:description" content="{{ $description }}">
@if($imageUrl)
<meta property="og:image" content="{{ $imageUrl }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif
<meta property="og:site_name" content="My Gig Guide">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ route('artists.show', $artist) }}">
<meta name="twitter:title" content="{{ $artistName }} - My Gig Guide">
<meta name="twitter:description" content="{{ $description }}">
@if($imageUrl)
<meta name="twitter:image" content="{{ $imageUrl }}">
@endif
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Hero Section -->
    <div class="relative h-96 bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl mb-8 overflow-hidden">
        @php
            $profilePicture = $artist->profile_picture ?? $artist->user->profile_picture ?? null;
            $profileImage = asset('logos/logo2.jpeg');
            if ($profilePicture && !str_contains($profilePicture, '/tmp/php') && !str_contains($profilePicture, 'tmp.php')) {
                // Use 'public' disk since images are stored in storage/app/public
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($profilePicture)) {
                    $profileImage = \Illuminate\Support\Facades\Storage::disk('public')->url($profilePicture);
                }
            }
        @endphp

        <img src="{{ $profileImage }}" alt="{{ $artist->stage_name ?? $artist->user->name }}" 
             class="absolute inset-0 w-full h-full object-cover opacity-30">
        
            <div class="absolute inset-0 bg-black/40"></div>
        
        <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                            <h1 class="text-4xl md:text-6xl font-bold mb-2">{{ $artist->stage_name ?? $artist->user->name }}</h1>
                            <div class="flex items-center flex-wrap gap-2 text-lg">
                                <x-page-ownership-badge :entity="$artist" variant="hero" />
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm font-semibold">★ {{ number_format($ratingAvg, 1) }}</span>
                                @if($artist->genre)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-white">{{ $artist->genre }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Favorite Button -->
                        <div class="absolute top-4 right-4">
                            <x-favorite-button :model="$artist" type="artist" size="lg" />
                        </div>
                        </div>

    <x-page-claim-cta :entity="$artist" />

    <!-- Content Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Bio Section -->
            @if($artist->bio)
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">About</h2>
                <div class="prose prose-lg text-gray-700">
                    {!! nl2br(e($artist->bio)) !!}
                </div>
            </div>
            @endif

            <!-- YouTube Videos -->
            @if($artist->youtubeVideos->count() > 0)
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Videos</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($artist->youtubeVideos as $video)
                    <x-youtube-video :video="$video" />
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Upcoming Events -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Upcoming Events</h2>
                @if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
                <div class="space-y-4">
                    @foreach($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="block border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $event->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $event->date->format('M d, Y') }}{{ $event->time ? ' · '.$event->time->format('H:i') : '' }}</p>
                                @if($event->venue)
                                    <p class="text-xs text-gray-500">{{ $event->venue->name }}</p>
                                @endif
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">Upcoming</span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-500">
                    <p>No upcoming events scheduled.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Artist Info -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Artist Info</h3>
                <div class="space-y-3">
                    @if($artist->genre)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Genre:</span>
                        <span class="font-medium text-gray-900">{{ $artist->genre }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">Page status:</span>
                        <x-page-ownership-badge :entity="$artist" variant="inline" />
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Rating:</span>
                        <span class="font-medium text-yellow-600">★ {{ number_format($ratingAvg, 1) }}</span>
                    </div>
                </div>
            </div>

            <!-- Rating Form -->
            <x-rating-form :model="$artist" type="artist" />

            {{-- Booking Section - Disabled. Enable via superadmin backend control when ready --}}
            {{--
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Book This Artist</h3>
        @auth
                <div class="space-y-4">
                    <form id="booking-form">
                    @csrf
                    <input type="hidden" name="artist_id" value="{{ $artist->id }}">

                    <div>
                        <label for="event_name" class="block text-sm font-medium text-gray-700 mb-2">Event Name</label>
                        <input type="text" id="event_name" name="event_name" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="event_date" class="block text-sm font-medium text-gray-700 mb-2">Event Date</label>
                        <input type="date" id="event_date" name="event_date" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="venue" class="block text-sm font-medium text-gray-700 mb-2">Venue</label>
                        <input type="text" id="venue" name="venue" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea id="message" name="message" rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>

                        <button type="submit" class="w-full bg-purple-600 text-white py-3 px-6 rounded-lg hover:bg-purple-700 transition-colors duration-200 font-medium">
                        Send Booking Request
                    </button>
                </form>
        </div>
        @else
                <div class="text-center py-4">
                    <p class="text-gray-600 mb-4">Please log in to send a booking request.</p>
                    <div class="space-y-2">
                        <a href="{{ route('login') }}" class="block bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors duration-200">
                    Login
                </a>
                        <a href="{{ route('register') }}" class="block bg-white text-purple-600 border border-purple-600 py-2 px-4 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                    Sign Up
                </a>
            </div>
        </div>
        @endauth
    </div>
            --}}
</div>
    </div>
</div>
@endsection

{{-- Booking Form Script - Disabled. Enable when booking section is re-enabled --}}
{{--
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bookingForm = document.getElementById('booking-form');
        if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
                e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;

            submitButton.textContent = 'Sending...';
                submitButton.disabled = true;
            
            fetch('/bookings', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Booking request sent successfully!');
                    this.reset();
                } else {
                    alert('Failed to send booking request. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to send booking request. Please try again.');
            })
            .finally(() => {
                submitButton.textContent = originalText;
                    submitButton.disabled = false;
            });
            });
        }
    });
</script>
@endpush
--}}