@props([
    'calendarByDate' => [],
    'rangeStart' => null,
    'rangeEnd' => null,
])

@php
    $rs = $rangeStart ?? now()->startOfMonth();
    $re = $rangeEnd ?? now()->addMonth()->endOfMonth();
    $eventsIndexUrl = route('events.index');
    $dayWithEventsActive = $siteBrand->calendarDayWithEventsActiveClass();
    $dayWithEvents = $siteBrand->calendarDayWithEventsClass();
    $dayEmptyToday = $siteBrand->calendarDayEmptyTodayClass();
    $dayEmpty = $siteBrand->calendarDayEmptyClass();
@endphp

<div
    class="{{ $siteBrand->calendarFrameClass() }}"
    x-data="homeEventCalendarData(@js($calendarByDate), '{{ $rs->format('Y-m-d') }}', '{{ $re->format('Y-m-d') }}', @js($eventsIndexUrl))"
>
    <div class="{{ $siteBrand->calendarInnerClass() }}">
        <div class="mb-4 flex items-center justify-between gap-3">
            <button
                type="button"
                class="{{ $siteBrand->calendarNavButtonClass() }}"
                @click="prevMonth()"
                :disabled="!canPrev"
                aria-label="Previous month"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <h2 class="{{ $siteBrand->calendarMonthTitleClass() }}" x-text="monthLabel"></h2>
            <button
                type="button"
                class="{{ $siteBrand->calendarNavButtonClass() }}"
                @click="nextMonth()"
                :disabled="!canNext"
                aria-label="Next month"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <div class="{{ $siteBrand->calendarWeekdayClass() }}">
            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
        </div>

        <div class="grid grid-cols-7 gap-1.5">
            <template x-for="(cell, idx) in gridCells" :key="idx">
                <div class="aspect-square min-h-[2.35rem] sm:min-h-[2.5rem]">
                    <div x-show="cell.placeholder" class="h-full w-full"></div>
                    <div x-show="!cell.placeholder" class="h-full w-full">
                        <a
                            x-show="cell.eventCount > 0"
                            :href="cell.listUrl"
                            class="flex h-full w-full flex-col items-center justify-center rounded-xl text-sm font-semibold transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md"
                            :class="cell.isToday ? '{{ $dayWithEventsActive }}' : '{{ $dayWithEvents }}'"
                        >
                            <span x-text="cell.day"></span>
                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-white/90" x-show="cell.isToday"></span>
                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full {{ $siteBrand->isRogues ? 'bg-sky-400' : 'bg-indigo-400' }}" x-show="!cell.isToday"></span>
                        </a>
                        <span
                            x-show="cell.eventCount === 0"
                            class="flex h-full w-full items-center justify-center rounded-xl text-sm font-medium transition-colors {{ $dayEmpty }}"
                            :class="cell.isToday ? '{{ $dayEmptyToday }}' : ''"
                            x-text="cell.day"
                        ></span>
                    </div>
                </div>
            </template>
        </div>

        <p class="{{ $siteBrand->calendarFooterClass() }}">
            <span class="{{ $siteBrand->calendarFooterHintClass() }}">Highlighted days</span> have gigs — tap to open the event list for that date.
        </p>
    </div>
</div>
