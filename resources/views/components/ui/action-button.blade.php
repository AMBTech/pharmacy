@props([
    'variant' => 'primary',
    'href' => null,
    'icon' => null,
])

@php
    $variants = [
        'primary' => 'bg-primary-600 hover:bg-primary-700 text-white border-primary-600',
        'success' => 'bg-success-600 hover:bg-success-700 text-white border-success-600',
        'warning' => 'bg-warning-500 hover:bg-warning-600 text-white border-warning-500',
        'secondary' => 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200',
        'blue' => 'bg-blue-100 hover:bg-blue-200 text-gray-700 border-blue-200 hover:border-blue-300'
    ];

    $baseClasses = 'flex flex-col items-center justify-center p-6 rounded-xl border-2 transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 h-24';
    $classes = $baseClasses . ' ' . $variants[$variant];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="{{ $icon }} text-2xl mb-2"></i>
        @endif
        <span class="font-semibold text-center">{{ $slot }}</span>
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="{{ $icon }} text-2xl mb-2"></i>
        @endif
        <span class="font-semibold text-center">{{ $slot }}</span>
    </button>
@endif
