{{--@props([--}}
{{--    'variant' => 'primary', // primary, secondary, success, danger, warning, ghost--}}
{{--    'size' => 'md', // sm, md, lg--}}
{{--    'icon' => null,--}}
{{--    'iconPosition' => 'left',--}}
{{--    'disabled' => false,--}}
{{--    'href' => null, // Add href support--}}
{{--])--}}

{{--@php--}}
{{--    $baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';--}}

{{--    $variants = [--}}
{{--        'primary' => 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 shadow-sm hover:shadow-md',--}}
{{--        'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-500 shadow-sm hover:shadow-md',--}}
{{--        'success' => 'bg-success-600 text-white hover:bg-success-700 focus:ring-success-500 shadow-sm hover:shadow-md',--}}
{{--        'danger' => 'bg-danger-600 text-white hover:bg-danger-700 focus:ring-danger-500 shadow-sm hover:shadow-md',--}}
{{--        'warning' => 'bg-warning-500 text-white hover:bg-warning-600 focus:ring-warning-500 shadow-sm hover:shadow-md',--}}
{{--        'ghost' => 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:ring-gray-500 border border-transparent',--}}
{{--    ];--}}

{{--    $sizes = [--}}
{{--        'sm' => 'px-3 py-2 text-sm',--}}
{{--        'md' => 'px-4 py-2.5 text-sm',--}}
{{--        'lg' => 'px-6 py-3 text-base',--}}
{{--    ];--}}

{{--    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];--}}

{{--    // Merge classes properly--}}
{{--    $attributes = $attributes->merge(['class' => $classes]);--}}
{{--@endphp--}}

{{--@if($href)--}}
{{--    <a href="{{ $href }}" {{ $attributes }} {{ $disabled ? 'aria-disabled="true"' : '' }}>--}}
{{--        @if($icon && $iconPosition === 'left')--}}
{{--            <i class="{{ $icon }} mr-2"></i>--}}
{{--        @endif--}}

{{--        {{ $slot }}--}}

{{--        @if($icon && $iconPosition === 'right')--}}
{{--            <i class="{{ $icon }} ml-2"></i>--}}
{{--        @endif--}}
{{--    </a>--}}
{{--@else--}}
{{--    <button {{ $attributes }} {{ $disabled ? 'disabled' : '' }}>--}}
{{--        @if($icon && $iconPosition === 'left')--}}
{{--            <i class="{{ $icon }} mr-2"></i>--}}
{{--        @endif--}}

{{--        {{ $slot }}--}}

{{--        @if($icon && $iconPosition === 'right')--}}
{{--            <i class="{{ $icon }} ml-2"></i>--}}
{{--        @endif--}}
{{--    </button>--}}
{{--@endif--}}


@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left',
    'disabled' => false,
    'href' => null,
])

@php
    $baseClass = 'btn';
    $variantClass = "btn-{$variant}";
    $sizeClass = "btn-{$size}";
    $styleClass = 'flex flex-row items-center gap-2 text-white bg-primary-600 rounded-md p-2 px-4';

    $classes = "{$baseClass} {$variantClass} {$sizeClass} {$styleClass}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'aria-disabled="true"' : '' }}>
        @if($icon && $iconPosition === 'left')
            <i class="{{ $icon }} text-2xl"></i>
{{--            <x-ui.svg-icon :name="$icon" class="w-4 h-4 mr-2" variant="white" />--}}
        @endif

        {{ $slot }}

        @if($icon && $iconPosition === 'right')
            <i class="{{ $icon }} text-2xl"></i>
{{--            <x-ui.svg-icon :name="$icon" class="w-4 h-4 ml-2" variant="white" />--}}
        @endif
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'disabled' : '' }}>
        @if($icon && $iconPosition === 'left')
            <i class="{{ $icon }} text-2xl"></i>
{{--            <x-ui.svg-icon :name="$icon" class="w-4 h-4 mr-2" variant="white" />--}}
        @endif

        {{ $slot }}

        @if($icon && $iconPosition === 'right')
            <i class="{{ $icon }} text-2xl"></i>
{{--            <x-ui.svg-icon :name="$icon" class="w-4 h-4 ml-2" variant="white" />--}}
        @endif
    </button>
@endif
