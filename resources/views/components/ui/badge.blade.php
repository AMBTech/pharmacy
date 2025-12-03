@props([
    'variant' => 'primary', // primary, success, warning, danger, gray
    'size' => 'md', // sm, md
    'icon' => null,
])

@php
    $baseClasses = 'inline-flex items-center font-medium rounded-full';

    $variants = [
        'primary' => 'bg-primary-100 text-primary-800',
        'success' => 'bg-success-100 text-success-800',
        'warning' => 'bg-warning-100 text-warning-800',
        'danger' => 'bg-danger-100 text-danger-800',
        'gray' => 'bg-gray-100 text-gray-800',
    ];

    $sizes = [
        'sm' => 'px-2 py-1 text-xs',
        'md' => 'px-2.5 py-0.5 text-xs',
    ];

    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <i class="{{ $icon }} mr-1"></i>
    @endif
    {{ $slot }}
</span>
