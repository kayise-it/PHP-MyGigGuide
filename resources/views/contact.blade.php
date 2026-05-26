@extends('layouts.app')

@section('title', 'Contact Us - My Gig Guide')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="{{ $siteBrand->pageTitleClass() }} mb-4">Contact Us</h1>
            <p class="{{ $siteBrand->subtitleClass() }} max-w-2xl mx-auto mb-0">
                Get in touch with our team. We're here to help with any questions about events, venues, artists, or platform support.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-8 bg-green-500/10 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div class="space-y-8">
                <div class="{{ $siteBrand->detailPanelClass() }}">
                    <h2 class="{{ $siteBrand->detailSubheadingClass() }}">Get In Touch</h2>
                    
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-white mb-4">Contact Owner</h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 {{ $siteBrand->isRogues ? 'bg-sky-500/20' : 'bg-indigo-500/20' }} rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 {{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium text-white">Mr Dave Welmans</p>
                                    <p class="{{ $siteBrand->detailMutedTextClass() }}">Owner, My Gig Guide</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4 pl-4">
                                <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <a href="mailto:dave@mygigguide.co.za" class="{{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }} transition-colors">
                                    dave@mygigguide.co.za
                                </a>
                            </div>
                            
                            <div class="flex items-center space-x-4 pl-4">
                                <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <a href="tel:+27746609752" class="{{ $siteBrand->isRogues ? 'text-sky-400 hover:text-sky-300' : 'text-indigo-400 hover:text-indigo-300' }} transition-colors">
                                    +27 74 660 9752
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="{{ $siteBrand->isRogues ? 'bg-slate-800/80' : 'bg-black/40' }} rounded-lg p-4">
                        <h4 class="font-semibold text-white mb-2">Support Hours</h4>
                        <div class="text-sm {{ $siteBrand->detailMutedTextClass() }} space-y-1">
                            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                            <p>Saturday: 9:00 AM - 2:00 PM</p>
                            <p>Sunday: Closed</p>
                        </div>
                    </div>
                </div>

                <div class="{{ $siteBrand->detailPanelClass() }}">
                    <h3 class="text-lg font-semibold text-white mb-4">Quick Help</h3>
                    <div class="space-y-4">
                        <a href="{{ route('events.index') }}" class="flex items-center space-x-3 {{ $siteBrand->detailMutedTextClass() }} {{ $siteBrand->isRogues ? 'hover:text-sky-400' : 'hover:text-indigo-400' }} transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Browse Events</span>
                        </a>
                        <a href="{{ route('artists.index') }}" class="flex items-center space-x-3 {{ $siteBrand->detailMutedTextClass() }} {{ $siteBrand->isRogues ? 'hover:text-sky-400' : 'hover:text-indigo-400' }} transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Discover Artists</span>
                        </a>
                        <a href="{{ route('venues.index') }}" class="flex items-center space-x-3 {{ $siteBrand->detailMutedTextClass() }} {{ $siteBrand->isRogues ? 'hover:text-sky-400' : 'hover:text-indigo-400' }} transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span>Find Venues</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="{{ $siteBrand->detailPanelClass() }}">
                <h2 class="{{ $siteBrand->detailSubheadingClass() }}">Send us a Message</h2>
                
                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="{{ $siteBrand->formLabelClass() }}">Name</label>
                            <input type="text" id="name" name="name" required class="{{ $siteBrand->formInputClass() }}"
                                value="{{ old('name') ?: (auth()->check() ? auth()->user()->name : '') }}">
                            @error('name')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="email" class="{{ $siteBrand->formLabelClass() }}">Email</label>
                            <input type="email" id="email" name="email" required class="{{ $siteBrand->formInputClass() }}"
                                value="{{ old('email') ?: (auth()->check() ? auth()->user()->email : '') }}">
                            @error('email')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label for="subject" class="{{ $siteBrand->formLabelClass() }}">Subject</label>
                        <input type="text" id="subject" name="subject" required class="{{ $siteBrand->formInputClass() }}"
                            value="{{ old('subject') }}" placeholder="e.g. Event inquiry, Support request, etc.">
                        @error('subject')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="message" class="{{ $siteBrand->formLabelClass() }}">Message</label>
                        <textarea id="message" name="message" required rows="6" class="{{ $siteBrand->formInputClass() }} resize-vertical"
                            placeholder="Tell us how we can help...">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="newsletter" name="newsletter"
                            class="h-4 w-4 {{ $siteBrand->isRogues ? 'text-sky-400 focus:ring-sky-400' : 'text-indigo-500 focus:ring-indigo-500' }} border-slate-600 rounded bg-slate-950">
                        <label for="newsletter" class="ml-2 text-sm {{ $siteBrand->detailMutedTextClass() }}">
                            Subscribe to our newsletter for event updates
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-primary w-full py-3 px-6 rounded-lg font-semibold">
                        Send Message
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-16 {{ $siteBrand->detailPanelClass() }}">
            <h2 class="{{ $siteBrand->detailSubheadingClass() }} text-center">Why Contact Us?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach([
                  ['title' => 'Quick Response', 'text' => 'We respond to inquiries within 24 hours during business days.'],
                  ['title' => 'Expert Support', 'text' => 'Our team understands events, venues, and artist needs.'],
                  ['title' => 'Secure & Private', 'text' => 'Your information is handled securely and confidentially.'],
                ] as $item)
                <div class="text-center">
                    <div class="h-16 w-16 {{ $siteBrand->isRogues ? 'bg-sky-500/20' : 'bg-indigo-500/20' }} rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 {{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">{{ $item['title'] }}</h3>
                    <p class="{{ $siteBrand->detailMutedTextClass() }}">{{ $item['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
