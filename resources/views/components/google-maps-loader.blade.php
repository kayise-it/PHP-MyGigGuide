{{-- Centralized Google Maps API Loader --}}
@if(!isset($googleMapsLoaded))
    @php $googleMapsLoaded = true; @endphp
    
    <script>
    // Prevent multiple Google Maps API loads
    window.googleMapsLoaded = false;
    window.googleMapsCallbacks = [];
    window.googleMapsAPIKey = '{{ env('GOOGLE_MAPS_API_KEY', 'YOUR_API_KEY') }}';

    function loadGoogleMaps() {
        if (window.googleMapsLoaded || document.querySelector('script[src*="maps.googleapis.com"]')) {
            console.log('Google Maps already loaded or loading');
            return;
        }

        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${window.googleMapsAPIKey}&libraries=places&callback=initGoogleMaps`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        
        window.googleMapsLoaded = true;
    }

    function initGoogleMaps() {
        console.log('Google Maps API loaded');
        window.googleMapsCallbacks.forEach(callback => {
            try {
                callback();
            } catch (error) {
                console.error('Error in Google Maps callback:', error);
            }
        });
        window.googleMapsCallbacks = [];
    }

    function addGoogleMapsCallback(callback) {
        if (window.googleMapsLoaded && typeof google !== 'undefined') {
            callback();
        } else {
            window.googleMapsCallbacks.push(callback);
        }
    }

    // Load Google Maps when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadGoogleMaps);
    } else {
        loadGoogleMaps();
    }
    </script>
@endif

