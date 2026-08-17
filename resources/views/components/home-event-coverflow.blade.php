@props([
    'heroEventCatalog',
    'heroDays' => 7,
    'categorySlug' => null,
    'categories',
])

@php
    $dayLabels = [1 => 'Today', 7 => 'Week', 30 => 'Month', 90 => '3 months'];
    $accentRing = $siteBrand->isRogues ? 'ring-sky-400/70' : 'ring-indigo-400/70';
    $accentBorder = $siteBrand->isRogues ? 'border-sky-400/40' : 'border-indigo-400/40';
@endphp

<div
    class="space-y-4 min-h-[28rem] sm:min-h-[30rem]"
    x-data="homeHeroGallery(@js($heroEventCatalog), @js($categorySlug ?? ''), @js($heroDays))"
>
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-2">
            @foreach($dayLabels as $days => $label)
                <button
                    type="button"
                    class="rounded-full px-3.5 py-1.5 text-sm font-semibold transition-colors"
                    :class="heroDays === {{ $days }} ? '{{ $siteBrand->homeFilterChipActiveClass() }}' : '{{ $siteBrand->homeFilterChipInactiveClass() }}'"
                    @click.prevent="setHeroDays({{ $days }})"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            @if($categories->isNotEmpty())
            <label for="home-category-filter" class="sr-only">Category</label>
            <select
                id="home-category-filter"
                class="{{ $siteBrand->formSelectClass() }} !py-2 !text-sm min-w-[10rem] max-w-full sm:max-w-[14rem]"
                @change="setCategory($event.target.value)"
            >
                <option value="" @selected(empty($categorySlug))>All genres</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected($categorySlug === $cat->slug)>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @endif
            <a
                href="{{ route('events.index', array_filter(['date_from' => now()->toDateString(), 'category' => $categorySlug])) }}"
                class="rounded-full px-3 py-1.5 text-sm font-medium whitespace-nowrap {{ $siteBrand->homeFilterChipInactiveClass() }}"
            >
                Full diary →
            </a>
        </div>
    </div>

    <div x-show="events.length === 0" x-cloak>
        <div class="{{ $siteBrand->listingCardShellClass() }} text-center py-10">
            <p class="text-slate-400" x-text="categorySlug ? 'No gigs in this window for that category — try a wider date range or another genre.' : 'No gigs in this window — try a wider date range or another genre.'"></p>
            <a href="{{ route('events.index') }}" class="btn-primary inline-flex mt-4 px-6 py-2 rounded-lg text-sm">
                Browse all events
            </a>
        </div>
    </div>

    <div x-show="events.length > 0" x-cloak class="{{ $siteBrand->coverflowFrameClass() }} overflow-hidden">
        <div class="relative px-1 py-5 sm:px-3">
            <button
                type="button"
                x-show="events.length > 1"
                x-cloak
                class="absolute left-0 top-1/2 z-40 -translate-y-1/2 rounded-full p-2.5 {{ $siteBrand->isRogues ? 'bg-slate-800/95 text-white hover:bg-slate-700' : 'bg-black/80 text-white hover:bg-black' }}"
                @click="prev()"
                aria-label="Previous gig"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button
                type="button"
                x-show="events.length > 1"
                x-cloak
                class="absolute right-0 top-1/2 z-40 -translate-y-1/2 rounded-full p-2.5 {{ $siteBrand->isRogues ? 'bg-slate-800/95 text-white hover:bg-slate-700' : 'bg-black/80 text-white hover:bg-black' }}"
                @click="next()"
                aria-label="Next gig"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>

            <div
                x-ref="track"
                @scroll.passive="onScroll()"
                class="flex items-center gap-2 sm:gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth px-[min(18vw,5rem)] py-3 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                style="perspective: 1000px;"
            >
                <template x-for="(event, index) in events" :key="event.url">
                    <a
                        :href="event.url"
                        x-bind:data-coverflow-index="index"
                        class="group relative shrink-0 snap-center transition-[transform,opacity,filter] duration-300 ease-out origin-center will-change-transform"
                        :class="cardClass(index)"
                        x-bind:style="{ width: cardWidth() }"
                    >
                        <div
                            class="aspect-[3/4] overflow-hidden rounded-xl border bg-black transition-[box-shadow] duration-300 {{ $accentBorder }}"
                            :class="frameClass(index) + (index === active ? ' {{ $accentRing }}' : '')"
                        >
                            <img
                                x-show="event.poster"
                                :src="event.poster"
                                :alt="event.name"
                                class="h-full w-full object-contain bg-black"
                                loading="lazy"
                            >
                            <div
                                x-show="!event.poster"
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br {{ $siteBrand->isRogues ? 'from-slate-800 to-sky-900' : 'from-[#12121A] to-indigo-950' }}"
                            >
                                <svg class="h-10 w-10 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                </template>
            </div>

            <div class="mt-2 min-h-[0.375rem]">
                <div x-show="events.length > 1 && events.length <= 15" x-cloak class="flex justify-center gap-1.5 flex-wrap max-w-md mx-auto">
                    <template x-for="(event, index) in events" :key="'dot-' + event.url">
                        <button
                            type="button"
                            class="h-1.5 rounded-full transition-all duration-200"
                            x-bind:class="active === index ? 'w-5 {{ $siteBrand->isRogues ? 'bg-sky-400' : 'bg-indigo-400' }}' : 'w-1.5 bg-white/25'"
                            @click="scrollTo(index)"
                            x-bind:aria-label="'Go to gig ' + (index + 1)"
                        ></button>
                    </template>
                </div>
                <p
                    x-show="events.length > 15"
                    x-cloak
                    class="text-center text-xs text-slate-500 min-h-[1rem]"
                    x-text="events.length + ' gigs — swipe or use arrows'"
                ></p>
            </div>
        </div>

        <div class="px-2 pt-1 pb-3 h-[6.5rem] sm:h-[6.75rem] text-center overflow-hidden">
            <a
                x-show="focused"
                x-cloak
                :href="focused?.url ?? '#'"
                class="inline-block max-w-lg mx-auto group h-full"
            >
                <p
                    class="text-base sm:text-lg font-semibold text-white line-clamp-2 min-h-[2.75rem] sm:min-h-[3rem] group-hover:underline"
                    x-text="focused?.name ?? ''"
                ></p>
                <p class="mt-0.5 text-sm text-slate-400 line-clamp-1" x-text="focused?.line ?? ''"></p>
                <p class="mt-1.5 text-xs font-medium {{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }} group-hover:underline">
                    View gig →
                </p>
            </a>
        </div>
    </div>
</div>
