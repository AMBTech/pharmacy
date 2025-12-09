@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Return Items</h1>
                <p class="text-gray-600 mt-1">Sale: {{ $sale->invoice_number }}</p>
            </div>
        </div>

        <x-ui.card class="p-6">
            <form id="returnForm" action="{{ route('returns.store', $sale) }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 gap-4">
                    @foreach($sale->items as $item)
                        @php
                            $unitPrice = $item->quantity > 0 ? ($item->total_price / $item->quantity) : 0;
                        @endphp

                        <div class="border p-4 rounded-lg bg-gray-50 return-item-row" data-sale-item-id="{{ $item->id }}"
                             data-max-qty="{{ $item->quantity }}" data-unit-price="{{ $unitPrice }}">
                            <div class="flex justify-between items-start">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 truncate">{{ $item->product_name }}</p>
                                    <p class="text-sm text-gray-600 mt-1">Sold Qty: {{ $item->quantity }}</p>
                                </div>

                                <div class="w-36">
                                    <label class="text-sm text-gray-700 block">Return Qty</label>

                                    <input
                                        type="number"
                                        name="items[{{ $loop->index }}][quantity]"
                                        class="return-qty w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                        max="{{ $item->quantity }}"
                                        min="0"
                                        value="0"
                                        step="1"
                                        aria-label="Return quantity for {{ $item->product_name }}"
                                    >

                                    <input type="hidden" name="items[{{ $loop->index }}][sale_item_id]" value="{{ $item->id }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="font-medium text-gray-700">Refund Method</label>
                        <select name="refund_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-medium text-gray-700">Reason</label>
                        <input type="text" name="reason" placeholder="Enter reason"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        >
                    </div>
                </div>

                {{-- Visible refund total (user-friendly) --}}
                <div class="mt-4 text-right">
                    <div class="text-sm text-gray-600">Refund Amount</div>
                    <div id="refundTotalDisplay" class="text-2xl font-bold text-gray-900">0.00</div>
                </div>

                {{-- Hidden input synced with JS but server will recompute/verify it --}}
                <input type="hidden" id="refundAmount" name="refund_amount" value="0.00">

                <button class="mt-6 bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 w-full" type="submit">
                    Process Return
                </button>
            </form>
        </x-ui.card>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('returnForm');
            const refundDisplay = document.getElementById('refundTotalDisplay');
            const refundHidden = document.getElementById('refundAmount');

            // Format number nicely according to user's locale; fallback if missing
            const moneyFormatter = new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            function recalcRefund() {
                let total = 0;

                document.querySelectorAll('.return-item-row').forEach(function (row, idx) {
                    const unit = parseFloat(row.dataset.unitPrice) || 0;
                    const maxQty = parseInt(row.dataset.maxQty) || 0;

                    // find the corresponding input inside row (use index)
                    const qtyInput = row.querySelector('.return-qty');
                    let qty = 0;
                    if (qtyInput) {
                        qty = parseInt(qtyInput.value || 0, 10);
                        if (isNaN(qty)) qty = 0;
                    }

                    // clamp between 0 and maxQty (extra safety)
                    if (qty < 0) qty = 0;
                    if (qty > maxQty) qty = maxQty;

                    total += qty * unit;
                });

                // round to 2 decimals
                total = Math.round((total + Number.EPSILON) * 100) / 100;

                refundHidden.value = total.toFixed(2);
                refundDisplay.textContent = moneyFormatter.format(total);
            }

            // Attach listeners on all quantity inputs
            document.querySelectorAll('.return-qty').forEach(function (input) {
                input.addEventListener('input', recalcRefund);
                input.addEventListener('change', recalcRefund);
            });

            // Initial calculation
            recalcRefund();

            // Optional: before submit ensure there's at least one qty > 0
            form.addEventListener('submit', function (e) {
                const total = parseFloat(refundHidden.value || '0');
                if (total <= 0) {
                    e.preventDefault();
                    alert('Please enter a return quantity for at least one item to process a refund.');
                    return false;
                }
                return true;
            });
        });
    </script>
@endpush
