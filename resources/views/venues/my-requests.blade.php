@extends('layouts.app')

@section('title', 'My Venue Requests - My Gig Guide')
@section('description', 'Manage your venue ownership requests')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        My Venue Requests
                    </h1>
                    <p class="text-gray-600 mt-2">Request to join venues or view your request history</p>
                </div>
                <div class="flex space-x-3">
                    <button 
                        onclick="openRequestModal()"
                        class="btn-primary"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Request to Join a Venue
                    </button>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Pending Requests -->
        @if($pendingRequests->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Pending Requests</h2>
                <div class="space-y-4">
                    @foreach($pendingRequests as $request)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <a href="{{ route('venues.show', $request->venue) }}" class="hover:text-purple-600">
                                            {{ $request->venue->name }}
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <strong>Address:</strong> {{ $request->venue->address ?? 'N/A' }}
                                        @if($request->venue->city)
                                            , {{ $request->venue->city }}
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-700 mt-2">
                                        <strong>Your reason:</strong> {{ $request->reason }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Requested on {{ $request->requested_at->format('M d, Y \a\t g:i A') }}
                                    </p>
                                </div>
                                <div class="ml-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Past Requests (History) -->
        @if($pastRequests->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Request History</h2>
                <div class="space-y-4">
                    @foreach($pastRequests as $request)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <a href="{{ route('venues.show', $request->venue) }}" class="hover:text-purple-600">
                                            {{ $request->venue->name }}
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <strong>Address:</strong> {{ $request->venue->address ?? 'N/A' }}
                                        @if($request->venue->city)
                                            , {{ $request->venue->city }}
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-700 mt-2">
                                        <strong>Your reason:</strong> {{ $request->reason }}
                                    </p>
                                    @if($request->rejection_reason)
                                        <p class="text-sm text-red-600 mt-2">
                                            <strong>Rejection reason:</strong> {{ $request->rejection_reason }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-gray-500 mt-2">
                                        @if($request->isApproved())
                                            Approved on {{ $request->reviewed_at->format('M d, Y \a\t g:i A') }}
                                        @else
                                            Rejected on {{ $request->reviewed_at->format('M d, Y \a\t g:i A') }}
                                        @endif
                                    </p>
                                </div>
                                <div class="ml-4">
                                    @if($request->isApproved())
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if($pendingRequests->count() === 0 && $pastRequests->count() === 0)
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No venue requests yet</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Click the button above to request to join a venue.
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Request Modal -->
<div id="requestVenueModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeRequestModal()"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('venues.request-ownership') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Request to Join a Venue
                            </h3>
                            
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
                        onclick="closeRequestModal()"
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
function openRequestModal() {
    document.getElementById('requestVenueModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRequestModal() {
    document.getElementById('requestVenueModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeRequestModal();
    }
});
</script>
@endsection

