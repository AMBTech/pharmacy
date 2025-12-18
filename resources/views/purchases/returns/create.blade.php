@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ isset($purchaseReturn) ? 'Edit Purchase Return' : 'New Purchase Return' }}
                </h1>
                <p class="text-gray-600 mt-1">
                    {{ isset($purchaseReturn) ? 'Update return details' : 'Create a new purchase return' }}
                </p>
            </div>
            <div>
                <a href="{{ route('purchases.returns.index') }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Returns
                </a>
            </div>
        </div>

        <form action="{{ isset($purchaseReturn) ? route('purchases.returns.update', $purchaseReturn) : route('purchases.returns.store') }}"
              method="POST" id="returnForm">
            @csrf
            @if(isset($purchaseReturn))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Return Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Purchase Order Selection -->
                    <x-ui.card title="Select Purchase Order">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Order *</label>
                                <select name="purchase_order_id" required id="purchaseOrderSelect"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                        onchange="loadPurchaseOrderItems()">
                                    <option value="">Select Purchase Order</option>
                                    @foreach($purchaseOrders as $order)
                                        <option value="{{ $order->id }}"
                                                {{ (isset($purchaseReturn) && $purchaseReturn->purchase_order_id == $order->id) || old('purchase_order_id') == $order->id ? 'selected' : '' }}
                                                data-supplier="{{ $order->supplier->name }}" data-orderDate="{{ $order->order_date }}">
                                            {{ $order->po_number }} - {{ $order->supplier->name }} ({{ $order->order_date->format('M d, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                                    <div id="selectedSupplier" class="px-3 py-2 bg-gray-50 rounded-lg text-gray-600">
                                        {{ isset($purchaseReturn) ? $purchaseReturn->purchaseOrder->supplier->name : 'Select a purchase order' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Order Date</label>
                                    <div id="orderDate" class="px-3 py-2 bg-gray-50 rounded-lg text-gray-600">
                                        {{ isset($purchaseReturn) ? $purchaseReturn->purchaseOrder->order_date : 'Select a purchase order' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Return Items -->
                    <x-ui.card title="Return Items">
                        <div class="space-y-4">
                            <div id="returnItems">
                                <!-- Items will be loaded here dynamically -->
                                @if(isset($purchaseReturn))
                                    @foreach($purchaseReturn->items as $index => $item)
                                        <div class="return-item border border-gray-200 rounded-lg p-4 bg-gray-50 mb-3">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Product</label>
                                                    <div class="font-medium">{{ $item->purchaseOrderItem->product->name }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        Batch: {{ $item->purchaseOrderItem->batch_number }} |
                                                        Received: {{ $item->purchaseOrderItem->received_quantity }}
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Available Qty</label>
                                                        <div class="text-sm font-semibold">{{ $item->purchaseOrderItem->received_quantity - $item->purchaseOrderItem->returned_quantity }}</div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Return Qty *</label>
                                                        <input type="number" name="items[{{ $index }}][quantity]"
                                                               min="0.01" step="0.01" max="{{ $item->purchaseOrderItem->received_quantity - $item->purchaseOrderItem->returned_quantity }}"
                                                               value="{{ $item->quantity }}"
                                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent return-quantity"
                                                               oninput="calculateReturnTotals()">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-3 mb-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Unit Cost ({{ $currency ?? '$' }})</label>
                                                    <input type="number" name="items[{{ $index }}][unit_cost]"
                                                           step="0.01" min="0"
                                                           value="{{ $item->unit_cost }}"
                                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent return-unit-cost"
                                                           oninput="calculateReturnTotals()">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Total Cost</label>
                                                    <div class="px-3 py-2 text-sm font-medium bg-gray-100 rounded-lg item-return-total">
                                                        {{ format_currency($item->quantity * $item->unit_cost) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Reason for Return</label>
                                                <select name="items[{{ $index }}][reason_type]"
                                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                    <option value="damaged" {{ $item->reason_type == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                                    <option value="defective" {{ $item->reason_type == 'defective' ? 'selected' : '' }}>Defective</option>
                                                    <option value="expired" {{ $item->reason_type == 'expired' ? 'selected' : '' }}>Expired</option>
                                                    <option value="wrong_item" {{ $item->reason_type == 'wrong_item' ? 'selected' : '' }}>Wrong Item</option>
                                                    <option value="excess_quantity" {{ $item->reason_type == 'excess_quantity' ? 'selected' : '' }}>Excess Quantity</option>
                                                    <option value="other" {{ $item->reason_type == 'other' ? 'selected' : '' }}>Other</option>
                                                </select>

                                                <input type="text" name="items[{{ $index }}][reason]"
                                                       value="{{ $item->reason }}"
                                                       class="mt-2 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                                       placeholder="Additional details...">
                                            </div>

                                            <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $item->purchase_order_item_id }}">
                                            @if(isset($item->id))
                                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div id="noItemsMessage" class="text-center py-8 text-gray-500 {{ isset($purchaseReturn) ? 'hidden' : '' }}">
                                <i class="lni lni-package text-4xl mb-3"></i>
                                <p>Select a purchase order to view returnable items</p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Right Column - Return Summary -->
                <div class="space-y-6">
                    <!-- Return Information -->
                    <x-ui.card title="Return Information">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Return Date *</label>
                                <input type="date" name="return_date" required
                                       value="{{ isset($purchaseReturn) ? $purchaseReturn->return_date->format('Y-m-d') : old('return_date', date('Y-m-d')) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Return Type *</label>
                                <select name="return_type" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="full_refund" {{ (isset($purchaseReturn) && $purchaseReturn->return_type == 'full_refund') || old('return_type') == 'full_refund' ? 'selected' : '' }}>Full Refund</option>
                                    <option value="partial_refund" {{ (isset($purchaseReturn) && $purchaseReturn->return_type == 'partial_refund') || old('return_type') == 'partial_refund' ? 'selected' : '' }}>Partial Refund</option>
                                    <option value="replacement" {{ (isset($purchaseReturn) && $purchaseReturn->return_type == 'replacement') || old('return_type') == 'replacement' ? 'selected' : '' }}>Replacement</option>
                                    <option value="store_credit" {{ (isset($purchaseReturn) && $purchaseReturn->return_type == 'store_credit') || old('return_type') == 'store_credit' ? 'selected' : '' }}>Store Credit</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="pending" {{ (isset($purchaseReturn) && $purchaseReturn->status == 'pending') || old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ (isset($purchaseReturn) && $purchaseReturn->status == 'approved') || old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ (isset($purchaseReturn) && $purchaseReturn->status == 'rejected') || old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="completed" {{ (isset($purchaseReturn) && $purchaseReturn->status == 'completed') || old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Return Summary -->
                    <x-ui.card title="Return Summary">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium" id="returnSubtotal">{{ $currency ?? '$' }}0.00</span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Restocking Fee (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="restocking_fee"
                                       value="{{ isset($purchaseReturn) ? $purchaseReturn->restocking_fee : old('restocking_fee', 0) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       onchange="calculateReturnTotals()">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Cost ({{ $currency ?? '$' }})</label>
                                <input type="number" step="0.01" min="0" name="shipping_cost"
                                       value="{{ isset($purchaseReturn) ? $purchaseReturn->shipping_cost : old('shipping_cost', 0) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       onchange="calculateReturnTotals()">
                            </div>

                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between font-semibold text-lg">
                                    <span>Total Refund</span>
                                    <span id="returnTotal">{{ $currency ?? '$' }}0.00</span>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Notes -->
                    <x-ui.card title="Notes">
                    <textarea name="reason" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="Reason for return...">{{ isset($purchaseReturn) ? $purchaseReturn->reason : old('reason') }}</textarea>
                    </x-ui.card>

                    <!-- Actions -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="window.history.back()"
                                class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                            {{ isset($purchaseReturn) ? 'Update Return' : 'Create Return' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const currencySymbol = '{{ $currency ?? "$" }}';
        let purchaseOrderItems = [];

        @if(isset($purchaseReturn))
        // Calculate totals for existing return
        calculateReturnTotals();
        @endif

        async function loadPurchaseOrderItems() {
            const purchaseOrderId = document.getElementById('purchaseOrderSelect').value;
            const noItemsMessage = document.getElementById('noItemsMessage');

            if (!purchaseOrderId) {
                document.getElementById('returnItems').innerHTML = '';
                noItemsMessage.classList.remove('hidden');
                document.getElementById('selectedSupplier').textContent = 'Select a purchase order';
                document.getElementById('orderDate').textContent = 'Select a purchase order';
                return;
            }

            // Update supplier and date info
            const selectedOption = document.getElementById('purchaseOrderSelect').selectedOptions[0];
            document.getElementById('selectedSupplier').textContent = selectedOption.dataset.supplier;
            document.getElementById('orderDate').textContent = selectedOption.dataset.orderdate;

            try {
                // Fetch purchase order items
                const response = await fetch(`/api/purchase-orders/${purchaseOrderId}/returnable-items`);
                purchaseOrderItems = await response.json();

                renderReturnItems();
                calculateReturnTotals();
                noItemsMessage.classList.add('hidden');
            } catch (error) {
                console.error('Error loading items:', error);
                showNotification('Error loading items. Please try again.', 'error');
            }
        }

        function renderReturnItems() {
            const container = document.getElementById('returnItems');
            container.innerHTML = '';

            purchaseOrderItems.data.forEach((item, index) => {
                const availableQty = parseFloat(item.received_quantity) - parseFloat(item.returned_quantity);

                if (availableQty <= 0) return; // Skip items with no available quantity

                const itemHtml = `
                    <div class="return-item border border-gray-200 rounded-lg p-4 bg-gray-50 mb-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Product</label>
                                <div class="font-medium">${item.product_name}</div>
                                <div class="text-xs text-gray-500">
                                    Batch: ${item.batch_number || 'N/A'} |
                                    Received: ${item.received_quantity}
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Available Qty</label>
                                    <div class="text-sm font-semibold">${availableQty}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Return Qty *</label>
                                    <input type="number" name="items[${index}][quantity]"
                                           min="1" step="1" max="${availableQty}"
                                           value="0"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent return-quantity"
                                           oninput="calculateItemTotal(${index}); calculateReturnTotals()">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Unit Cost (${currencySymbol})</label>
                                <input type="number" name="items[${index}][unit_cost]"
                                       step="0.01" min="0" value="${item.unit_cost}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent return-unit-cost"
                                       oninput="calculateItemTotal(${index}); calculateReturnTotals()">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Total Cost</label>
                                <div class="px-3 py-2 text-sm font-medium bg-gray-100 rounded-lg item-return-total" data-index="${index}">
                                    ${currencySymbol}0.00
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Reason for Return</label>
                            <select name="items[${index}][reason_type]"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="damaged">Damaged</option>
                                <option value="defective">Defective</option>
                                <option value="expired">Expired</option>
                                <option value="wrong_item">Wrong Item</option>
                                <option value="excess_quantity">Excess Quantity</option>
                                <option value="other">Other</option>
                            </select>

                            <input type="text" name="items[${index}][reason]"
                                   class="mt-2 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="Additional details...">
                        </div>

                        <input type="hidden" name="items[${index}][purchase_order_item_id]" value="${item.id}">
                    </div>
                `;
                container.innerHTML += itemHtml;
            });
        }

        function calculateItemTotal(index) {
            const quantityInput = document.querySelector(`input[name="items[${index}][quantity]"]`);
            const unitCostInput = document.querySelector(`input[name="items[${index}][unit_cost]"]`);
            const totalElement = document.querySelector(`.item-return-total[data-index="${index}"]`);

            if (!quantityInput || !unitCostInput || !totalElement) return;

            const quantity = parseFloat(quantityInput.value) || 0;
            const unitCost = parseFloat(unitCostInput.value) || 0;
            const total = quantity * unitCost;

            totalElement.textContent = currencySymbol + total.toFixed(2);
        }

        function calculateReturnTotals() {
            let subtotal = 0;

            // Calculate from existing return items
            const returnItems = document.querySelectorAll('.return-item');
            returnItems.forEach(item => {
                const quantityInput = item.querySelector('.return-quantity');
                const unitCostInput = item.querySelector('.return-unit-cost');

                if (quantityInput && unitCostInput) {
                    const quantity = parseFloat(quantityInput.value) || 0;
                    const unitCost = parseFloat(unitCostInput.value) || 0;
                    subtotal += quantity * unitCost;
                }
            });

            const restockingFee = parseFloat(document.querySelector('input[name="restocking_fee"]').value) || 0;
            const shippingCost = parseFloat(document.querySelector('input[name="shipping_cost"]').value) || 0;

            const feeAmount = subtotal * (restockingFee / 100);
            const total = subtotal - feeAmount - shippingCost;

            document.getElementById('returnSubtotal').textContent = currencySymbol + subtotal.toFixed(2);
            document.getElementById('returnTotal').textContent = currencySymbol + Math.max(total, 0).toFixed(2);
        }

        // Form submission handler
        document.getElementById('returnForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate purchase order selection
            const purchaseOrderId = document.getElementById('purchaseOrderSelect').value;
            if (!purchaseOrderId) {
                showNotification('Please select a purchase order.', 'error');
                return;
            }

            // Validate at least one item has quantity > 0
            const hasReturnItems = Array.from(document.querySelectorAll('.return-quantity'))
                .some(input => parseFloat(input.value) > 0);

            if (!hasReturnItems) {
                showNotification('Please add at least one item to return.', 'error');
                return;
            }

            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="lni lni-spinner animate-spin mr-2"></i> Processing...';
            submitButton.disabled = true;

            // Submit via fetch
            fetch(this.action, {
                method: this.method,
                body: new FormData(this),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.errors) {
                        let errorMessages = [];
                        for (const field in data.errors) {
                            errorMessages.push(data.errors[field][0]);
                        }
                        showNotification('Validation errors:\n' + errorMessages.join('\n'), 'error');
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    } else if (data.success) {
                        window.location.href = data.redirect;
                    } else if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        showNotification('An error occurred. Please try again.', 'error');
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                });
        });

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' :
                    type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    </script>
@endpush
