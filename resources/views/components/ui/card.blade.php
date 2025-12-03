@props([
    'title' => null,
    'header' => null,
    'footer' => null,
    'padding' => 'p-6',
    'hover' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden' . ($hover ? ' hover:shadow-md transition-shadow duration-200' : '')]) }}>
    @if($title || $header)
        <div class="border-b border-gray-200 px-6 py-4 shadow-sm">
            @if($title)
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            @endif
            {{ $header }}
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
            {{ $footer }}
        </div>
    @endif
</div>
