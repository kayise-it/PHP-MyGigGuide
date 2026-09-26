@props([
    'instagram' => null,
    'facebook' => null,
    'twitter' => null,
    'tiktok' => null,
    'headingClass' => null,
])

@php
    $links = array_filter([
        'Instagram' => $instagram,
        'Facebook' => $facebook,
        'Twitter / X' => $twitter,
        'TikTok' => $tiktok,
    ], fn ($url) => filled($url));
@endphp

@if(count($links) > 0)
<div {{ $attributes->merge(['class' => '']) }}>
    <h3 class="{{ $headingClass ?? 'text-lg font-semibold text-white mb-3' }}">Social</h3>
    <div class="flex flex-wrap gap-2">
        @foreach($links as $label => $url)
            <a href="{{ $url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium border border-white/15 bg-black/30 text-slate-200 hover:bg-white/10 hover:text-white transition">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>
@endif
