@props([
    'id' => 'modal',
    'title' => '',
    'size' => 'md', // sm, md, lg, xl
    'closeable' => true,
])

@php
    $sizes = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
@endphp

<div id="{{ $id }}"
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 transition-opacity duration-300 hidden">
    <div class="relative top-10 mx-auto p-4 {{ $sizes[$size] ?? $sizes['md'] }}">
        <!-- Modal content -->
        <div class="bg-white rounded-2xl shadow-xl transform transition-all duration-300">
            <!-- Header -->
            @if($title || $closeable)
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    @if($title)
                        <h3 class="text-xl font-semibold text-gray-900">{{ $title }}</h3>
                    @endif

                    @if($closeable)
                        <button type="button"
                                onclick="closeModal('{{ $id }}')"
                                class="text-gray-400 hover:text-gray-600 transition-colors duration-200 p-2 rounded-lg hover:bg-gray-100">
                            <i class="lni lni-close text-xl"></i>
                        </button>
                    @endif
                </div>
            @endif

        <!-- Body -->
            <div class="p-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if(isset($footer))
                <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Add backdrop animation
        setTimeout(() => {
            modal.classList.add('opacity-100');
        }, 50);
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('fixed') && e.target.id.includes('modal')) {
            closeModal(e.target.id);
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.fixed:not(.hidden)');
            if (openModal && openModal.id.includes('modal')) {
                closeModal(openModal.id);
            }
        }
    });
</script>
