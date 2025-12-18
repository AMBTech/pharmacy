@props([
    'type' => 'info',
    'message' => '',
    'dismissible' => false,
    'icon' => null,
])

@php
    $typeClasses = [
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'danger' => 'bg-red-50 border-red-200 text-red-800',
    ];

    $typeIcons = [
        'info' => 'lni lni-information',
        'success' => 'lni lni-checkmark-circle',
        'warning' => 'lni lni-warning',
        'error' => 'lni lni-close-circle',
        'danger' => 'lni lni-close-circle',
    ];

    $iconClass = $icon ?? $typeIcons[$type] ?? $typeIcons['info'];
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border px-4 py-3 {$typeClasses[$type]} transition-all duration-300"]) }}>
    <div class="flex items-start">
        <!-- Icon -->
        <div class="flex-shrink-0 mt-0.5">
            <i class="{{ $iconClass }} text-lg"></i>
        </div>

        <!-- Message -->
        <div class="ml-3 flex-1">
            <p class="text-sm">{{ $message }}</p>
        </div>

        <!-- Dismiss Button -->
        @if($dismissible)
            <button onclick="this.parentElement.parentElement.style.display='none'"
                    class="ml-3 flex-shrink-0 text-gray-400 hover:text-gray-500 focus:outline-none">
                <i class="lni lni-close text-sm"></i>
            </button>
        @endif
    </div>
</div>
