{{--@props([--}}
{{--    'variant' => 'primary',--}}
{{--    'size' => 'md',--}}
{{--    'icon' => null,--}}
{{--    'iconPosition' => 'left',--}}
{{--    'disabled' => false,--}}
{{--    'href' => null,--}}
{{--])--}}

{{--@php--}}
{{--    $baseClass = 'btn';--}}
{{--    $variantClass = "btn-{$variant}";--}}
{{--    $sizeClass = "btn-{$size}";--}}
{{--    $styleClass = 'flex flex-row items-center gap-2 text-white bg-primary-600 rounded-md p-2 px-4';--}}

{{--    $classes = "{$baseClass} {$variantClass} {$sizeClass} {$styleClass}";--}}
{{--@endphp--}}

{{--@if($href)--}}
{{--    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'aria-disabled="true"' : '' }}>--}}
{{--        @if($icon && $iconPosition === 'left')--}}
{{--            <i class="{{ $icon }} text-2xl"></i>--}}
{{--            <x-ui.svg-icon :name="$icon" class="w-4 h-4 mr-2" variant="white" />--}}
{{--        @endif--}}

{{--        {{ $slot }}--}}

{{--        @if($icon && $iconPosition === 'right')--}}
{{--            <i class="{{ $icon }} text-2xl"></i>--}}
{{--            <x-ui.svg-icon :name="$icon" class="w-4 h-4 ml-2" variant="white" />--}}
{{--        @endif--}}
{{--    </a>--}}
{{--@else--}}
{{--    <button {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'disabled' : '' }}>--}}
{{--        @if($icon && $iconPosition === 'left')--}}
{{--            <i class="{{ $icon }} text-2xl"></i>--}}
{{--            <x-ui.svg-icon :name="$icon" class="w-4 h-4 mr-2" variant="white" />--}}
{{--        @endif--}}

{{--        {{ $slot }}--}}

{{--        @if($icon && $iconPosition === 'right')--}}
{{--            <i class="{{ $icon }} text-2xl"></i>--}}
{{--            <x-ui.svg-icon :name="$icon" class="w-4 h-4 ml-2" variant="white" />--}}
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
    'unescaped' => false, // Add this prop to control escaping
])

@php
    $baseClass = 'btn';
    $variantClass = "btn-{$variant}";
    $sizeClass = "btn-{$size}";
    $styleClass = 'flex flex-row items-center gap-2 text-white bg-primary-600 rounded-md p-2 px-4';

    $classes = "{$baseClass} {$variantClass} {$sizeClass} {$styleClass}";

    // Handle href escaping
    $hrefOutput = $href;
    if ($href && !$unescaped) {
        $hrefOutput = e($href); // Escape by default
    }
@endphp

@if($href)
    <a href="{{ $unescaped ? $href : e($href) }}" {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'aria-disabled="true"' : '' }}>
        @if($icon && $iconPosition === 'left')
            <i class="{{ $icon }} text-2xl"></i>
        @endif

        {{ $slot }}

        @if($icon && $iconPosition === 'right')
            <i class="{{ $icon }} text-2xl"></i>
        @endif
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'disabled' : '' }}>
        @if($icon && $iconPosition === 'left')
            <i class="{{ $icon }} text-2xl"></i>
        @endif

        {{ $slot }}

        @if($icon && $iconPosition === 'right')
            <i class="{{ $icon }} text-2xl"></i>
        @endif
    </button>
@endif
