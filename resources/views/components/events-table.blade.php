@props(['events'])

<div class="{{ $siteBrand->tableShellClass() }}">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="{{ $siteBrand->tableHeadClass() }}">
                <tr>
                    <th class="{{ $siteBrand->tableHeadCellClass() }}">Event</th>
                    <th class="{{ $siteBrand->tableHeadCellClass() }}">Date & Time</th>
                    <th class="{{ $siteBrand->tableHeadCellClass() }}">Venue</th>
                    <th class="{{ $siteBrand->tableHeadCellClass() }}">Price</th>
                    <th class="{{ $siteBrand->tableHeadCellClass() }}">Category</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="{{ $siteBrand->tableBodyClass() }}">
                @foreach($events as $event)
                    @if($event)
                <tr class="{{ $siteBrand->tableRowClass() }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($event->poster)
                                <div class="flex-shrink-0 h-12 w-12">
                                    <img class="h-12 w-12 rounded-lg object-cover" src="{{ asset('storage/' . $event->poster) }}" alt="{{ $event->name }}">
                                </div>
                            @else
                                <div class="flex-shrink-0 h-12 w-12 bg-slate-800 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="ml-4">
                                <div class="{{ $siteBrand->tableCellPrimaryClass() }}">
                                    <a href="{{ route('events.show', $event) }}" class="{{ $siteBrand->isRogues ? 'hover:text-sky-400' : 'hover:text-indigo-400' }} transition-colors">
                                        {{ $event->name }}
                                    </a>
                                </div>
                                @if($event->description)
                                    <div class="{{ $siteBrand->tableCellSecondaryClass() }} truncate max-w-xs">
                                        {{ Str::limit(strip_tags($event->description), 60) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="{{ $siteBrand->tableCellPrimaryClass() }}">
                            {{ $event->date ? $event->date->format('M j, Y') : 'TBA' }}
                        </div>
                        <div class="{{ $siteBrand->tableCellSecondaryClass() }}">
                            {{ $event->date ? $event->date->format('g:i A') : '' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="{{ $siteBrand->tableCellPrimaryClass() }}">
                            @if($event->venue)
                                <a href="{{ route('venues.show', $event->venue) }}" class="{{ $siteBrand->isRogues ? 'hover:text-sky-400' : 'hover:text-indigo-400' }} transition-colors">
                                    {{ $event->venue->name }}
                                </a>
                            @else
                                <span class="text-slate-500">TBA</span>
                            @endif
                        </div>
                        @if($event->venue && $event->venue->city)
                            <div class="{{ $siteBrand->tableCellSecondaryClass() }}">{{ $event->venue->city }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="{{ $siteBrand->tableCellPrimaryClass() }}">
                            @if($event->price && $event->price > 0)
                                R{{ number_format($event->price, 2) }}
                            @else
                                <span class="text-green-400 font-medium">Free</span>
                            @endif
                        </div>
                        @if($event->capacity)
                            <div class="{{ $siteBrand->tableCellSecondaryClass() }}">{{ $event->capacity }} spots</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($event->category)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $siteBrand->isRogues ? 'bg-sky-500/20 text-sky-300' : 'bg-indigo-500/20 text-indigo-300' }}">
                                {{ ucfirst($event->category) }}
                            </span>
                        @else
                            <span class="text-slate-500 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('events.show', $event) }}" 
                               class="{{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }} transition-colors"
                               title="View Details">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            
                            @if($event->ticket_url)
                                <a href="{{ $event->ticket_url }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="text-blue-400 hover:text-blue-300 transition-colors"
                                   title="Get Tickets">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                </a>
                            @endif

                            @auth
                                @if($event && $event->id)
                                    <x-favorite-button 
                                        :model="$event" 
                                        :type="'event'" 
                                        size="sm"
                                    />
                                @endif
                            @endauth
                        </div>
                    </td>
                </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    
    @if($events->isEmpty())
        <div class="text-center py-12">
            <svg class="h-12 w-12 text-slate-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="{{ $siteBrand->emptyStateTitleClass() }}">No events found</h3>
            <p class="{{ $siteBrand->emptyStateTextClass() }}">Try adjusting your search criteria.</p>
        </div>
    @endif
</div>
