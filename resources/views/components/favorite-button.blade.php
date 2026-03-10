@props([
    'model' => null, // The item being favorited (Event, Venue, Artist, etc.)
    'type' => 'event', // 'event', 'venue', 'artist', 'organiser'
    'size' => 'lg', // 'sm', 'md', 'lg'
])

@auth
@php
    $favorites = auth()->user()->{'favorite' . Str::plural(ucfirst($type))}();
    $isFavorited = $favorites->where($type . '_id', $model->id)->exists();
$toggleRoute = "favorites.{$type}s.toggle";
@endphp

<button 
    class="favorite-toggle {{ $size === 'sm' ? 'p-2' : ($size === 'md' ? 'p-2.5' : 'p-3') }} {{ $isFavorited ? 'favorited' : '' }} bg-white/20 hover:bg-white/30 rounded-full transition-all"
    data-{{ $type }}-id="{{ $model->id }}"
    data-type="{{ $type }}"
    data-route="{{ route($toggleRoute, $model) }}"
    title="{{ $isFavorited ? 'Remove from favorites' : 'Add to favorites' }}"
>
    <svg class="{{ $size === 'sm' ? 'h-4 w-4' : ($size === 'md' ? 'h-5 w-5' : 'h-6 w-6') }} text-white" 
         {{ $isFavorited ? 'fill=#ef4444' : 'fill=none' }} stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
    </svg>
</button>
@else
<button onclick="window.location.href='{{ route('login', ['continue' => request()->fullUrl()]) }}'" 
        class="{{ $size === 'sm' ? 'p-2' : ($size === 'md' ? 'p-2.5' : 'p-3') }} bg-white/20 hover:bg-white/30 rounded-full transition-all" 
        title="Login to add favorites">
    <svg class="{{ $size === 'sm' ? 'h-4 w-4' : ($size === 'md' ? 'h-5 w-5' : 'h-6 w-6') }} text-white" 
         fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
    </svg>
</button>
@endauth

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize favorite toggle functionality for all components
    document.querySelectorAll('.favorite-toggle').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Read the id using the raw data attribute (dataset camel-cases keys)
            const type = this.dataset.type;
            const id = this.getAttribute('data-' + type + '-id');
            const route = this.dataset.route;
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : '';

            if (!id || !route) return;
            
            if (!csrfToken) {
                console.error('CSRF token not found');
                alert('Security token missing. Please refresh the page and try again.');
                return;
            }

            // Add loading state
            const originalIcon = this.innerHTML;
            const size = this.classList.contains('p-2') ? '4' : (this.classList.contains('p-2.5') ? '5' : '6');
            
            // Prevent double-clicks
            if (this.disabled) return;
            
            this.innerHTML = `<svg class="h-${size} w-${size} text-white animate-spin" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-dasharray="31.416" stroke-dashoffset="15.708"/></svg>`;
            this.disabled = true;

            // Make AJAX request
            fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => {
                if (response.status === 401) {
                    // Not authenticated: redirect to login with continue param back to current page
                    window.location.href = `{{ route('login') }}?continue=${encodeURIComponent(window.location.href)}`;
                    return Promise.reject('Unauthenticated');
                }
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    // Toggle visual state
                    this.classList.toggle('favorited');
                    
                    // Update title
                    this.title = this.classList.contains('favorited') ? 'Remove from favorites' : 'Add to favorites';
                    
                    // Update SVG fill
                    const svg = this.querySelector('svg');
                    if (this.classList.contains('favorited')) {
                        svg.setAttribute('fill', '#ef4444');
                    } else {
                        svg.removeAttribute('fill');
                    }
                    
                    // Show feedback
                    if (window.showToast) {
                        window.showToast(data.message);
                    } else {
                        console.log(data.message);
                    }
                } else {
                    throw new Error(data?.message || 'Failed to toggle favorite');
                }
            })
            .catch(error => {
                if (error !== 'Unauthenticated') {
                    console.error('Error:', error);
                    alert('Failed to update favorite. Please try again.');
                }
            })
            .finally(() => {
                // Restore button state
                this.innerHTML = originalIcon;
                this.disabled = false;
            });
        });
    });
});
</script>
@endpush
