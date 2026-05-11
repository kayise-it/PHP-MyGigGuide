<nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo (with notification dot when user has unread notifications) -->
            <div class="flex items-center relative">
                <a href="{{ route('home') }}" class="flex items-center space-x-3">
                    <span class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 text-gray-400" title="Tenant logo (white-label)">
                        {{-- Optional: <img src="..." class="h-10 w-10 object-contain rounded-lg" alt=""> --}}
                        @auth
                            @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 flex h-3 w-3" title="You have {{ $unreadNotificationCount }} unread notification(s)">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                </span>
                            @endif
                        @endauth
                    </span>
                    <span class="text-xl font-bold text-gray-900 whitespace-nowrap">My Gig Guide</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Home</a>
                <a href="{{ route('events.index') }}" class="nav-link {{ request()->routeIs('events.*') ? 'nav-link-active' : '' }}">Events</a>
                <a href="{{ route('artists.index') }}" class="nav-link {{ request()->routeIs('artists.*') ? 'nav-link-active' : '' }}">Artists</a>
                <a href="{{ route('venues.index') }}" class="nav-link {{ request()->routeIs('venues.*') ? 'nav-link-active' : '' }}">Venues</a>
                <a href="{{ url('/about') }}" class="nav-link {{ request()->is('about') ? 'nav-link-active' : '' }}">About</a>
                <a href="{{ route('contact.index') }}" class="nav-link {{ request()->routeIs('contact.*') ? 'nav-link-active' : '' }}">Contact</a>
                
                @auth
                    @if(auth()->user()->hasRole(['admin', 'superuser']))
                        <!-- Admin Navigation -->
                        <div class="border-l border-gray-300 h-6 mx-2"></div>
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'nav-link-active' : '' }}">Admin Panel</a>
                    @endif
                @endauth
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- Notifications (bell with dot when unread) -->
                    <div class="relative" x-data="{ notificationOpen: false }" @click.outside="notificationOpen = false">
                        <button type="button" @click="notificationOpen = !notificationOpen" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors" aria-label="Notifications">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                                <span class="absolute top-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
                            @endif
                        </button>
                        <div x-show="notificationOpen"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             x-cloak
                             class="absolute right-0 mt-2 w-80 max-h-96 overflow-hidden bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                            </div>
                            <div class="max-h-72 overflow-y-auto">
                                @if(isset($navbarNotifications) && $navbarNotifications->isNotEmpty())
                                    @foreach($navbarNotifications as $notification)
                                        @php $data = is_array($notification->data) ? $notification->data : []; @endphp
                                        <a href="{{ route('notifications.read', $notification->id) }}" class="block px-4 py-3 hover:bg-purple-50 border-b border-gray-100 last:border-b-0 transition-colors">
                                            <p class="text-sm text-gray-800">{{ $data['message'] ?? 'New notification' }}</p>
                                            @if(!empty($data['requested_at']))
                                                <p class="text-xs text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($data['requested_at'])->diffForHumans() }}</p>
                                            @endif
                                        </a>
                                    @endforeach
                                @else
                                    <div class="px-4 py-6 text-center text-sm text-gray-500">No new notifications</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center space-x-3 text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 rounded-lg px-3 py-2 transition-colors duration-200">
                            <div class="relative">
                                @php
                                    $userProfileImage = null;
                                    if (auth()->user()->profile_picture && !str_contains(auth()->user()->profile_picture, '/tmp/php') && !str_contains(auth()->user()->profile_picture, 'tmp.php')) {
                                        $userProfileImage = Storage::url(auth()->user()->profile_picture);
                                    }
                                @endphp
                                
                                @if($userProfileImage)
                                    <img src="{{ $userProfileImage }}" alt="{{ auth()->user()->name }}" class="h-8 w-8 rounded-full object-cover shadow-sm">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-r from-purple-500 to-blue-500 flex items-center justify-center shadow-sm">
                                        <span class="text-white text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                @if(auth()->user()->hasRole(['admin', 'superuser']))
                                    <div class="absolute -top-1 -right-1 h-4 w-4 bg-yellow-400 rounded-full flex items-center justify-center">
                                        <svg class="h-2.5 w-2.5 text-yellow-800" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="hidden md:block text-sm font-medium">{{ auth()->user()->name }}</span>
                                @if(auth()->user()->hasRole(['admin', 'superuser']))
                                    <span class="hidden md:block text-xs text-yellow-600 font-medium">Admin</span>
                                @endif
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-50 border border-gray-200">
                            @if(auth()->user()->hasRole(['admin', 'superuser']))
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Admin Dashboard
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="dropdown-item">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                    </svg>
                                    Dashboard
                                </a>
                            @endif
                            <a href="{{ route('profile.show') }}" class="dropdown-item">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="dropdown-item w-full text-left text-red-600 hover:text-red-700 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary">Login</a>
                    <a href="{{ route('register') }}" class="btn-primary">Sign Up</a>
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center ml-4">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 rounded-lg p-2 transition-colors duration-200">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div x-show="mobileMenuOpen" class="md:hidden bg-white border-t border-gray-200">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ route('home') }}" class="mobile-nav-link {{ request()->routeIs('home') ? 'mobile-nav-link-active' : '' }}">Home</a>
            <a href="{{ route('events.index') }}" class="mobile-nav-link {{ request()->routeIs('events.*') ? 'mobile-nav-link-active' : '' }}">Events</a>
            <a href="{{ route('artists.index') }}" class="mobile-nav-link {{ request()->routeIs('artists.*') ? 'mobile-nav-link-active' : '' }}">Artists</a>
            <a href="{{ url('/about') }}" class="mobile-nav-link {{ request()->is('about') ? 'mobile-nav-link-active' : '' }}">About</a>
            <a href="{{ route('venues.index') }}" class="mobile-nav-link {{ request()->routeIs('venues.*') ? 'mobile-nav-link-active' : '' }}">Venues</a>
            <a href="{{ route('organisers.index') }}" class="mobile-nav-link {{ request()->routeIs('organisers.*') ? 'mobile-nav-link-active' : '' }}">Organisers</a>
            <a href="{{ route('contact.index') }}" class="mobile-nav-link {{ request()->routeIs('contact.*') ? 'mobile-nav-link-active' : '' }}">Contact</a>
        </div>
    </div>
</nav>
