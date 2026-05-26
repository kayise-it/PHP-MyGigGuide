@props([
    'model' => null, // The item being rated (Artist, Event, Venue, etc.)
    'type' => 'artist', // 'artist', 'event', 'venue', 'organiser'
])

@php
    $existingRating = null;
    if (auth()->check() && $model) {
        $existingRating = $model->ratings()
            ->where('user_id', auth()->id())
            ->first();
    }
@endphp

@auth
<div class="rating-section {{ $siteBrand->detailPanelClass('p-4') }}">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-medium text-white">
            {{ $existingRating ? 'Update your rating' : 'Rate this ' . ucfirst($type) }}
        </h3>
        <div class="flex items-center space-x-1" id="star-rating">
            @for($i = 1; $i <= 5; $i++)
                <button type="button" class="star-btn transition-colors {{ $existingRating && $i <= $existingRating->rating ? 'text-yellow-400' : 'text-slate-600' }} hover:text-yellow-400" data-rating="{{ $i }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.888c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </button>
            @endfor
        </div>
    </div>

    <div id="review-form-container" class="{{ $existingRating ? '' : 'hidden' }} mt-3 pt-3 {{ $siteBrand->isRogues ? 'border-t border-slate-700' : 'border-t border-white/10' }}">
        <form id="rating-form" class="space-y-3">
            @csrf
            <input type="hidden" name="rateable_type" value="{{ get_class($model) }}">
            <input type="hidden" name="rateable_id" value="{{ $model->id }}">
            <input type="hidden" name="rating" id="rating-input" value="{{ $existingRating ? $existingRating->rating : '' }}">
            
            <div>
                <label for="review" class="{{ $siteBrand->formLabelClass() }} text-xs">Your Review (Optional)</label>
                <textarea id="review" name="review" rows="3" 
                          class="{{ $siteBrand->formInputClass() }} text-sm"
                          placeholder="Share your experience...">{{ $existingRating ? $existingRating->review : '' }}</textarea>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="button" id="cancel-rating" 
                        class="text-sm {{ $siteBrand->detailMutedTextClass() }} hover:text-white transition-colors">
                    {{ $existingRating ? 'Close' : 'Cancel' }}
                </button>
                <button type="submit" class="btn-primary px-4 py-1.5 text-sm font-medium">
                    {{ $existingRating ? 'Update Rating' : 'Submit' }}
                </button>
            </div>
        </form>
    </div>
</div>
@else
<div class="{{ $siteBrand->detailPanelClass() }} text-center">
    <p class="{{ $siteBrand->detailMutedTextClass() }} mb-4">Please <a href="{{ route('login') }}" class="{{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }} font-medium">login</a> to rate this {{ $type }}.</p>
</div>
@endauth

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const starButtons = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating-input');
    const form = document.getElementById('rating-form');
    const reviewFormContainer = document.getElementById('review-form-container');
    const cancelRating = document.getElementById('cancel-rating');
    
    // Initialize with existing rating if present
    let selectedRating = {{ $existingRating ? $existingRating->rating : 0 }};
    if (selectedRating > 0) {
        updateStarDisplay(selectedRating);
    }
    
    // Star rating functionality
    starButtons.forEach(button => {
        button.addEventListener('click', function() {
            selectedRating = parseInt(this.dataset.rating);
            ratingInput.value = selectedRating;
            
            // Update star display
            updateStarDisplay(selectedRating);
            
            // Show the review form inline
            showReviewForm();
        });
        
        // Hover effects
        button.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            updateStarDisplay(rating);
        });
        
        button.addEventListener('mouseleave', function() {
            updateStarDisplay(selectedRating);
        });
    });
    
    function updateStarDisplay(rating) {
        starButtons.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-slate-600');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-slate-600');
            }
        });
    }
    
    function showReviewForm() {
        reviewFormContainer.classList.remove('hidden');
    }
    
    function hideReviewForm() {
        const hasExistingRating = {{ $existingRating ? 'true' : 'false' }};
        reviewFormContainer.classList.add('hidden');
        
        if (!hasExistingRating) {
            // Reset form only if no existing rating
            if (form) {
                form.reset();
                ratingInput.value = '';
                selectedRating = 0;
                updateStarDisplay(0);
            }
        }
        // If existing rating, just hide the form - stars and values remain as they are
    }
    
    // Cancel handler
    if (cancelRating) {
        cancelRating.addEventListener('click', hideReviewForm);
    }
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
            
            fetch('{{ route("ratings.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const contentType = response.headers.get('content-type') || '';
                let data;
                if (contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    data = { success: false, message: 'Unexpected response. Please ensure you are logged in.' };
                }
                return { ok: response.ok, status: response.status, data };
            })
            .then(({ ok, status, data }) => {
                if (data && data.success) {
                    // Show success message (controller returns appropriate message)
                    alert(data.message || 'Rating saved successfully!');
                    
                    // Reload page to show updated rating
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    // Build friendly error from validation
                    let msg = 'Failed to submit rating';
                    if (data && data.message) msg = data.message;
                    if (data && data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey) {
                            msg = data.errors[firstKey][0] || msg;
                        }
                    }
                    alert(msg);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the rating');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });
    }
});
</script>
@endpush
