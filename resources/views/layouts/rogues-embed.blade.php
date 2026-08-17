<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteBrand->name)</title>
    <meta name="description" content="@yield('description', $siteBrand->tagline)">
    <link rel="icon" href="{{ $siteBrand->faviconUrl() }}" type="image/png" sizes="32x32">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    @stack('head')
</head>
<body class="font-sans antialiased {{ $siteBrand->pageShellClass() }} {{ $siteBrand->bodyClass() }} min-h-screen">
    @yield('content')
    @stack('scripts')
</body>
</html>
