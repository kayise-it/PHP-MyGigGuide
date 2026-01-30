<!-- Claim Venue Modal -->
<div id="claimVenueModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div id="claimVenueModalBackdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeClaimVenueModal()"></div>

        <!-- Modal panel -->
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" onclick="event.stopPropagation()">
            <form id="claimVenueForm" action="{{ route('venues.request-ownership') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Request to Join a Venue
                            </h3>
                            
                            @if(session('success'))
                                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                                    {{ session('success') }}
                                </div>
                            @endif
                            
                            @if($errors->any())
                                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                                    <ul class="list-disc list-inside text-sm">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <!-- Venue Selector -->
                            <div class="mb-4">
                                <label for="venue_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Search and select venue: *
                                </label>
                                <x-venue-selector 
                                    name="venue_id" 
                                    placeholder="Search for venue name..."
                                    userRole="all"
                                    :required="true"
                                    class="w-full"
                                />
                                @error('venue_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Reason field -->
                            <div class="mb-4">
                                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                    Why do you want to join this venue? *
                                </label>
                                <textarea 
                                    id="reason"
                                    name="reason" 
                                    required
                                    rows="4"
                                    placeholder="e.g., I'm the new manager, I'm a co-owner, I work here..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('reason') border-red-500 @enderror"
                                >{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Optional proof upload -->
                            <div class="mb-4">
                                <label for="proof_document" class="block text-sm font-medium text-gray-700 mb-2">
                                    Proof document (optional)
                                </label>
                                <input 
                                    type="file" 
                                    id="proof_document"
                                    name="proof_document" 
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                >
                                <p class="mt-1 text-xs text-gray-500">
                                    Upload a document proving your relationship to this venue (PDF, JPG, PNG - max 5MB)
                                </p>
                                @error('proof_document')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Submit Request
                    </button>
                    <button 
                        type="button"
                        onclick="closeClaimVenueModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openClaimVenueModal() {
    document.getElementById('claimVenueModal')?.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeClaimVenueModal() {
    document.getElementById('claimVenueModal')?.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeClaimVenueModal();
    }
});

// Close modal when clicking the backdrop (not the modal content)
document.getElementById('claimVenueModalBackdrop')?.addEventListener('click', function() {
    closeClaimVenueModal();
});

// Handle form submission with AJAX
document.getElementById('claimVenueForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(async response => {
        const data = await response.json();
        if (response.ok && data.success) {
            // Show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg';
            successDiv.textContent = data.message || 'Request submitted successfully!';
            const formContent = form.querySelector('.sm\\:flex');
            if (formContent) {
                // Remove any existing messages
                const existingMessages = formContent.querySelectorAll('.bg-green-50, .bg-red-50');
                existingMessages.forEach(msg => msg.remove());
                formContent.insertBefore(successDiv, formContent.firstChild);
            }
            
            // Reset form
            form.reset();
            
            // Close modal after 2 seconds and reload
            setTimeout(() => {
                closeClaimVenueModal();
                window.location.reload();
            }, 2000);
        } else {
            // Show error message in form
            const errorDiv = document.createElement('div');
            errorDiv.className = 'mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
            errorDiv.textContent = data.message || 'An error occurred. Please try again.';
            const formContent = form.querySelector('.sm\\:flex');
            if (formContent) {
                // Remove any existing messages
                const existingMessages = formContent.querySelectorAll('.bg-green-50, .bg-red-50');
                existingMessages.forEach(msg => msg.remove());
                formContent.insertBefore(errorDiv, formContent.firstChild);
            }
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Show error message in form
        const errorDiv = document.createElement('div');
        errorDiv.className = 'mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
        errorDiv.textContent = 'An error occurred. Please try again.';
        const formContent = form.querySelector('.sm\\:flex');
        if (formContent) {
            // Remove any existing messages
            const existingMessages = formContent.querySelectorAll('.bg-green-50, .bg-red-50');
            existingMessages.forEach(msg => msg.remove());
            formContent.insertBefore(errorDiv, formContent.firstChild);
        }
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});
</script>

