@props([
    'calendarByDate' => [],
    'rangeStart' => null,
    'rangeEnd' => null,
])

@php
    $rs = $rangeStart ?? now()->startOfMonth();
    $re = $rangeEnd ?? now()->addMonth()->endOfMonth();
    $eventsIndexUrl = route('events.index');
@endphp

<div
    class="rounded-3xl bg-gradient-to-br from-purple-100/80 via-white to-sky-50/70 p-[1px] shadow-md shadow-purple-900/5"
    x-data="homeEventCalendarData(@js($calendarByDate), '{{ $rs->format('Y-m-d') }}', '{{ $re->format('Y-m-d') }}', @js($eventsIndexUrl))"
>
    <div class="rounded-[1.35rem] bg-white/95 backdrop-blur-sm p-5 text-left ring-1 ring-purple-100/80">
        <div class="mb-4 flex items-center justify-between gap-3">
            <button
                type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gray-200/90 bg-white text-gray-600 shadow-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-35"
                @click="prevMonth()"
                :disabled="!canPrev"
                aria-label="Previous month"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <h2 class="min-w-0 flex-1 text-center text-base font-bold tracking-tight text-gray-900 sm:text-lg" x-text="monthLabel"></h2>
            <button
                type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gray-200/90 bg-white text-gray-600 shadow-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-35"
                @click="nextMonth()"
                :disabled="!canNext"
                aria-label="Next month"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <div class="mb-2 grid grid-cols-7 gap-1 text-center text-[10px] font-bold uppercase tracking-wider text-gray-400 sm:text-xs">
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
                            :class="cell.isToday ? 'bg-gradient-to-br from-purple-600 to-indigo-600 text-white shadow-md ring-2 ring-purple-300/70' : 'bg-gradient-to-b from-purple-50 to-white text-purple-900 ring-1 ring-purple-200/90 hover:from-purple-100'"
                        >
                            <span x-text="cell.day"></span>
                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-purple-500" :class="{ '!bg-white': cell.isToday }"></span>
                        </a>
                        <span
                            x-show="cell.eventCount === 0"
                            class="flex h-full w-full items-center justify-center rounded-xl text-sm font-medium text-gray-600 transition-colors"
                            :class="cell.isToday ? 'bg-slate-100 font-bold text-slate-900 ring-2 ring-purple-200' : 'hover:bg-gray-50'"
                            x-text="cell.day"
                        ></span>
                    </div>
                </div>
            </template>
        </div>

        <p class="mt-4 border-t border-gray-100 pt-3 text-center text-xs leading-relaxed text-gray-500">
            Purple days have gigs — tap to open the event list for that date.
        </p>
    </div>
</div>
