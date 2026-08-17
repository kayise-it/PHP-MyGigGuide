<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Dashboard - My Gig Guide')</title>
    <meta name="description" content="@yield('description', 'Management portal for My Gig Guide administrators.')">
    <link rel="icon" href="{{ asset('logos/favicon.png') }}" type="image/png" sizes="32x32">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('head')
    <!-- Minimal fallback styles in case Vite assets are not built -->
    <style>
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .9rem;border-radius:.5rem;border:1px solid rgba(255,255,255,.12);background:#1e293b;color:#e2e8f0;font-weight:500}
        .btn:hover{background:#334155}
        .btn-primary{background:#6366f1;color:#fff;border-color:#6366f1}
        .btn-primary:hover{background:#4f46e5;border-color:#4f46e5}
        .btn-secondary{background:#1e293b;color:#e2e8f0;border-color:rgba(255,255,255,.12)}
        .btn-secondary:hover{background:#334155;border-color:rgba(255,255,255,.18)}
        .btn-danger{background:#dc2626;color:#fff;border-color:#dc2626}
        .btn-danger:hover{background:#b91c1c;border-color:#b91c1c}
        .btn-sm{padding:.25rem .6rem;font-size:.875rem}
        .input{width:100%;padding:.5rem .75rem;border:1px solid rgba(255,255,255,.15);border-radius:.5rem;background:#0a0a10;color:#e2e8f0}
        .alert{padding:.5rem .75rem;border-radius:.5rem}
        .alert-success{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.35);color:#86efac}
        .alert-danger{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.35);color:#fca5a5}
        .nav-item{display:flex;align-items:center;gap:.6rem;padding:.6rem .75rem;border-radius:.5rem;color:#cbd5e1}
        .nav-item:hover{background:rgba(99,102,241,.1);color:#a5b4fc}
        .nav-item-active{background:rgba(99,102,241,.15);color:#a5b4fc}
        table{width:100%;border-collapse:separate;border-spacing:0}
        thead th{font-size:.875rem;color:#94a3b8;border-bottom:1px solid rgba(255,255,255,.1)}
        tbody td{font-size:.9375rem;color:#e2e8f0}
        tr.border-t td{border-top:1px solid rgba(255,255,255,.1)}
    </style>
</head>
<body class="font-sans antialiased admin-shell brand-mygigguide bg-[#0a0a10] text-slate-100">
    <div id="app" x-data="{ sidebarOpen: false }">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-[#12121A] border-r border-white/10 shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0" 
             :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 px-4 bg-gradient-to-r from-purple-600 to-blue-600">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('logos/mgg-headphones-logo.png') }}" alt="My Gig Guide" class="h-8 w-auto rounded-lg">
                    <span class="text-white font-bold text-lg">Admin Panel</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="mt-8 px-4">
                <div class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="nav-item {{ request()->routeIs('admin.dashboard') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.users.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.users.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        Users
                    </a>

                    <a href="{{ route('admin.events.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.events.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Events
                    </a>

                    <a href="{{ route('admin.venues.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.venues.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Venues
                    </a>

                    <a href="{{ route('admin.artists.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.artists.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                        Artists
                    </a>

                    <a href="{{ route('admin.organisers.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.organisers.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Organisers
                    </a>

                    @if(\App\Models\SiteSetting::isPaidFeaturesEnabled())
                        <a href="{{ route('admin.paid-features.index') }}" 
                           class="nav-item {{ request()->routeIs('admin.paid-features.*') ? 'nav-item-active' : '' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0-6a9 9 0 100 18 9 9 0 000-18z" />
                            </svg>
                            Paid Features
                        </a>
                    @endif
                    <!-- Add genre management -->
                    <a href="{{ route('admin.genres.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.genres.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9 2.5 2.5 0 000-5z" />
                        </svg>
                        Genres
                    </a>
                    <!-- Categories Management -->
                    <a href="{{ route('admin.categories.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.categories.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Categories
                    </a>
                    <!-- Unclaimed Items -->
                    @php
                        $unclaimedCount = \App\Models\Artist::notOfficiallyOwned()->count()
                            + \App\Models\Venue::notOfficiallyOwned()->count()
                            + \App\Models\Event::whereNull('owner_id')->count()
                            + \App\Models\Organiser::notOfficiallyOwned()->count();
                    @endphp
                    <a href="{{ route('admin.unclaimed.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.unclaimed.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Unclaimed
                        @if($unclaimedCount > 0)
                            <span class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">{{ $unclaimedCount }}</span>
                        @endif
                    </a>
                    <!-- Artist Claim Disputes -->
                    @php
                        $disputeCount = \App\Models\Artist::where(function($q) {
                            $q->where('claim_status', 'pending')
                              ->orWhere('claim_status', 'disputed')
                              ->orWhere('dispute_raised', true);
                        })->whereNotNull('pending_claim_user_id')->count();
                    @endphp
                    <a href="{{ route('admin.artist-disputes.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.artist-disputes.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Disputes
                        @if($disputeCount > 0)
                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $disputeCount }}</span>
                        @endif
                    </a>

                    @php
                        try {
                            $contentReportCount = \App\Models\ContentReport::where('status', 'new')->count();
                        } catch (\Throwable $e) {
                            $contentReportCount = 0;
                        }
                    @endphp
                    <a href="{{ route('admin.content-reports.index') }}"
                       class="nav-item {{ request()->routeIs('admin.content-reports.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                        Content reports
                        @if($contentReportCount > 0)
                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">{{ $contentReportCount }}</span>
                        @endif
                    </a>

                    <!-- Duplicate Names -->
                    @php
                        $duplicateService = app(\App\Services\DuplicateNameService::class);
                        $duplicateSummary = $duplicateService->getDuplicateSummary();
                        $totalDuplicateGroups = collect($duplicateSummary)->sum('duplicate_groups');
                    @endphp
                    <a href="{{ route('admin.duplicates.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.duplicates.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Duplicates
                        @if($totalDuplicateGroups > 0)
                            <span class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">{{ $totalDuplicateGroups }}</span>
                        @endif
                    </a>

                    <!-- Station Polls -->
                    <a href="{{ route('admin.polls.index') }}"
                       class="nav-item {{ request()->routeIs('admin.polls.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Polls
                    </a>

                    <!-- Divider -->
                    <div class="border-t border-white/10 my-4"></div>

                    <!-- Settings -->
                    <a href="{{ route('admin.settings.index') }}" 
                       class="nav-item {{ request()->routeIs('admin.settings.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </a>

                    <a href="{{ route('admin.email-templates.index') }}"
                       class="nav-item {{ request()->routeIs('admin.email-templates.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m5 8H6a2 2 0 01-2-2V6a2 2 0 012-2h8l6 6v8a2 2 0 01-2 2z" />
                        </svg>
                        Email Templates
                    </a>
                    <!-- Mail Accounts -->
                    <a href="{{ route('admin.mail-accounts.index') }}"
                       class="nav-item {{ request()->routeIs('admin.mail-accounts.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Mail Accounts
                    </a>

                    <a href="{{ route('admin.capabilities.index') }}"
                       class="nav-item {{ request()->routeIs('admin.capabilities.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6" />
                        </svg>
                        Capabilities
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="lg:pl-64">
            <!-- Top Bar -->
            <div class="sticky top-0 z-40 bg-[#12121A]/95 backdrop-blur shadow-sm border-b border-white/10">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-md text-slate-400 hover:text-white hover:bg-white/5">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <div class="flex-1">
                        <h1 class="text-xl font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="flex items-center space-x-3 text-slate-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-[#12121A] rounded-lg px-3 py-2 transition-colors duration-200">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-purple-500 to-blue-500 flex items-center justify-center shadow-sm">
                                    <span class="text-white text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                                <span class="hidden md:block text-sm font-medium">{{ auth()->user()->name }}</span>
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
                                 class="absolute right-0 mt-2 w-56 bg-[#12121A] rounded-lg shadow-xl py-2 z-50 border border-white/10">
                                <a href="{{ route('home') }}" class="dropdown-item">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    View Site
                                </a>
                                <div class="border-t border-white/10 my-1"></div>
                                <form method="POST" action="{{ route('admin.logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="dropdown-item w-full text-left text-red-400 hover:text-red-300 hover:bg-red-500/10">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 alert-success" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition
                         x-init="setTimeout(() => show = false, 5000)">
                        <div class="flex">
                            <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="ml-3 flex-1">
                                <p class="text-sm text-green-800">{{ session('success') }}</p>
                            </div>
                            <button @click="show = false" class="ml-4 text-green-400 hover:text-green-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition
                         x-init="setTimeout(() => show = false, 7000)">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="ml-3 flex-1">
                                <p class="text-sm text-red-800">{{ session('error') }}</p>
                            </div>
                            <button @click="show = false" class="ml-4 text-red-400 hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        <!-- Global admin modal -->
        <div id="admin-modal-backdrop"
             class="fixed inset-0 z-40 hidden transition-opacity bg-gray-900/20"
             style="backdrop-filter: blur(8px);"></div>
        <div id="admin-modal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
            <div class="bg-[#12121A] rounded-2xl shadow-xl max-w-4xl w-full relative overflow-hidden border border-white/10">
                <button type="button"
                        id="admin-modal-close"
                        class="absolute top-3 right-3 inline-flex items-center justify-center rounded-full p-1.5 text-slate-400 hover:text-white hover:bg-white/5 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div id="admin-modal-body" class="max-h-[80vh] overflow-y-auto p-6">
                    <!-- Content injected dynamically -->
                    <div class="flex items-center justify-center py-12 text-slate-400 text-sm">
                        Loading...
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile sidebar overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/70 lg:hidden"
             @click="sidebarOpen = false">
        </div>
    </div>

    <script>
        /**
         * Global Admin Modal helper.
         * Usage:
         * - AdminModal.openFromElement('#selector')
         * - AdminModal.openWithHtml('<p>Content</p>')
         * - AdminModal.openFromUrl('/admin/users/1')
         * - AdminModal.close()
         */
        window.AdminModal = (function () {
            const modal = document.getElementById('admin-modal');
            const backdrop = document.getElementById('admin-modal-backdrop');
            const body = document.getElementById('admin-modal-body');
            const closeBtn = document.getElementById('admin-modal-close');

            if (!modal || !backdrop || !body || !closeBtn) {
                return {
                    openWithHtml: function () {},
                    openFromElement: function () {},
                    openFromUrl: function () {},
                    close: function () {},
                };
            }

            function open() {
                modal.classList.remove('hidden');
                backdrop.classList.remove('hidden');
            }

            function close() {
                modal.classList.add('hidden');
                backdrop.classList.add('hidden');
            }

            closeBtn.addEventListener('click', close);
            backdrop.addEventListener('click', close);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    close();
                }
            });

            function setBodyHtml(html) {
                body.innerHTML = html;
            }

            function openWithHtml(html) {
                setBodyHtml(html);
                open();
            }

            function openFromElement(selector) {
                const el = document.querySelector(selector);
                if (!el) return;
                setBodyHtml(el.innerHTML);
                open();
            }

            function openFromUrl(url, options = {}) {
                const loadingText = options.loadingText || 'Loading...';
                setBodyHtml(
                    '<div class="flex items-center justify-center py-12 text-gray-400 text-sm">' +
                        loadingText +
                        '</div>'
                );
                open();

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                })
                    .then((r) => r.text())
                    .then((html) => {
                        setBodyHtml(html);
                    })
                    .catch(() => {
                        setBodyHtml(
                            '<div class="flex items-center justify-center py-12 text-red-500 text-sm">Failed to load content.</div>'
                        );
                    });
            }

            return {
                openWithHtml,
                openFromElement,
                openFromUrl,
                close,
            };
        })();
    </script>

    @stack('scripts')
</body>
</html>
