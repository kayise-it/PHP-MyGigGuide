@props([
    'artist',
    'href' => null,
    'rating' => null,
])

@php
    $targetUrl = $href ?? route('artists.show', $artist->id);
    
    // Default when artist has no usable profile image (keep for cards / listings)
    $image = asset('logos/logo2.jpeg');
    $profilePicture = $artist->profile_picture ?? $artist->user->profile_picture ?? null;
    if ($profilePicture && !str_contains($profilePicture, '/tmp/php') && !str_contains($profilePicture, 'tmp.php')) {
        // Use 'public' disk since images are stored in storage/app/public
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($profilePicture)) {
            $image = \Illuminate\Support\Facades\Storage::disk('public')->url($profilePicture);
        }
    }
    
    $computedRating = $rating ?? (method_exists($artist, 'ratings') ? round((float) ($artist->ratings()->avg('rating') ?? 0), 1) : null);
    $displayName = $artist->stage_name ?? ($artist->user->name ?? 'Artist');
    $subtitle = trim(($artist->genre ?? '') . (isset($artist->user) && isset($artist->user->username) ? ' • @' . $artist->user->username : ''));
@endphp

<div class="group relative block overflow-hidden rounded-2xl shadow-lg bg-gray-100 aspect-[4/5]">
    <a href="{{ $targetUrl }}" class="block w-full h-full">
        <img src="{{ $image }}" alt="{{ $displayName }}" class="w-full h-full object-cover transition-transform duration-300 ease-out group-hover:scale-[1.04]" loading="lazy">
        
        <!-- Gradient Overlay (inline gradient to ensure compatibility) -->
        <div style="position:absolute; inset:0; pointer-events:none; background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.7) 35%, rgba(0,0,0,0.0) 100%);"></div>
    </a>

    <!-- Favorite Button -->
    <div class="absolute top-2 right-2 z-10">
        <x-favorite-button :model="$artist" type="artist" size="md" />
    </div>

    <!-- Artist Name at Bottom -->
    <div class="absolute left-0 right-0 bottom-0 pb-6 pt-16 px-4">
        <h3 class="ml-4 text-white font-bold text-3xl truncate transform scale-110 drop-shadow-lg">{{ $displayName }}</h3>
    </div>
</div>


