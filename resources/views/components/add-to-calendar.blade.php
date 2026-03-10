@php
// Combine date and time for the event
$startDateTime = null;
$endDateTime = null;

if ($event->date) {
    $startDateTime = $event->date->copy();
    
    // If time is set, combine date and time
    if ($event->time) {
        $startDateTime->setTime(
            (int) $event->time->format('H'),
            (int) $event->time->format('i'),
            (int) $event->time->format('s')
        );
    }
    
    // End time is 1 hour after start
    $endDateTime = $startDateTime->copy()->addHour();
} else {
    $startDateTime = now();
    $endDateTime = now()->addHour();
}

// Format dates for Google Calendar (YYYYMMDDTHHmmssZ format)
$googleStartDate = $startDateTime->utc()->format('Ymd\THis\Z');
$googleEndDate = $endDateTime->utc()->format('Ymd\THis\Z');

// Format dates for Outlook (ISO 8601 format)
$outlookStartDate = $startDateTime->toIso8601String();
$outlookEndDate = $endDateTime->toIso8601String();

// Event details for calendar
$title = urlencode($event->name);
$description = urlencode(strip_tags($event->description ?? ''));
$location = '';
if ($event->venue) {
    $location = $event->venue->name ?? $event->venue->address ?? '';
}
$location = urlencode($location);

// Google Calendar URL - adds to user's default calendar
$googleCalendarUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE" .
    "&text={$title}" .
    "&dates={$googleStartDate}/{$googleEndDate}" .
    "&details={$description}" .
    "&location={$location}";

// Outlook URL - opens compose dialog
$outlookUrl = "https://outlook.live.com/calendar/0/deeplink/compose?subject={$title}" .
    "&startdt={$outlookStartDate}" .
    "&enddt={$outlookEndDate}" .
    "&body={$description}" .
    "&location={$location}";

// Apple Calendar URL (macOS/iOS) - uses webcal protocol
$calendarRoute = route('events.calendar', $event->id);
$calendarUrl = str_replace(['http://', 'https://'], '', $calendarRoute);
$appleCalendarUrl = "webcal://{$calendarUrl}";

// ICS download URL - works with any calendar app
$icsUrl = route('events.calendar', $event->id);
@endphp

@props(['event'])

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Add to Calendar</h3>
    
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <!-- Google Calendar -->
        <a href="{{ $googleCalendarUrl }}" 
           target="_blank" 
           rel="noopener noreferrer"
           class="inline-flex items-center justify-center px-3 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors text-sm">
            <svg class="h-5 w-5 mr-1" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
            </svg>
            Google
        </a>

        <!-- Outlook -->
        <a href="{{ $outlookUrl }}" 
           target="_blank" 
           rel="noopener noreferrer"
           class="inline-flex items-center justify-center px-3 py-3 bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700 transition-colors text-sm">
            <svg class="h-5 w-5 mr-1" fill="currentColor" viewBox="0 0 24 24">
                <path d="M7.5 3h9A1.5 1.5 0 0 1 18 4.5v15A1.5 1.5 0 0 1 16.5 21h-9A1.5 1.5 0 0 1 6 19.5v-15A1.5 1.5 0 0 1 7.5 3zM18 19.5V7.5H6v12h12zM8 10h8v2H8v-2zm0 3h8v2H8v-2z"/>
            </svg>
            Outlook
        </a>

        <!-- Apple Calendar -->
        <a href="{{ $appleCalendarUrl }}" 
           class="inline-flex items-center justify-center px-3 py-3 bg-gray-800 text-white rounded-lg font-medium hover:bg-gray-900 transition-colors text-sm">
            <svg class="h-5 w-5 mr-1" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.5 3H6c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM6 5h12v2H6V5zm12 14H6V9h12v10zm-4-7h4v2h-4v-2zm0 3h4v2h-4v-2z"/>
            </svg>
            Apple
        </a>

        <!-- Download ICS -->
        <a href="{{ $icsUrl }}" 
           download="{{ $event->name }}.ics"
           class="inline-flex items-center justify-center px-3 py-3 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors text-sm">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download
        </a>
    </div>
    
    <p class="text-xs text-gray-500 mt-3 text-center">
        Click any button to add this event to your calendar app
    </p>
</div>
