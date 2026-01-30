@props(['video', 'class' => ''])

@if($video && $video->youtube_video_id)
<div class="youtube-video-container {{ $class }}">
    <div class="relative w-full" style="padding-bottom: 56.25%;">
        <iframe
            src="{{ $video->embed_url }}"
            title="{{ $video->title ?? 'YouTube video' }}"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            class="absolute top-0 left-0 w-full h-full rounded-lg"
            loading="lazy"
        ></iframe>
    </div>
    @if($video->title)
    <p class="mt-2 text-sm text-gray-600">{{ $video->title }}</p>
    @endif
</div>
@endif

