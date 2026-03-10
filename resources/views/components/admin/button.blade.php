@props([
    'variant' => 'primary', // primary, secondary, danger
    'full' => false,
])

@php
    $base = 'inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';
    $variants = [
        'primary' => 'bg-purple-600 text-white hover:bg-purple-700 focus:ring-purple-500',
        'secondary' => 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    ];
    $classes = ($full ? 'w-full ' : '') . $base . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>

