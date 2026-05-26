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
    $mapId = 'single-event-map-' . Str::random(8);
    $loadingId = 'single-map-loading-' . Str::random(8);
@endphp

<div class="{{ $siteBrand->mapFrameClass() }}" style="height: {{ $height }}; width: {{ $width }};">
    <div id="{{ $mapId }}" class="w-full h-full"></div>

    <div id="{{ $loadingId }}" class="{{ $siteBrand->mapLoadingClass() }}">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 {{ $siteBrand->isRogues ? 'border-sky-400' : 'border-indigo-500' }} mx-auto mb-4"></div>
            <p class="{{ $siteBrand->detailMutedTextClass() }}">Loading map...</p>
        </div>
    </div>

    @if($address)
    <div class="absolute top-4 left-4 z-10 {{ $siteBrand->isRogues ? 'bg-slate-900 border border-slate-700' : 'bg-[#12121A] border border-white/10' }} rounded-lg shadow-lg p-3">
        <div class="text-sm">
            <div class="font-semibold text-white mb-1">Location</div>
            <div class="{{ $siteBrand->detailMutedTextClass() }}">{{ $address }}</div>
        </div>
    </div>
    @endif
</div>

@include('components.google-maps-loader')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapId = @json($mapId);
    const loadingId = @json($loadingId);
    const mapStyles = {!! $siteBrand->googleMapStylesJson() !!};
    const markerHex = @json($siteBrand->mapMarkerHex());

    addGoogleMapsCallback(function() {
        initSingleEventMap(mapId, loadingId, {
            center: { lat: {{ (float) $latitude }}, lng: {{ (float) $longitude }} },
            zoom: 15,
            address: @json($address),
            styles: mapStyles,
            markerHex: markerHex,
        });
    });
});

function initSingleEventMap(mapId, loadingId, config) {
    function initMap() {
        if (typeof google === 'undefined' || !google.maps) {
            const loadingEl = document.getElementById(loadingId);
            if (loadingEl) {
                loadingEl.innerHTML = `
                    <div class="text-center px-4">
                        <p class="text-slate-400 mb-2">Map not available</p>
                        <p class="text-slate-500 text-sm">${config.address || 'Event location'}</p>
                    </div>
                `;
            }
            return;
        }

        const map = new google.maps.Map(document.getElementById(mapId), {
            center: config.center,
            zoom: config.zoom,
            styles: config.styles,
            colorScheme: 'DARK',
            disableDefaultUI: false,
            zoomControl: true,
            streetViewControl: false,
            mapTypeControl: false,
            fullscreenControl: true,
            gestureHandling: 'auto',
        });

        const loadingEl = document.getElementById(loadingId);
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }

        const marker = new google.maps.Marker({
            position: config.center,
            map: map,
            title: config.address || 'Event Location',
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="${config.markerHex}"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(24, 24),
                anchor: new google.maps.Point(12, 12)
            }
        });

        if (config.address) {
            const infoWindow = new google.maps.InfoWindow({
                content: `<div style="padding:8px 10px;font-family:system-ui,sans-serif;color:#e2e8f0;"><strong style="color:#fff;">Event Location</strong><br><span style="color:#94a3b8;font-size:13px;">${config.address}</span></div>`
            });

            marker.addListener('click', function() {
                infoWindow.open(map, marker);
            });
        }
    }

    initMap();
}

window.initSingleEventMap = initSingleEventMap;
</script>
@endpush
