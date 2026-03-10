@extends('layouts.app')

@section('title', $organiser->organisation_name . ' - Event Organiser - My Gig Guide')
@section('description', 'View ' . $organiser->organisation_name . ' profile and upcoming events.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Organiser Profile -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-8 mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center space-y-4 md:space-y-0 md:space-x-6">
                <div class="flex-shrink-0">
                    @if($organiser->user && $organiser->user->profile_photo)
                        <img src="{{ Storage::url($organiser->user->profile_photo) }}" 
                             alt="{{ $organiser->organisation_name }}" 
                             class="w-24 h-24 rounded-full object-cover">
                    @else
                        <div class="w-24 h-24 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="text-3xl font-bold text-purple-600">
                                {{ substr($organiser->organisation_name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $organiser->organisation_name }}</h1>
                    @if($organiser->user)
                        <p class="text-lg text-gray-600 mb-4">{{ $organiser->user->name }}</p>
                    @endif
                    @if($organiser->description)
                        <p class="text-gray-700 mb-4">{{ $organiser->description }}</p>
                    @endif
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center text-gray-600">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ $organiser->events_count ?? 0 }} events</span>
                        </div>
                        @if($organiser->average_rating)
                            <div class="flex items-center text-yellow-600">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.888c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span>{{ number_format($organiser->average_rating, 1) }}/5</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        @if($organiser->events->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Upcoming Events</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($organiser->events as $event)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="font-semibold text-gray-900">{{ $event->name }}</h3>
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    {{ $event->status }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">{{ $event->venue->name }}</p>
                            <p class="text-xs text-gray-500 mb-3">
                                {{ $event->date->format('M j, Y') }} at {{ $event->time ? $event->time->format('g:i A') : 'TBA' }}
                            </p>
                            @if($event->artists->count() > 0)
                                <p class="text-xs text-purple-600 mb-3">
                                    Artists: {{ $event->artists->pluck('stage_name')->implode(', ') }}
                                </p>
                            @endif
                            <div class="flex justify-end">
                                <a href="{{ route('events.show', $event->id) }}" 
                                   class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                    View Event →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-8 text-center">
                <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No upcoming events</h3>
                <p class="text-gray-500">This organiser doesn't have any upcoming events scheduled.</p>
            </div>
        @endif
    </div>
</div>
@endsection

