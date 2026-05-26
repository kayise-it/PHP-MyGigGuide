<nav class="{{ $siteBrand->navBarClass() }} sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center relative">
                <a href="{{ route('home') }}" class="flex items-center space-x-3">
                    <span class="relative inline-flex h-10 shrink-0 items-center justify-center rounded-lg overflow-hidden {{ $siteBrand->navLogoWrapClass() }}">
                        <img src="{{ $siteBrand->logoUrl() }}" alt="{{ $siteBrand->name }}" class="h-10 w-auto max-w-[140px] object-contain px-1">
                        @auth
                            @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 flex h-3 w-3" title="You have {{ $unreadNotificationCount }} unread notification(s)">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                </span>
                            @endif
                        @endauth
                    </span>
                    <span class="{{ $siteBrand->navTitleClass() }}">{{ $siteBrand->name }}</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ route('home') }}" class="{{ $siteBrand->navLinkClass(request()->routeIs('home')) }}">Home</a>
                <a href="{{ route('events.index') }}" class="{{ $siteBrand->navLinkClass(request()->routeIs('events.*')) }}">Events</a>
                <a href="{{ route('artists.index') }}" class="{{ $siteBrand->navLinkClass(request()->routeIs('artists.*')) }}">Artists</a>
                <a href="{{ route('venues.index') }}" class="{{ $siteBrand->navLinkClass(request()->routeIs('venues.*')) }}">Venues</a>
                <a href="{{ url('/about') }}" class="{{ $siteBrand->navLinkClass(request()->is('about')) }}">About</a>
                <a href="{{ route('contact.index') }}" class="{{ $siteBrand->navLinkClass(request()->routeIs('contact.*')) }}">Contact</a>
                
                @auth
                    @if(auth()->user()->hasRole(['admin', 'superuser']))
                        <div class="border-l {{ $siteBrand->navDividerClass() }} h-6 mx-2"></div>
                        <a href="{{ route('admin.dashboard') }}" class="{{ $siteBrand->navLinkClass(request()->routeIs('admin.*')) }}">Admin Panel</a>
                    @endif
                @endauth
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- Notifications (bell with dot when unread) -->
                    <div class="relative" x-data="{ notificationOpen: false }" @click.outside="notificationOpen = false">
                        <button type="button" @click="notificationOpen = !notificationOpen" class="relative p-2 {{ $siteBrand->navIconButtonClass() }} rounded-lg focus:outline-none focus:ring-2 {{ $siteBrand->focusRingClass() }} focus:ring-offset-2 transition-colors" aria-label="Notifications">
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
                             class="{{ $siteBrand->dropdownPanelClass() }}">
                            <div class="{{ $siteBrand->dropdownHeaderClass() }}">
                                <h3 class="{{ $siteBrand->dropdownTitleClass() }}">Notifications</h3>
                            </div>
                            <div class="max-h-72 overflow-y-auto">
                                @if(isset($navbarNotifications) && $navbarNotifications->isNotEmpty())
                                    @foreach($navbarNotifications as $notification)
                                        @php $data = is_array($notification->data) ? $notification->data : []; @endphp
                                        <a href="{{ route('notifications.read', $notification->id) }}" class="{{ $siteBrand->dropdownItemClass() }}">
                                            <p class="text-sm text-slate-200">{{ $data['message'] ?? 'New notification' }}</p>
                                            @if(!empty($data['requested_at']))
                                                <p class="text-xs text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($data['requested_at'])->diffForHumans() }}</p>
                                            @endif
                                        </a>
                                    @endforeach
                                @else
                                    <div class="px-4 py-6 text-center text-sm text-slate-500">No new notifications</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center space-x-3 {{ $siteBrand->navUserButtonClass() }} focus:outline-none focus:ring-2 {{ $siteBrand->focusRingClass() }} focus:ring-offset-2 rounded-lg px-3 py-2 transition-colors duration-200">
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
                             class="{{ $siteBrand->dropdownMenuClass() }}">
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
                            <div class="border-t border-slate-700 my-1"></div>
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
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="{{ $siteBrand->navIconButtonClass() }} focus:outline-none focus:ring-2 {{ $siteBrand->focusRingClass() }} focus:ring-offset-2 rounded-lg p-2 transition-colors duration-200">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div x-show="mobileMenuOpen" class="{{ $siteBrand->mobileNavPanelClass() }}">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ route('home') }}" class="{{ $siteBrand->mobileNavLinkClass(request()->routeIs('home')) }}">Home</a>
            <a href="{{ route('events.index') }}" class="{{ $siteBrand->mobileNavLinkClass(request()->routeIs('events.*')) }}">Events</a>
            <a href="{{ route('artists.index') }}" class="{{ $siteBrand->mobileNavLinkClass(request()->routeIs('artists.*')) }}">Artists</a>
            <a href="{{ url('/about') }}" class="{{ $siteBrand->mobileNavLinkClass(request()->is('about')) }}">About</a>
            <a href="{{ route('venues.index') }}" class="{{ $siteBrand->mobileNavLinkClass(request()->routeIs('venues.*')) }}">Venues</a>
            <a href="{{ route('organisers.index') }}" class="{{ $siteBrand->mobileNavLinkClass(request()->routeIs('organisers.*')) }}">Organisers</a>
            <a href="{{ route('contact.index') }}" class="{{ $siteBrand->mobileNavLinkClass(request()->routeIs('contact.*')) }}">Contact</a>
        </div>
    </div>
</nav>
