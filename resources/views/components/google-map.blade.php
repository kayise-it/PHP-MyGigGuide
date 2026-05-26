@props([
    'events' => collect(),
    'height' => '400px',
    'width' => '100%',
    'showLegend' => true,
    'compact' => false,
    'zoomDelta' => 0,
    'center' => ['lat' => -26.1550, 'lng' => 28.0595], // Default to Johannesburg
    'radiusKm' => 50,
    'categoriesList' => null
])

@php
    // Filter events that have venue coordinates
    $eventsWithCoordinates = $events->filter(function($event) {
        return $event->venue && 
               $event->venue->latitude && 
               $event->venue->longitude &&
               !is_null($event->venue->latitude) && 
               !is_null($event->venue->longitude);
    });

    // Count unique venues
    $uniqueVenueCount = $eventsWithCoordinates->pluck('venue.id')->unique()->count();

    // Calculate center if events have coordinates
    if ($eventsWithCoordinates->count() > 0) {
        $avgLat = $eventsWithCoordinates->avg('venue.latitude');
        $avgLng = $eventsWithCoordinates->avg('venue.longitude');
        $center = ['lat' => $avgLat, 'lng' => $avgLng];
    }

    $categoriesFromEvents = $eventsWithCoordinates
        ->flatMap(function ($event) {
            $relatedCategories = $event->categories
                ? $event->categories->map(function ($category) {
                    return [
                        'slug' => $category->slug ?? \Illuminate\Support\Str::slug($category->name),
                        'name' => $category->name,
                    ];
                })
                : collect();

            if ($relatedCategories->isEmpty() && !empty($event->category)) {
                $relatedCategories = collect([[
                    'slug' => \Illuminate\Support\Str::slug($event->category),
                    'name' => $event->category,
                ]]);
            }

            return $relatedCategories;
        })
        ->filter(function ($category) {
            return !empty($category['slug'] ?? null) && !empty($category['name'] ?? null);
        })
        ->unique('slug')
        ->values();

    $categoriesFromProps = collect($categoriesList ?? [])
        ->map(function ($category) {
            if ($category instanceof \App\Models\Category) {
                return [
                    'slug' => $category->slug ?? \Illuminate\Support\Str::slug($category->name),
                    'name' => $category->name,
                ];
            }

            if (is_array($category)) {
                $name = $category['name'] ?? ($category['title'] ?? null);

                return [
                    'slug' => $category['slug'] ?? ($name ? \Illuminate\Support\Str::slug($name) : null),
                    'name' => $name,
                ];
            }

            if (is_string($category)) {
                return [
                    'slug' => \Illuminate\Support\Str::slug($category),
                    'name' => $category,
                ];
            }

            return null;
        })
        ->filter(function ($category) {
            return is_array($category)
                && !empty($category['slug'] ?? null)
                && !empty($category['name'] ?? null);
        })
        ->values();

    $categoryOptions = $categoriesFromProps
        ->concat($categoriesFromEvents)
        ->unique('slug')
        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();
@endphp

@if(!$compact)
<div class="mb-4">
    <div class="flex flex-wrap gap-2 items-center justify-between">
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm font-medium text-slate-300 mr-2">Show events:</span>
            <button 
                data-filter="all" 
                class="{{ $siteBrand->mapFilterActiveClass() }}"
            >
                All Events
            </button>
            <button 
                data-filter="today" 
                class="{{ $siteBrand->mapFilterInactiveClass() }}"
            >
                Today
            </button>
            <button 
                data-filter="tomorrow" 
                class="{{ $siteBrand->mapFilterInactiveClass() }}"
            >
                Tomorrow
            </button>
            <button 
                data-filter="this-week" 
                class="{{ $siteBrand->mapFilterInactiveClass() }}"
            >
                This Week
            </button>
            <button 
                data-filter="this-month" 
                class="{{ $siteBrand->mapFilterInactiveClass() }}"
            >
                This Month
            </button>
            <button 
                data-filter="next-month" 
                class="{{ $siteBrand->mapFilterInactiveClass() }}"
            >
                Next Month
            </button>
            <span id="filtered-count-{{ $attributes->get('id', 'default') }}" class="ml-2 text-sm text-slate-400"></span>
        </div>
        
        <div class="flex items-center gap-2">
            <select id="category-filter-{{ $attributes->get('id', 'default') }}" class="{{ $siteBrand->formSelectClass() }} text-sm py-2">
                <option value="all">All categories</option>
                @foreach($categoryOptions as $category)
                    <option value="{{ $category['slug'] }}">{{ $category['name'] }}</option>
                @endforeach
            </select>
            <button
                id="get-location-btn-{{ $attributes->get('id', 'default') }}"
                class="{{ $siteBrand->mapFilterInactiveClass() }}"
                title="Get my location"
            >
                <div class="flex items-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="mr-2">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="{{ $siteBrand->mapUserLocationHex() }}"/>
                    </svg>
                    My Location
                </div>
            </button>
        </div>
    </div>
</div>
@endif

<div class="{{ $siteBrand->mapFrameClass() }}" style="height: {{ $height }}; width: {{ $width }};">
    @if($showLegend && !$compact)
    <div class="{{ $siteBrand->mapLegendClass() }}">
        <h3 class="text-sm font-semibold text-white mb-2">Map Legend</h3>
        <div class="space-y-2">
            <div class="flex items-center">
                <div class="w-5 h-5 rounded-full mr-2 flex items-center justify-center relative" style="background: {{ $siteBrand->mapUserLocationHex() }}; box-shadow: 0 0 8px {{ $siteBrand->mapUserLocationHex() }}80;">
                    <div class="w-2.5 h-2.5 bg-white rounded-full"></div>
                </div>
                <span class="text-xs text-slate-300">Your Location</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 rounded mr-2 flex items-center justify-center" style="background: {{ $siteBrand->mapMarkerHex() }};">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="9" r="2" fill="#ffffff"/>
                    </svg>
                </div>
                <span class="text-xs text-slate-300">Live Gigs ({{ $uniqueVenueCount }})</span>
            </div>
        </div>
    </div>
    @endif


    {{-- Map Container --}}
    <div id="google-map-{{ $attributes->get('id', 'default') }}" class="w-full h-full"></div>


    <div id="map-loading-{{ $attributes->get('id', 'default') }}" class="{{ $siteBrand->mapLoadingClass() }}">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 {{ $siteBrand->isRogues ? 'border-sky-400' : 'border-indigo-500' }} mx-auto mb-4"></div>
            <p class="{{ $siteBrand->detailMutedTextClass() }}">Loading map...</p>
        </div>
    </div>

    @if($eventsWithCoordinates->count() === 0)
    <div class="absolute inset-0 w-full h-full flex items-center justify-center {{ $siteBrand->isRogues ? 'bg-slate-950' : 'bg-black' }}">
        <div class="text-center px-4">
            <div class="text-slate-600 mb-2">
                <svg class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <p class="text-slate-300 mb-2">No upcoming events found</p>
            <p class="text-slate-500 text-sm">Check back later for new events</p>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapId = '{{ $attributes->get("id", "default") }}';
    
    console.log('Initializing map with ID:', mapId);
    console.log('Events with coordinates count:', {{ $eventsWithCoordinates->count() }});
    
    // Map configuration
    const mapConfig = {
        center: { lat: {{ $center['lat'] }}, lng: {{ $center['lng'] }} },
        zoom: {{ $eventsWithCoordinates->count() > 1 ? 10 : 12 }},
        events: {!! json_encode($eventsWithCoordinates->map(function($event) {
            $categories = $event->categories
                ? $event->categories->map(function ($category) {
                    return [
                        'slug' => $category->slug ?? \Illuminate\Support\Str::slug($category->name),
                        'name' => $category->name,
                    ];
                })
                : collect();

            if ($categories->isEmpty() && !empty($event->category)) {
                $categories = collect([[
                    'slug' => \Illuminate\Support\Str::slug($event->category),
                    'name' => $event->category,
                ]]);
            }

            $posterUrl = null;
            if (! empty($event->poster)) {
                try {
                    $posterUrl = url(\Illuminate\Support\Facades\Storage::url($event->poster));
                } catch (\Throwable $e) {
                    $posterUrl = null;
                }
            }

            $galleryUrls = collect($event->gallery ?? [])
                ->filter(fn ($p) => is_string($p) && $p !== '')
                ->map(fn ($p) => url(\Illuminate\Support\Facades\Storage::url($p)))
                ->values()
                ->all();

            $genre = $event->categories?->first()?->name ?? ($event->category ?? null);

            return [
                'id' => $event->id,
                'name' => $event->name,
                'date' => $event->date->toISOString(),
                'time' => $event->time ? $event->time->format('H:i') : null,
                'price' => $event->price,
                'categories' => $categories->values(),
                'genre' => $genre,
                'poster_url' => $posterUrl,
                'images' => $galleryUrls,
                'venue' => [
                    'name' => $event->venue->name,
                    'latitude' => (float) $event->venue->latitude,
                    'longitude' => (float) $event->venue->longitude,
                ],
            ];
        })) !!},
        showLegend: {{ $showLegend ? 'true' : 'false' }},
        compact: {{ $compact ? 'true' : 'false' }},
        zoomDelta: {{ $zoomDelta }},
        apiKey: '{{ config('services.google_maps.api_key') }}',
        radiusKm: {{ (int) $radiusKm }},
        styles: {!! $siteBrand->googleMapStylesJson() !!},
        markerHex: @json($siteBrand->mapMarkerHex()),
        userLocationHex: @json($siteBrand->mapUserLocationHex()),
        ui: {
            filterActiveClass: @json($siteBrand->mapFilterActiveClass()),
            filterInactiveClass: @json($siteBrand->mapFilterInactiveClass()),
        },
    };

    console.log('Map config:', mapConfig);

    // Initialize the map directly since the bundled function might not be available
    const mapElement = document.getElementById(`google-map-${mapId}`);
    if (!mapElement) {
        console.error(`Map element not found: google-map-${mapId}`);
        return;
    }

    console.log('Map element found:', mapElement);

    // Load Google Maps API and initialize
    function loadGoogleMaps() {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${mapConfig.apiKey}&libraries=places&callback=initMap`;
        script.async = true;
        script.defer = true;
        script.onerror = function() {
            console.error('Failed to load Google Maps API');
        };
        document.head.appendChild(script);
    }

    // Initialize map function
    window.initMap = function() {
        console.log('Google Maps API loaded, initializing map...');
        
        if (typeof google === 'undefined' || !google.maps) {
            console.error('Google Maps API not loaded');
            return;
        }

        function createMap(center, zoom) {
            return new google.maps.Map(mapElement, {
                center: center,
                zoom: zoom,
                styles: mapConfig.styles,
                colorScheme: 'DARK',
                disableDefaultUI: mapConfig.compact,
                zoomControl: !mapConfig.compact,
                streetViewControl: false,
                mapTypeControl: false,
                fullscreenControl: !mapConfig.compact,
                gestureHandling: mapConfig.compact ? 'none' : 'auto',
            });
        }

        function userLocationIcon() {
            const hex = mapConfig.userLocationHex;
            return {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="${hex}" opacity="0.3">
                            <animate attributeName="r" values="8;12;8" dur="2s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.3;0.1;0.3" dur="2s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="12" cy="12" r="8" fill="#ffffff" stroke="${hex}" stroke-width="2"/>
                        <circle cx="12" cy="12" r="4" fill="${hex}"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(24, 24),
                anchor: new google.maps.Point(12, 12)
            };
        }

        function userLocationInfoHtml() {
            return `<div style="padding:10px;font-family:system-ui,sans-serif;text-align:center;"><h3 style="font-weight:700;color:#fff;margin:0 0 6px;">Your Location</h3><p style="font-size:13px;color:#94a3b8;margin:0;">You are here</p></div>`;
        }

            // Try to get user location first
            function initializeMapWithLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const userLocation = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            };
                            console.log('User location found:', userLocation);
                            
                            const map = createMap(userLocation, 13);
                            
                            userLocationMarker = new google.maps.Marker({
                                position: userLocation,
                                map: map,
                                title: 'Your Location',
                                icon: userLocationIcon(),
                                zIndex: 9999
                            });
                            
                            userLocationMarker.addListener('click', function() {
                                const infoWindow = new google.maps.InfoWindow({ disableAutoPan: true, maxWidth: 240 });
                                infoWindow.setContent(userLocationInfoHtml());
                                infoWindow.open(map, userLocationMarker);
                            });
                            
                            initializeMapFeatures(map);
                        },
                        function(error) {
                            console.log('Location access denied or failed, using default center');
                            const map = createMap(mapConfig.center, mapConfig.zoom + mapConfig.zoomDelta);
                            initializeMapFeatures(map);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 300000
                        }
                    );
                } else {
                    console.log('Geolocation not supported, using default center');
                    const map = createMap(mapConfig.center, mapConfig.zoom + mapConfig.zoomDelta);
                    initializeMapFeatures(map);
                }
            }
            
            // Function to initialize map features after map is created
            function initializeMapFeatures(map) {
        console.log('Map created successfully');

        // Hide loading element
        const loadingElement = document.getElementById(`map-loading-${mapId}`);
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }

        // Shared InfoWindow for marker popups
        let infoWindow = new google.maps.InfoWindow({ disableAutoPan: true, maxWidth: 240 });
        
        // Store all markers for filtering
        const allMarkers = [];
        let currentDateFilter = 'all';
        let currentCategoryFilter = 'all';
        let userLocationMarker = null;

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

            const categoryLabels = Array.isArray(event.categories) && event.categories.length
                ? event.categories.map(category => category.name).join(', ')
                : null;

            return `
                <div style="padding:10px;max-width:240px;font-family:system-ui,sans-serif;">
                    <h3 style="font-weight:600;color:#fff;margin:0 0 6px;font-size:11px;line-height:1.3;">${event.name}</h3>
                    <div style="font-size:10px;color:#94a3b8;margin-bottom:8px;">
                        <div>${formatDate(eventDate)} • ${event.time || eventDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                        <div>${event.venue.name}</div>
                    </div>
                    <a href="/events/${event.id}" style="display:block;text-align:center;background:${mapConfig.markerHex};color:#fff;padding:8px 12px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">View Details</a>
                </div>
            `;
        }

        function showInfoWindow(marker, event) {
            infoWindow.setContent(createInfoWindowContent(event));
            infoWindow.open(map, marker);
        }

        // Add event markers
        mapConfig.events.forEach(event => {
            console.log('Adding marker for event:', event.name);
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
                            <circle cx="12" cy="12" r="10" fill="${mapConfig.markerHex}" opacity="0.3" filter="url(#glow)">
                                <animate attributeName="r" values="10;14;10" dur="2s" repeatCount="indefinite"/>
                                <animate attributeName="opacity" values="0.3;0.1;0.3" dur="2s" repeatCount="indefinite"/>
                            </circle>
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="${mapConfig.markerHex}" stroke="#ffffff" stroke-width="1"/>
                            <circle cx="12" cy="9" r="2.5" fill="#ffffff"/>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(48, 48),
                    anchor: new google.maps.Point(16, 32)
                }
            });

            // Add click event to open popup
            marker.addListener('click', function() {
                showInfoWindow(marker, event);
            });

            // Store marker with event data for filtering
            allMarkers.push({
                marker: marker,
                event: event
            });

            console.log('Marker added for:', event.name);
        });

        // Combined filter: date + category
        function filterMarkers(filterType) {
            const now = new Date();
            now.setHours(0, 0, 0, 0);
            
            let visibleCount = 0;
            
            allMarkers.forEach(({ marker, event }) => {
                const eventDate = new Date(event.date);
                eventDate.setHours(0, 0, 0, 0);
                
                let shouldShow = false;
                
                switch(filterType) {
                    case 'all':
                        shouldShow = true;
                        break;
                    
                    case 'today':
                        shouldShow = eventDate.getTime() === now.getTime();
                        break;
                    
                    case 'tomorrow':
                        const tomorrow = new Date(now);
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        shouldShow = eventDate.getTime() === tomorrow.getTime();
                        break;
                    
                    case 'this-week':
                        const weekEnd = new Date(now);
                        weekEnd.setDate(weekEnd.getDate() + 7);
                        shouldShow = eventDate >= now && eventDate <= weekEnd;
                        break;
                    
                    case 'this-month':
                        const monthEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                        shouldShow = eventDate >= now && 
                                   eventDate.getMonth() === now.getMonth() && 
                                   eventDate.getFullYear() === now.getFullYear();
                        break;
                    
                    case 'next-month':
                        const nextMonthStart = new Date(now.getFullYear(), now.getMonth() + 1, 1);
                        const nextMonthEnd = new Date(now.getFullYear(), now.getMonth() + 2, 0);
                        shouldShow = eventDate >= nextMonthStart && eventDate <= nextMonthEnd;
                        break;
                }

                // Apply category filter
                if (shouldShow && currentCategoryFilter !== 'all') {
                    const categorySlugs = Array.isArray(event.categories)
                        ? event.categories.map(category => category.slug)
                        : [];
                    shouldShow = categorySlugs.includes(currentCategoryFilter);
                }
                
                marker.setVisible(shouldShow);
                if (shouldShow) visibleCount++;
            });
            
            // Update counter
            const counterElement = document.getElementById(`filtered-count-${mapId}`);
            if (counterElement) {
                if (filterType === 'all' && currentCategoryFilter === 'all') {
                    counterElement.textContent = `(${visibleCount} total)`;
                } else {
                    counterElement.textContent = `(${visibleCount} shown)`;
                }
            }
            
            // Adjust map bounds to fit visible markers if needed
            const shouldFitBounds = filterType !== 'all' || currentCategoryFilter !== 'all';
            if (visibleCount > 0 && shouldFitBounds) {
                const bounds = new google.maps.LatLngBounds();
                allMarkers.forEach(({ marker }) => {
                    if (marker.getVisible()) {
                        bounds.extend(marker.getPosition());
                    }
                });
                map.fitBounds(bounds);
                
                // Add some padding
                const currentZoom = map.getZoom();
                if (currentZoom > 15) {
                    map.setZoom(15);
                }
            }
        }
        
        // Set up filter button event listeners
        const filterButtons = document.querySelectorAll('.time-filter-btn');
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const filterType = this.getAttribute('data-filter');
                currentDateFilter = filterType;
                
                // Update button styles
                filterButtons.forEach(b => {
                    b.className = mapConfig.ui.filterInactiveClass;
                });
                
                this.className = mapConfig.ui.filterActiveClass;
                
                // Apply filter
                filterMarkers(currentDateFilter);
            });
        });

        // Category filter change handler
        const categorySelect = document.getElementById(`category-filter-${mapId}`);
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                currentCategoryFilter = this.value || 'all';
                filterMarkers(currentDateFilter);
            });
        }
        
        // Initialize counter
        filterMarkers('all');
        
        // Handle "Get my location" button
        const locationBtn = document.getElementById(`get-location-btn-${mapId}`);
        if (locationBtn) {
            locationBtn.addEventListener('click', function() {
                if (navigator.geolocation) {
                    // Show loading state
                    this.classList.add('animate-pulse');
                    
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const userLocation = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            };
                            
                            // Remove previous user location marker if exists
                            if (userLocationMarker) {
                                userLocationMarker.setMap(null);
                            }
                            
                            // Create custom marker for user location
                            userLocationMarker = new google.maps.Marker({
                                position: userLocation,
                                map: map,
                                title: 'Your Location',
                                icon: userLocationIcon(),
                                zIndex: 9999
                            });
                            
                            userLocationMarker.addListener('click', function() {
                                infoWindow.setContent(userLocationInfoHtml());
                                infoWindow.open(map, userLocationMarker);
                            });
                            
                            // Pan to user location with smooth animation
                            map.panTo(userLocation);
                            
                            // Optionally zoom in a bit
                            const currentZoom = map.getZoom();
                            if (currentZoom < 13) {
                                map.setZoom(13);
                            }
                            
                            // Remove loading state
                            locationBtn.classList.remove('animate-pulse');
                            
                            console.log('User location set:', userLocation);
                        },
                        function(error) {
                            console.error('Error getting location:', error);
                            let errorMessage = 'Unable to get your location. ';
                            
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMessage += 'Please allow location access in your browser settings.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMessage += 'Location information is unavailable.';
                                    break;
                                case error.TIMEOUT:
                                    errorMessage += 'Location request timed out. Please try again.';
                                    break;
                                default:
                                    errorMessage += 'Please enable location services and try again.';
                                    break;
                            }
                            
                            alert(errorMessage);
                            locationBtn.classList.remove('animate-pulse');
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 300000
                        }
                    );
                } else {
                    alert('Geolocation is not supported by your browser.');
                }
            });
        }

        console.log('Map initialization complete');
            }
            
            // Start the location-based initialization
            initializeMapWithLocation();
    };

    // Load Google Maps API
    if (typeof google === 'undefined') {
        loadGoogleMaps();
    } else {
        window.initMap();
    }
});
</script>
@endpush