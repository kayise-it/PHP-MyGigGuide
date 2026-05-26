<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', $siteBrand->name)</title>
    <meta name="description" content="@yield('description', $siteBrand->tagline)">
    <link rel="icon" href="{{ $siteBrand->faviconUrl() }}" type="image/png" sizes="32x32">
    @if(! $siteBrand->isRogues)
    <link rel="apple-touch-icon" href="{{ asset('logos/apple-touch-icon.png') }}">
    @endif
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    
    @stack('head')
    
    <!-- Favorite Button Styles -->
    <style>
    .favorite-toggle.favorited svg {
        fill: #ef4444 !important;
        stroke: #ef4444 !important;
    }
    .favorite-toggle:not(.favorited) svg {
        fill: none !important;
        stroke: white !important;
    }
    .favorite-toggle:hover svg {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }
    .favorite-toggle.loading {
        opacity: 0.7;
        pointer-events: none;
    }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 {{ $siteBrand->bodyClass() }}">
    <div id="app" x-data="{ mobileMenuOpen: false }">
        @include('layouts.navigation')
        
        <main class="min-h-screen">
            @yield('content')
        </main>
        
        @include('layouts.footer')
    </div>
    
    <!-- jQuery (required for Owl Carousel) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <!-- Owl Carousel JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    
    <!-- Generic Favorite Toggle Function -->
    <script>
    function toggleFavorite(button) {
        // Determine the type and ID from data attributes
        const eventId = button.dataset.eventId;
        const venueId = button.dataset.venueId;
        const artistId = button.dataset.artistId;
        const organiserId = button.dataset.organiserId;
        
        // Determine which type and ID to use
        let type, id;
        if (eventId) { type = 'events'; id = eventId; }
        else if (venueId) { type = 'venues'; id = venueId; }
        else if (artistId) { type = 'artists'; id = artistId; }
        else if (organiserId) { type = 'organisers'; id = organiserId; }
        else { return; }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        // Add loading state
        const originalContent = button.innerHTML;
        const iconSize = button.querySelector('svg')?.classList.contains('h-7') ? 'h-7 w-7' : 'h-5 w-5';
        const strokeWidth = button.querySelector('svg')?.getAttribute('stroke-width') || '2';
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            alert('Security token missing. Please refresh the page and try again.');
            button.innerHTML = originalContent;
            button.disabled = false;
            return;
        }
        
        button.innerHTML = `<svg class="${iconSize} text-white animate-spin" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-dasharray="31.416" stroke-dashoffset="15.708"/></svg>`;
        button.disabled = true;
        
        // Make AJAX request
        fetch(`/favorites/${type}/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            if (response.status === 419) {
                // CSRF token expired - redirect to login
                window.location.href = '/login?continue=' + encodeURIComponent(window.location.href);
                return Promise.reject('CSRF token expired');
            }
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update visual state based on server response  
                if (data.favorited) {
                    // Make heart red
                    button.classList.add('favorited');
                    const redSvg = `<svg class="${iconSize} text-white fill-red-500" viewBox="0 0 24 24" stroke="currentColor" stroke-width="${strokeWidth}"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>`;
                    button.innerHTML = redSvg;
                } else {
                    // Remove red color - restore to original state
                    button.classList.remove('favorited');
                    const defaultSvg = `<svg class="${iconSize} text-white fill-none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="${strokeWidth}"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>`;
                    button.innerHTML = defaultSvg;
                }
                
                // Show feedback
                if (window.showToast) {
                    window.showToast(data.message);
                } else {
                    console.log(data.message);
                }
            } else {
                throw new Error(data.message || 'Failed to toggle favorite');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (error !== 'CSRF token expired') {
                alert('Failed to update favorite. Please try again.');
            }
            button.innerHTML = originalContent;
        })
        .finally(() => {
            // Restore button state
            button.disabled = false;
        });
    }
    
    // Keep backward compatibility
    function toggleEventFavorite(button) { toggleFavorite(button); }
    function toggleVenueFavorite(button) { toggleFavorite(button); }
    function toggleArtistFavorite(button) { toggleFavorite(button); }
    function toggleOrganiserFavorite(button) { toggleFavorite(button); }
    </script>
    
    @stack('scripts')
</body>
</html>
