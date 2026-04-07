// Copy of resources/js/google-map.js adapted for direct browser module usage
// (no changes needed other than ensuring it's available in /public/js)

// Google Maps functionality
function initGoogleMap(mapId, mapConfig) {
    console.log('initGoogleMap called with:', mapId, mapConfig);
    
    const mapElement = document.getElementById(`google-map-${mapId}`);
    const loadingElement = document.getElementById(`map-loading-${mapId}`);
    
    console.log('Map element found:', mapElement);
    console.log('Loading element found:', loadingElement);
    
    if (!mapElement) {
        console.error(`Map element not found: google-map-${mapId}`);
        return;
    }

    // Initialize Google Maps
    function initMap() {
        if (typeof google === 'undefined' || !google.maps) {
            console.error('Google Maps API not loaded');
            return;
        }

        const map = new google.maps.Map(mapElement, {
            center: mapConfig.center,
            zoom: mapConfig.zoom + mapConfig.zoomDelta,
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
            disableDefaultUI: mapConfig.compact,
            zoomControl: !mapConfig.compact,
            streetViewControl: false,
            mapTypeControl: false,
            fullscreenControl: !mapConfig.compact,
            gestureHandling: mapConfig.compact ? 'none' : 'auto',
        });

        // Hide loading
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }

        let userLocation = null;
        let selectedEvent = null;

        // User location marker
        function addUserLocationMarker(position) {
            userLocation = new google.maps.Marker({
                position: position,
                map: map,
                title: 'Your Location',
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <!-- Pulsing outer ring -->
                            <circle cx="12" cy="12" r="10" fill="#3b82f6" opacity="0.3">
                                <animate attributeName="r" values="8;12;8" dur="2s" repeatCount="indefinite"/>
                                <animate attributeName="opacity" values="0.3;0.1;0.3" dur="2s" repeatCount="indefinite"/>
                            </circle>
                            <!-- White background circle -->
                            <circle cx="12" cy="12" r="8" fill="#ffffff" stroke="#3b82f6" stroke-width="2"/>
                            <!-- Inner blue dot -->
                            <circle cx="12" cy="12" r="4" fill="#3b82f6"/>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(24, 24),
                    anchor: new google.maps.Point(12, 12)
                }
            });
        }

        // Get user location
        function getUserLocation() {
            if (navigator.geolocation && !userLocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const userPos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        addUserLocationMarker(userPos);
                        map.setCenter(userPos);
                        filterByRadius(userPos, mapConfig.radiusKm || 50);
                    },
                    (error) => {
                        console.log('Geolocation error:', error);
                    }
                , { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 });
            }
        }

        // Haversine distance in km
        function distanceKm(a, b) {
            const toRad = (v) => (v * Math.PI) / 180;
            const R = 6371;
            const dLat = toRad(b.lat - a.lat);
            const dLng = toRad(b.lng - a.lng);
            const lat1 = toRad(a.lat);
            const lat2 = toRad(b.lat);
            const x = Math.sin(dLat/2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng/2) ** 2;
            return 2 * R * Math.asin(Math.sqrt(x));
        }

        function filterByRadius(center, radiusKm) {
            if (!Array.isArray(mapConfig.events)) return;
            const within = mapConfig.events.filter(e => {
                if (!e.venue || typeof e.venue.latitude !== 'number' || typeof e.venue.longitude !== 'number') return false;
                const d = distanceKm(center, { lat: e.venue.latitude, lng: e.venue.longitude });
                return d <= radiusKm;
            });

            // If there are nearby events, fit bounds
            if (within.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                within.forEach(e => bounds.extend(new google.maps.LatLng(e.venue.latitude, e.venue.longitude)));
                bounds.extend(new google.maps.LatLng(center.lat, center.lng));
                map.fitBounds(bounds);
            }
        }

        // Add event markers and collect for clustering
        const markers = mapConfig.events.map(event => {
            const marker = new google.maps.Marker({
                position: { 
                    lat: event.venue.latitude, 
                    lng: event.venue.longitude 
                },
                map: map,
                title: event.name,
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg width="48" height="48" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <filter id="glow">
                                    <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                                    <feMerge> 
                                        <feMergeNode in="coloredBlur"/>
                                        <feMergeNode in="SourceGraphic"/>
                                    </feMerge>
                                </filter>
                            </defs>
                            <circle cx="12" cy="12" r="10" fill="#7c3aed" opacity="0.3" filter="url(#glow)">
                                <animate attributeName="r" values="10;14;10" dur="2s" repeatCount="indefinite"/>
                                <animate attributeName="opacity" values="0.3;0.1;0.3" dur="2s" repeatCount="indefinite"/>
                            </circle>
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#7c3aed" stroke="#ffffff" stroke-width="1"/>
                            <circle cx="12" cy="9" r="2.5" fill="#ffffff"/>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(48, 48),
                    anchor: new google.maps.Point(16, 32)
                }
            });

            // Click event
            if (!mapConfig.compact) {
                marker.addListener('click', () => {
                    selectedEvent = event;
                    showInfoWindow(marker, event);
                    openSheet(event);
                });
            }
            return marker;
        });

        // Marker clustering (if library loaded)
        try {
            if (window.markerClusterer?.MarkerClusterer) {
                new window.markerClusterer.MarkerClusterer({ map, markers });
            } else if (window.MarkerClusterer) {
                new window.MarkerClusterer({ map, markers });
            }
        } catch (e) {
            console.warn('Marker clustering not available', e);
        }

        // Info window
        let infoWindow = new google.maps.InfoWindow({ disableAutoPan: true, maxWidth: 320 });

        function showInfoWindow(marker, event) {
            const content = createInfoWindowContent(event);
            infoWindow.setContent(content);
            infoWindow.open(map, marker);
        }

        // Bottom sheet logic
        const sheet = document.getElementById(`map-sheet-${mapId}`);
        const sheetTitle = document.getElementById(`map-sheet-title-${mapId}`);
        const sheetContent = document.getElementById(`map-sheet-content-${mapId}`);
        const sheetClose = document.getElementById(`map-sheet-close-${mapId}`);

        function renderSheetContent(event) {
            const eventDate = new Date(event.date);
            sheetTitle.textContent = event.name;
            sheetContent.innerHTML = `
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span>${event.venue.name}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>${eventDate.toLocaleDateString()}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>${event.time || eventDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                    </div>
                    <div class="pt-2">
                        <a href="/events/${event.id}" class="inline-flex items-center bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 shadow-md">View details</a>
                    </div>
                </div>`;
        }

        function openSheet(event) {
            if (!sheet) return;
            renderSheetContent(event);
            sheet.classList.remove('hidden');
        }

        function closeSheet() {
            if (!sheet) return;
            sheet.classList.add('hidden');
        }

        if (sheetClose) sheetClose.addEventListener('click', closeSheet);

        function hideInfoWindow() {
            infoWindow.close();
        }

        function createInfoWindowContent(event) {
            const eventDate = new Date(event.date);
            const formatDate = (date) => {
                const now = new Date();
                const diffTime = date - now;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays === 0) return 'Today';
                if (diffDays === 1) return 'Tomorrow';
                if (diffDays < 7) return `In ${diffDays} days`;
                return date.toLocaleDateString();
            };

            return `
                <div class="p-4 max-w-xs">
                    <h3 class="font-bold text-gray-900 mb-2 text-xs leading-tight">${event.name}</h3>
                    <div class="space-y-2 text-xs text-gray-600">
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-purple-600 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="truncate">${event.venue.name}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-purple-600 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>${formatDate(eventDate)}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-purple-600 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>${event.time || eventDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                        </div>
                        <div class="pt-3">
                            <a href="/events/${event.id}" class="inline-flex items-center bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-4 py-2 rounded-lg text-base font-semibold transition-all duration-200 shadow-md">View details</a>
                        </div>
                    </div>
                </div>
            `;
        }

        // Location button event
        const locationBtn = document.getElementById('get-location-btn');
        if (locationBtn) {
            locationBtn.addEventListener('click', getUserLocation);
        }

        // Try to use browser location on load (secure context required)
        getUserLocation();
    }

    // Load Google Maps API
    if (typeof google === 'undefined') {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${mapConfig.apiKey}&libraries=places&callback=initMap`;
        script.async = true;
        script.defer = true;
        window.initMap = initMap;
        document.head.appendChild(script);
    } else {
        initMap();
    }
}

// Export for use in other files
window.initGoogleMap = initGoogleMap;


