<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found • My Gig Guide</title>
    <meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 antialiased flex items-center justify-center p-6">
    <div class="w-full max-w-6xl">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-10 text-center">
            <div class="mx-auto mb-6 w-16 h-16 rounded-2xl flex items-center justify-center bg-gradient-to-r from-purple-600 to-blue-600 text-white">
                <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-7 4h8M7 7h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-sm font-semibold tracking-wide text-purple-600 mb-2">Error 404</p>
            <h1 class="text-3xl md:text-5xl font-bold text-gray-900 mb-4">We can't find that page</h1>
            <p class="text-gray-600 max-w-2xl mx-auto mb-8">The page you’re looking for may have been moved or no longer exists. Try going back to the homepage or browse our latest events and artists.</p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/" class="px-6 py-3 rounded-xl font-medium bg-gradient-to-r from-purple-600 to-blue-600 text-white hover:from-purple-700 hover:to-blue-700 transition">Go to Homepage</a>
                <a href="/events" class="px-6 py-3 rounded-xl font-medium bg-white border border-purple-200 text-purple-700 hover:bg-purple-50 transition">Browse Events</a>
            </div>

            <div class="mt-10">
                <form action="/events" method="GET" class="max-w-xl mx-auto">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Search events, artists, venues..." class="w-full pl-12 pr-4 py-3 rounded-xl border border-purple-200 placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white/80">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-12">
            <h2 class="text-center text-2xl font-bold text-gray-900 mb-6">Recent events you might like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    try {
                        $recentEvents = \App\Models\Event::with('venue')
                            ->whereDate('date', '>=', now()->subMonths(1))
                            ->orderBy('date')
                            ->limit(6)
                            ->get();
                    } catch (\Throwable $e) {
                        $recentEvents = collect();
                    }
                @endphp

                @forelse($recentEvents as $event)
                    <a href="/events/{{ $event->id }}" class="block bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="font-semibold text-gray-900 line-clamp-2 pr-3">{{ $event->name }}</h3>
                            <span class="text-xs px-2 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200">{{ optional($event->date)->format('M d') }}</span>
                        </div>
                        <p class="text-sm text-gray-600 line-clamp-2">{{ optional($event->venue)->name }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-sm font-medium text-purple-700">R{{ $event->price ?? 0 }}</span>
                            <span class="text-sm text-gray-500">{{ optional($event->time)->format('H:i') ?? '—' }}</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center text-gray-500">No events to show right now.</div>
                @endforelse
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-10">&copy; <span id="yr"></span> My Gig Guide</p>
    </div>

    <script>
        document.getElementById('yr').textContent = new Date().getFullYear();
    </script>
</body>
</html>


