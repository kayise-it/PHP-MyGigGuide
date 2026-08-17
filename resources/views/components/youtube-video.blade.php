@props(['video', 'class' => ''])

@if($video && $video->youtube_video_id)
<div class="youtube-video-container {{ $class }}">
    <p class="mb-2 text-sm font-medium text-gray-900">{{ $video->display_title }}</p>
    <div class="relative w-full" style="padding-bottom: 56.25%;">
        <iframe
            src="{{ $video->embed_url }}"
            title="{{ $video->display_title }}"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            class="absolute top-0 left-0 w-full h-full rounded-lg"
            loading="lazy"
        ></iframe>
    </div>
</div>
@endif

