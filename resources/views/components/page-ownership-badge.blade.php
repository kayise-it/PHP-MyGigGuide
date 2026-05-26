@props([
    'entity',
    'variant' => 'hero',
])

@if(is_object($entity) && method_exists($entity, 'getPublicOwnershipStatus'))
@php
    $status = $entity->getPublicOwnershipStatus();
    $label = $entity->getPublicOwnershipLabel();
    $description = $entity->getPublicOwnershipDescription();

    $classes = match ($variant) {
        'hero' => match ($status) {
            'official' => 'bg-emerald-500/90 text-white border border-emerald-400/50',
            'unclaimed' => 'bg-amber-500/95 text-white border border-amber-300/60',
            'pending' => 'bg-sky-500/90 text-white border border-sky-400/50',
            'disputed' => 'bg-orange-500/90 text-white border border-orange-400/50',
            default => 'bg-white/20 text-white border border-white/30',
        },
        default => match ($status) {
            'official' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            'unclaimed' => 'bg-amber-100 text-amber-900 border border-amber-200',
            'pending' => 'bg-sky-100 text-sky-800 border border-sky-200',
            'disputed' => 'bg-orange-100 text-orange-900 border border-orange-200',
            default => 'bg-gray-100 text-gray-700 border border-gray-200',
        },
    };
@endphp

<span
    {{ $attributes->merge([
        'class' => 'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold ' . $classes,
        'title' => $description,
    ]) }}
>
    {{ $label }}
</span>
@endif
