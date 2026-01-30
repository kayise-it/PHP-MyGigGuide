<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $shareData['title'] }}</title>
    <meta name="description" content="{{ $shareData['description'] }}">

    <meta property="og:title" content="{{ $shareData['title'] }}">
    <meta property="og:description" content="{{ $shareData['description'] }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $shareData['url'] }}">
    <meta property="og:site_name" content="My Gig Guide">
    @if(!empty($shareData['image']))
        <meta property="og:image" content="{{ $shareData['image'] }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $shareData['title'] }}">
    <meta name="twitter:description" content="{{ $shareData['description'] }}">
    @if(!empty($shareData['image']))
        <meta name="twitter:image" content="{{ $shareData['image'] }}">
    @endif
</head>
<body>
    <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
        <h1>{{ $venue->name }}</h1>
        <p>{{ $shareData['description'] }}</p>
        @if(!empty($shareData['image']))
            <img src="{{ $shareData['image'] }}" alt="{{ $venue->name }}" style="max-width: 100%; height: auto;">
        @endif
        <p><a href="{{ $shareData['url'] }}">View Venue</a></p>
    </div>
</body>
</html>
