@props([
    'model' => null, // The item being reviewed (Artist, Event, Venue, etc.)
    'type' => 'artist', // 'artist', 'event', 'venue', 'organiser'
    'showHeader' => true,
    'maxInitialReviews' => 3,
    'loadMoreLimit' => 5
])

@php
    $totalReviews = $model->ratings()->count();
    $averageRating = $model->ratings()->avg('rating') ?? 0;
    $recentReviews = $model->ratings()->with('user')->latest()->take($maxInitialReviews)->get();
@endphp

@if($totalReviews > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
        @if($showHeader)
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Reviews & Ratings</h3>
                    <div class="flex items-center mt-2">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="h-5 w-5 {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.888c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-900">{{ number_format($averageRating, 1) }}/5</span>
                        <span class="ml-2 text-sm text-gray-500">({{ $totalReviews }} {{ Str::plural('review', $totalReviews) }})</span>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Reviews List -->
        <div class="space-y-4" id="reviews-list">
            @foreach($recentReviews as $rating)
                <div class="border border-gray-200 rounded-lg p-4 review-item">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center">
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.888c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-900">{{ $rating->user->name }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ $rating->created_at->diffForHumans() }}</span>
                    </div>
                    @if($rating->review)
                        <p class="text-sm text-gray-600">{{ $rating->review }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        
        <!-- Load More Button -->
        @if($totalReviews > $maxInitialReviews)
            <div class="mt-6 text-center">
                <button id="load-more-reviews" 
                        class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors duration-200 text-sm font-medium"
                        data-model-id="{{ $model->id }}"
                        data-model-type="{{ get_class($model) }}"
                        data-loaded="{{ $maxInitialReviews }}"
                        data-total="{{ $totalReviews }}"
                        data-limit="{{ $loadMoreLimit }}">
                    Load More Reviews ({{ $totalReviews - $maxInitialReviews }} remaining)
                </button>
            </div>
        @endif
        
        <!-- View All Reviews Link -->
        @if($totalReviews > $maxInitialReviews)
            <div class="mt-4 text-center">
                <a href="{{ route('ratings.index', ['type' => $type, 'id' => $model->id]) }}" 
                   class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                    View All {{ $totalReviews }} Reviews →
                </a>
            </div>
        @endif
    </div>
@else
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 text-center">
        <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No reviews yet</h3>
        <p class="text-gray-500">Be the first to review this {{ $type }}!</p>
    </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.getElementById('load-more-reviews');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const modelId = this.dataset.modelId;
            const modelType = this.dataset.modelType;
            const loaded = parseInt(this.dataset.loaded);
            const total = parseInt(this.dataset.total);
            const limit = parseInt(this.dataset.limit);
            const reviewsList = document.getElementById('reviews-list');
            
            // Show loading state
            const originalText = this.textContent;
            this.textContent = 'Loading...';
            this.disabled = true;
            
            // Make AJAX request to load more reviews
            fetch('/reviews/load-more', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    model_id: modelId,
                    model_type: modelType,
                    offset: loaded,
                    limit: limit
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Append new reviews
                    data.reviews.forEach(review => {
                        const reviewElement = document.createElement('div');
                        reviewElement.className = 'border border-gray-200 rounded-lg p-4 review-item';
                        reviewElement.innerHTML = `
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center">
                                    <div class="flex text-yellow-400">
                                        ${Array.from({length: 5}, (_, i) => 
                                            `<svg class="h-4 w-4 ${i < review.rating ? 'text-yellow-400' : 'text-gray-300'}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.888c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>`
                                        ).join('')}
                                    </div>
                                    <span class="ml-2 text-sm font-medium text-gray-900">${review.user.name}</span>
                                </div>
                                <span class="text-xs text-gray-500">${review.created_at}</span>
                            </div>
                            ${review.review ? `<p class="text-sm text-gray-600">${review.review}</p>` : ''}
                        `;
                        reviewsList.appendChild(reviewElement);
                    });
                    
                    // Update button state
                    const newLoaded = loaded + data.reviews.length;
                    this.dataset.loaded = newLoaded;
                    
                    if (newLoaded >= total) {
                        this.style.display = 'none';
                    } else {
                        this.textContent = `Load More Reviews (${total - newLoaded} remaining)`;
                    }
                } else {
                    alert('Failed to load more reviews');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while loading reviews');
            })
            .finally(() => {
                this.disabled = false;
                if (this.style.display !== 'none') {
                    this.textContent = originalText;
                }
            });
        });
    }
});
</script>
@endpush

