@props([
    'name', // The SVG file name without extension
    'class' => 'w-4 h-4', // Default size
    'variant' => 'default', // default, primary, success, etc.
])

@php
    $variants = [
        'default' => 'text-gray-600',
        'primary' => 'text-primary-600',
        'success' => 'text-success-600',
        'warning' => 'text-warning-600',
        'danger' => 'text-danger-600',
        'white' => 'text-white',
    ];

    $baseClass = 'w-4 h-4';
    $iconClass = $baseClass . ' ' . $class . ' ' . $variants[$variant];
@endphp

@if(file_exists(public_path("icons/{$name}.svg")))
    <img src="{{ asset("icons/{$name}.svg") }}" {{ $attributes->merge(['class' => $iconClass]) }} />
@else
    <!-- Fallback to LineIcons if SVG not found -->
    <i class="lni lni-question-circle {{ $iconClass }}"></i>
@endif
