@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Receive Items - PO#{{ $purchase->po_number }}</h1>
                <p class="text-gray-600 mt-1">Record received items and add to inventory</p>
            </div>
            <div>
                <a href="{{ route('purchases.show', $purchase) }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Order
                </a>
            </div>
        </div>

        <form action="{{ route('purchases.receive-store', $purchase) }}" method="POST" id="receiveForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Receiving Items -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Items to Receive -->
                    <x-ui.card title="Items to Receive">
                        <div class="space-y-4">
                            @foreach($purchase->items as $item)
                                @if($item->pending_quantity > 0)
                                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                        <div class="flex items-start mb-4">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900">{{ $item->product->name }}</h4>
                                                <p class="text-sm text-gray-600">
                                                    Ordered: {{ $item->quantity }} |
                                                    Received: {{ $item->received_quantity }} |
                                                    Pending: {{ $item->pending_quantity }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Quantity to Receive *</label>
                                                <input type="number" step="1" min="0" max="{{ $item->pending_quantity }}"
                                                       name="items[{{ $item->id }}][received_quantity]"
                                                       value="{{ $item->pending_quantity }}"
                                                       required
                                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                                       onchange="validateQuantity(this, {{ $item->pending_quantity }})">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Batch Number</label>
                                                <input type="text"
                                                       name="items[{{ $item->id }}][batch_number]"
                                                       value="{{ $item->batch_number }}"
                                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Manufacturing Date</label>
                                                <input type="date"
                                                       name="items[{{ $item->id }}][manufacturing_date]"
                                                       value="{{ $item->manufacturing_date ? $item->manufacturing_date->format('Y-m-d') : '' }}"
                                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label>
                                                <input type="date"
                                                       name="items[{{ $item->id }}][expiry_date]"
                                                       value="{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '' }}"
                                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </x-ui.card>
                </div>

                <!-- Right Column - Summary & Actions -->
                <div class="space-y-6">
                    <!-- Receiving Summary -->
                    <x-ui.card title="Receiving Summary">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Purchase Order</span>
                                <span class="font-medium">#{{ $purchase->po_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Supplier</span>
                                <span class="font-medium">{{ $purchase->supplier->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order Total</span>
                                <span class="font-medium">{{ format_currency($purchase->total) }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between font-medium">
                                    <span>Items Pending</span>
                                    <span>{{ $purchase->items->sum('pending_quantity') }}</span>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Receiving Notes -->
                    <x-ui.card title="Receiving Notes">
                    <textarea name="receiving_notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="Add any notes about this delivery..."></textarea>
                    </x-ui.card>

                    <!-- Actions -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="window.history.back()"
                                class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center justify-center">
                            <i class="lni lni-checkmark-circle mr-2"></i>
                            Confirm Receipt
                        </button>
                    </div>

                    <!-- Warning -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="lni lni-warning text-yellow-600 mt-0.5 mr-3"></i>
                            <div>
                                <p class="text-sm text-yellow-800 font-medium">Important</p>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Once received, items will be added to inventory and the order status will be updated.
                                    This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function validateQuantity(input, maxQuantity) {
            const value = parseFloat(input.value);
            if (value > maxQuantity) {
                input.value = maxQuantity;
                alert('Quantity cannot exceed pending quantity of ' + maxQuantity);
            }
            if (value < 0) {
                input.value = 0;
            }
        }

        // Form submission validation
        document.getElementById('receiveForm').addEventListener('submit', function(e) {
            let hasItemsToReceive = false;

            // Check if any items have quantity > 0
            const quantityInputs = document.querySelectorAll('input[name*="received_quantity"]');
            quantityInputs.forEach(input => {
                if (parseFloat(input.value) > 0) {
                    hasItemsToReceive = true;
                }
            });

            if (!hasItemsToReceive) {
                e.preventDefault();
                alert('Please enter quantity for at least one item to receive.');
            }
        });
    </script>
@endpush
