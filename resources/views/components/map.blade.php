@props([
    'latitude' => null,
    'longitude' => null,
    'address' => null,
    'height' => '400px',
    'width' => '100%',
])

@php
    if (!$latitude || !$longitude) {
        throw new \Exception('Map component requires latitude and longitude props');
    }
@endphp

<div class="w-full rounded-xl overflow-hidden shadow-sm border border-purple-100 relative" style="height: {{ $height }}; width: {{ $width }};">
    @php
        $mapId = 'single-event-map-' . Str::random(8);
        $loadingId = 'single-map-loading-' . Str::random(8);
    @endphp

    {{-- Map Container --}}
    <div id="{{ $mapId }}" class="w-full h-full"></div>

    {{-- Loading State --}}
    <div id="{{ $loadingId }}" class="absolute inset-0 w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-50 to-blue-50">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Loading map...</p>
        </div>
    </div>

    {{-- Address Info Overlay --}}
    @if($address)
    <div class="absolute top-4 left-4 z-10 bg-white rounded-lg shadow-lg p-3 border border-gray-200">
        <div class="text-sm">
            <div class="font-semibold text-gray-900 mb-1">Location</div>
            <div class="text-gray-600">{{ $address }}</div>
        </div>
    </div>
    @endif
</div>

{{-- Use centralized Google Maps loader --}}
@include('components.google-maps-loader')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapId = "{{ $mapId }}";
    const loadingId = "{{ $loadingId }}";
    
    const mapElement = document.getElementById(mapId);
    const loadingElement = document.getElementById(loadingId);
    
    if (!mapElement || !loadingElement) {
        console.error('Map elements not found');
        return;
    }

    // Use centralized Google Maps loader
    addGoogleMapsCallback(function() {
        initSingleEventMap(mapId, loadingId, {
            center: { lat: {{ (float) $latitude }}, lng: {{ (float) $longitude }} },
            zoom: 15,
            address: {!! json_encode($address) !!},
            apiKey: window.googleMapsAPIKey
        });
    });
});

// Single Event Map Function
function initSingleEventMap(mapId, loadingId, config) {
    function initMap() {
        if (typeof google === 'undefined' || !google.maps) {
            console.error('Google Maps API not loaded');
            // Show fallback info
            const loadingEl = document.getElementById(loadingId);
            if (loadingEl) {
                loadingEl.innerHTML = `
                    <div class="text-center">
                        <div class="text-gray-400 mb-2">
                            <svg class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="text-gray-600 mb-2">Map not available</p>
                        <p class="text-gray-500 text-sm">Location: ${config.address || 'Event location'}</p>
                    </div>
                `;
            }
            return;
        }

        const map = new google.maps.Map(document.getElementById(mapId), {
            center: config.center,
            zoom: config.zoom,
            styles: [
                {
                    featureType: 'all',
                    elementType: 'geometry.fill',
                    stylers: [{ color: '#fefefe' }]
                },
                {
                    featureType: 'water',
                    elementType: 'geometry',
                    stylers: [{ color: '#e3f2fd' }]
                },
                {
                    featureType: 'road',
                    elementType: 'geometry.stroke',
                    stylers: [{ color: '#e8eaf6' }]
                }
            ],
            disableDefaultUI: false,
            zoomControl: true,
            streetViewControl: false,
            mapTypeControl: false,
            fullscreenControl: true,
            gestureHandling: 'auto',
        });

        // Hide loading
        const loadingEl = document.getElementById(loadingId);
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }

        // Add marker for the event location
        const marker = new google.maps.Marker({
            position: config.center,
            map: map,
            title: config.address || 'Event Location',
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#7c3aed"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(24, 24),
                anchor: new google.maps.Point(12, 12)
            }
        });

        // Add info window if address is provided
        if (config.address) {
            const infoWindow = new google.maps.InfoWindow({
                content: `<div class="text-center py-2"><strong>Event Location</strong><br>${config.address}</div>`
            });
            
            marker.addListener('click', function() {
                infoWindow.open(map, marker);
            });
        }
    }

    // Assume centralized loader handles API; just try to init
    initMap();
}

// Export for global access
window.initSingleEventMap = initSingleEventMap;
</script>
@endpush
