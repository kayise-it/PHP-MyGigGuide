// Google Maps functionality
function initGoogleMap(mapId, mapConfig) {

    const mapElement = document.getElementById(`google-map-${mapId}`);
    const loadingElement = document.getElementById(`map-loading-${mapId}`);

    if (!mapElement) {
        console.error(`Map element not found: google-map-${mapId}`);
        return;
    }

    function initMap() {

        if (typeof google === 'undefined' || !google.maps) {
            console.error('Google Maps API not loaded');
            return;
        }

        const map = new google.maps.Map(mapElement, {
            center: mapConfig.center,
            zoom: mapConfig.zoom + mapConfig.zoomDelta,
            disableDefaultUI: mapConfig.compact,
            zoomControl: !mapConfig.compact,
            streetViewControl: false,
            mapTypeControl: false,
            fullscreenControl: !mapConfig.compact
        });

        if (loadingElement) {
            loadingElement.style.display = 'none';
        }

        let userLocation = null;

        // ✅ USER LOCATION MARKER (NO POPUP)
        function addUserLocationMarker(position) {

            if (userLocation) {
                userLocation.setMap(null);
            }

            userLocation = new google.maps.Marker({
                position: position,
                map: map,
                title: "Your Location",
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 9,
                    fillColor: "#1a73e8",
                    fillOpacity: 1,
                    strokeColor: "#ffffff",
                    strokeWeight: 3
                }
            });
        }

        // Get user location
        function getUserLocation() {

            if (navigator.geolocation) {

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
                        console.log("Geolocation error:", error);
                    },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            }
        }

        // Distance calculator
        function distanceKm(a, b) {

            const toRad = (v) => (v * Math.PI) / 180;
            const R = 6371;

            const dLat = toRad(b.lat - a.lat);
            const dLng = toRad(b.lng - a.lng);

            const lat1 = toRad(a.lat);
            const lat2 = toRad(b.lat);

            const x =
                Math.sin(dLat / 2) ** 2 +
                Math.cos(lat1) *
                Math.cos(lat2) *
                Math.sin(dLng / 2) ** 2;

            return 2 * R * Math.asin(Math.sqrt(x));
        }

        // Filter nearby events
        function filterByRadius(center, radiusKm) {

            if (!Array.isArray(mapConfig.events)) return;

            const within = mapConfig.events.filter(e => {

                if (!e.venue ||
                    typeof e.venue.latitude !== 'number' ||
                    typeof e.venue.longitude !== 'number') return false;

                const d = distanceKm(center, {
                    lat: e.venue.latitude,
                    lng: e.venue.longitude
                });

                return d <= radiusKm;
            });

            if (within.length > 0) {

                const bounds = new google.maps.LatLngBounds();

                within.forEach(e =>
                    bounds.extend(
                        new google.maps.LatLng(
                            e.venue.latitude,
                            e.venue.longitude
                        )
                    )
                );

                bounds.extend(
                    new google.maps.LatLng(center.lat, center.lng)
                );

                map.fitBounds(bounds);
            }
        }

        // Add event markers
        const markers = mapConfig.events.map(event => {

            const marker = new google.maps.Marker({
                position: {
                    lat: event.venue.latitude,
                    lng: event.venue.longitude
                },
                map: map,
                title: event.name
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding:10px">
                        <strong>${event.name}</strong><br>
                        ${event.venue.name}<br>
                        <a href="/events/${event.id}" style="color:purple;font-weight:bold">
                            View Details
                        </a>
                    </div>
                `
            });

            marker.addListener("click", () => {
                infoWindow.open(map, marker);
            });

            return marker;
        });

        // Marker clustering
        try {

            if (window.markerClusterer?.MarkerClusterer) {

                new window.markerClusterer.MarkerClusterer({
                    map,
                    markers
                });

            } else if (window.MarkerClusterer) {

                new window.MarkerClusterer({
                    map,
                    markers
                });
            }

        } catch (e) {
            console.warn("Marker clustering not available", e);
        }

        const locationBtn = document.getElementById("get-location-btn");

        if (locationBtn) {
            locationBtn.addEventListener("click", getUserLocation);
        }

        getUserLocation();
    }

    // Load Google Maps API
    if (typeof google === "undefined") {

        const script = document.createElement("script");

        script.src =
            `https://maps.googleapis.com/maps/api/js?key=${mapConfig.apiKey}&libraries=places&callback=initMap`;

        script.async = true;
        script.defer = true;

        window.initMap = initMap;

        document.head.appendChild(script);

    } else {

        initMap();

    }
}

// Export
window.initGoogleMap = initGoogleMap;