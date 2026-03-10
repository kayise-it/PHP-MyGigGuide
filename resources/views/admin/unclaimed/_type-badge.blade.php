@php
    $badgeConfig = [
        'artist' => [
            'color' => 'bg-purple-100 text-purple-700',
            'label' => 'Artist',
        ],
        'venue' => [
            'color' => 'bg-blue-100 text-blue-700',
            'label' => 'Venue',
        ],
        'event' => [
            'color' => 'bg-green-100 text-green-700',
            'label' => 'Event',
        ],
        'organiser' => [
            'color' => 'bg-orange-100 text-orange-700',
            'label' => 'Organiser',
        ],
    ];
    
    $config = $badgeConfig[$itemType] ?? ['color' => 'bg-gray-100 text-gray-700', 'label' => ucfirst($itemType)];
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $config['color'] }}">
    @include('admin.unclaimed._type-icon', ['itemType' => $itemType, 'class' => 'w-3.5 h-3.5'])
    {{ $config['label'] }}
</span>


