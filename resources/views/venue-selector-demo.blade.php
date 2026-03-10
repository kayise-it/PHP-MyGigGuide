@extends('layouts.app')

@section('title', 'Venue Selector Demo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Venue Selector Component</h1>
            <p class="mt-2 text-gray-600">A reusable venue selector with search and filter functionality</p>
        </div>

        <div class="space-y-8">
            <!-- Basic Usage -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Usage</h2>
                <p class="text-gray-600 mb-4">Simple venue selector without any special configuration:</p>
                
                <x-venue-selector 
                    name="venue_id" 
                    placeholder="Select a venue..."
                />
            </div>

            <!-- With Pre-selected Venue -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">With Pre-selected Venue</h2>
                <p class="text-gray-600 mb-4">Venue selector with a pre-selected venue:</p>
                
                <x-venue-selector 
                    name="venue_id_2" 
                    :selectedVenueId="1"
                    placeholder="Select a venue..."
                />
            </div>

            <!-- For Organiser -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">For Organiser</h2>
                <p class="text-gray-600 mb-4">Venue selector configured for an organiser (shows own venues first):</p>
                
                <x-venue-selector 
                    name="venue_id_3" 
                    userRole="organiser"
                    :organiserId="1"
                    placeholder="Select a venue..."
                />
            </div>

            <!-- For Artist -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">For Artist</h2>
                <p class="text-gray-600 mb-4">Venue selector configured for an artist (shows venues where they've performed first):</p>
                
                <x-venue-selector 
                    name="venue_id_4" 
                    userRole="artist"
                    :artistId="1"
                    placeholder="Select a venue..."
                />
            </div>

            <!-- In Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">In Form</h2>
                <p class="text-gray-600 mb-4">Venue selector integrated into a form:</p>
                
                <form class="space-y-4" x-data="{ selectedVenue: null }" @venue-selected="selectedVenue = $event.detail.venue">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Name</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Enter event name">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Venue</label>
                        <x-venue-selector 
                            name="venue_id" 
                            placeholder="Choose a venue for your event..."
                            required
                        />
                    </div>
                    
                    <div x-show="selectedVenue" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <h3 class="font-medium text-green-800">Selected Venue:</h3>
                        <p class="text-green-700" x-text="selectedVenue ? selectedVenue.name + ' - ' + selectedVenue.location : ''"></p>
                    </div>
                    
                    <button type="submit" class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors">
                        Create Event
                    </button>
                </form>
            </div>

            <!-- Usage Examples -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Usage Examples</h2>
                
                <div class="space-y-4">
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">Basic Usage</h3>
                        <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto"><code>&lt;x-venue-selector 
    name="venue_id" 
    placeholder="Select a venue..."
/&gt;</code></pre>
                    </div>
                    
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">With Pre-selected Venue</h3>
                        <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto"><code>&lt;x-venue-selector 
    name="venue_id" 
    :selectedVenueId="1"
    placeholder="Select a venue..."
/&gt;</code></pre>
                    </div>
                    
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">For Organiser</h3>
                        <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto"><code>&lt;x-venue-selector 
    name="venue_id" 
    userRole="organiser"
    :organiserId="1"
    placeholder="Select a venue..."
/&gt;</code></pre>
                    </div>
                    
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">For Artist</h3>
                        <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto"><code>&lt;x-venue-selector 
    name="venue_id" 
    userRole="artist"
    :artistId="1"
    placeholder="Select a venue..."
/&gt;</code></pre>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Features</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900">Search & Filter</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Real-time search by name or location</li>
                            <li>• Filter by city (popular South African cities)</li>
                            <li>• Filter by capacity range</li>
                            <li>• Clear all filters with one click</li>
                        </ul>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900">Smart Ordering</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Shows own venues first for organisers</li>
                            <li>• Shows familiar venues first for artists</li>
                            <li>• Visual indicators for owned venues</li>
                            <li>• Owner information display</li>
                        </ul>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900">User Experience</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Responsive design for all devices</li>
                            <li>• Loading states and animations</li>
                            <li>• Pagination for large venue lists</li>
                            <li>• Keyboard navigation support</li>
                        </ul>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900">Integration</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Works with any form</li>
                            <li>• Custom event dispatching</li>
                            <li>• Alpine.js integration</li>
                            <li>• Tailwind CSS styling</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

