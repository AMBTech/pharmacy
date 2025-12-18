@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Purchase Return</h1>
                <p class="text-gray-600 mt-1">Update return #{{ $purchaseReturn->return_number }}</p>
                <div class="mt-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        <i class="lni lni-pencil mr-1"></i>
                        Editing - Status: {{ ucfirst($purchaseReturn->status) }}
                    </span>
                </div>
            </div>
            <div>
                <a href="{{ route('purchases.returns.show', $purchaseReturn) }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Return
                </a>
            </div>
        </div>

        @if(session('error'))
            <x-ui.alert type="error" message="{{ session('error') }}" />
        @endif

        @if(session('success'))
            <x-ui.alert type="success" message="{{ session('success') }}" />
        @endif

        <form action="{{ route('purchases.returns.update', $purchaseReturn) }}"
              method="POST" id="returnForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Return Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Purchase Order Selection -->
                    <x-ui.card title="Purchase Order">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Order *</label>
                                <select name="purchase_order_id" required id="purchaseOrderSelect"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                        onchange="loadPurchaseOrderItems()">
                                    <option value="">Select Purchase Order</option>
                                    @foreach($purchaseOrders as $order)
                                        <option value="{{ $order->id }}"
                                                {{ $purchaseReturn->purchase_order_id == $order->id ? 'selected' : '' }}
                                                data-supplier="{{ $order->supplier->name }}"
                                                data-order-date="{{ $order->order_date->format('M d, Y') }}">
                                            {{ $order->po_number }} - {{ $order->supplier->name }} ({{ $order->order_date->format('M d, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                                    <div id="selectedSupplier" class="px-3 py-2 bg-gray-50 rounded-lg text-gray-900 font-medium">
                                        {{ $purchaseReturn->purchaseOrder->supplier->name }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Order Date</label>
                                    <div id="orderDate" class="px-3 py-2 bg-gray-50 rounded-lg text-gray-900">
                                        {{ $purchaseReturn->purchaseOrder->order_date->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Return Items -->
                    <x-ui.card title="Return Items">
                        <div class="space-y-4">
                            <div id="returnItems">
                                @foreach($purchaseReturn->items as $index => $item)
                                    @php
                                        $purchaseOrderItem = $item->purchaseOrderItem;
                                        $availableQty = $purchaseOrderItem->received_quantity - $purchaseOrderItem->returned_quantity + $item->quantity;
                                    @endphp
                                    <div class="return-item border border-gray-200 rounded-lg p-4 bg-gray-50 mb-3">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900">{{ $item->purchaseOrderItem->product->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    Batch: {{ $purchaseOrderItem->batch_number ?: 'N/A' }} |
                                                    Received: {{ $purchaseOrderItem->received_quantity }} |
                                                    Already Returned: {{ $purchaseOrderItem->returned_quantity - $item->quantity }}
                                                </div>
                                            </div>
                                            <button type="button" onclick="removeReturnItem(this)"
                                                    class="ml-3 text-red-600 hover:text-red-700 p-1 rounded hover:bg-red-50"
                                                    data-item-id="{{ $item->id }}">
                                                <i class="lni lni-close"></i>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Available Quantity</label>
                                                <div class="text-sm font-semibold text-gray-900">{{ $availableQty }}</div>
                                                <div class="text-xs text-gray-500">
                                                    (Previously returned: {{ $item->quantity }})
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Return Quantity *</label>
                                                <input type="number"
                                                       name="items[{{ $index }}][quantity]"
                                                       min="1"
                                                       step="1"
                                                       max="{{ $availableQty }}"
                                                       value="{{ old('items.' . $index . '.quantity', $item->quantity) }}"
                                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent return-quantity"
                                                       oninput="calculateItemTotal(this); calculateReturnTotals()"
                                                       required>
                                                @error('items.' . $index . '.quantity')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 mb-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Unit Cost ({{ $currency ?? '$' }})</label>
                                                <input type="number"
                                                       name="items[{{ $index }}][unit_cost]"
                                                       step="0.01"
                                                       min="0"
                                                       value="{{ old('items.' . $index . '.unit_cost', $item->unit_cost) }}"
                                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent return-unit-cost"
                                                       oninput="calculateItemTotal(this); calculateReturnTotals()"
                                                       required>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Total Cost</label>
                                                <div class="px-3 py-2 text-sm font-medium bg-gray-100 rounded-lg item-return-total">
                                                    {{ format_currency($item->quantity * $item->unit_cost) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 mb-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Reason Type</label>
                                                <select name="items[{{ $index }}][reason_type]"
                                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                    <option value="damaged" {{ $item->reason_type == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                                    <option value="defective" {{ $item->reason_type == 'defective' ? 'selected' : '' }}>Defective</option>
                                                    <option value="expired" {{ $item->reason_type == 'expired' ? 'selected' : '' }}>Expired</option>
                                                    <option value="wrong_item" {{ $item->reason_type == 'wrong_item' ? 'selected' : '' }}>Wrong Item</option>
                                                    <option value="excess_quantity" {{ $item->reason_type == 'excess_quantity' ? 'selected' : '' }}>Excess Quantity</option>
                                                    <option value="other" {{ $item->reason_type == 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Total Cost (Calculated)</label>
                                                <div class="px-3 py-2 text-sm font-medium bg-gray-100 rounded-lg">
                                                    {{ format_currency($item->quantity * $item->unit_cost) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Reason Details</label>
                                            <input type="text"
                                                   name="items[{{ $index }}][reason]"
                                                   value="{{ old('items.' . $index . '.reason', $item->reason) }}"
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                                   placeholder="Additional details...">
                                        </div>

                                        <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]"
                                               value="{{ $item->purchase_order_item_id }}">
                                        @if($item->id)
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div id="noItemsMessage" class="text-center py-8 text-gray-500 hidden">
                                <i class="lni lni-package text-4xl mb-3"></i>
                                <p>Select a purchase order to view returnable items</p>
                            </div>

                            <!-- Add Item Button (hidden when editing existing items) -->
                            <div id="addItemSection" class="hidden">
                                <button type="button"
                                        onclick="addNewItem()"
                                        class="w-full border-2 border-dashed border-gray-300 rounded-lg py-4 text-gray-500 hover:text-gray-700 hover:border-gray-400 transition-colors flex items-center justify-center">
                                    <i class="lni lni-plus mr-2"></i>
                                    Add Another Product
                                </button>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Warning for Changed Purchase Order -->
                    <div id="poChangeWarning" class="hidden">
                        {{--<x-ui.alert type="warning"
                                    message="Changing the purchase order will reset all items. Please re-select items from the new purchase order." />--}}
                    </div>
                </div>

                <!-- Right Column - Return Summary -->
                <div class="space-y-6">
                    <!-- Return Information -->
                    <x-ui.card title="Return Information">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Return Date *</label>
                                <input type="date" name="return_date" required
                                       value="{{ old('return_date', $purchaseReturn->return_date->format('Y-m-d')) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                @error('return_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Return Type *</label>
                                <select name="return_type" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="full_refund" {{ old('return_type', $purchaseReturn->return_type) == 'full_refund' ? 'selected' : '' }}>Full Refund</option>
                                    <option value="partial_refund" {{ old('return_type', $purchaseReturn->return_type) == 'partial_refund' ? 'selected' : '' }}>Partial Refund</option>
                                    <option value="replacement" {{ old('return_type', $purchaseReturn->return_type) == 'replacement' ? 'selected' : '' }}>Replacement</option>
                                    <option value="store_credit" {{ old('return_type', $purchaseReturn->return_type) == 'store_credit' ? 'selected' : '' }}>Store Credit</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select name="status" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="pending" {{ old('status', $purchaseReturn->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ old('status', $purchaseReturn->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status', $purchaseReturn->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="completed" {{ old('status', $purchaseReturn->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Note: Changing status may trigger additional actions.</p>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Return Summary -->
                    <x-ui.card title="Return Summary">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium" id="returnSubtotal">{{ format_currency($purchaseReturn->subtotal) }}</span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Restocking Fee (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="restocking_fee"
                                       value="{{ old('restocking_fee', $purchaseReturn->restocking_fee) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       onchange="calculateReturnTotals()">
                                @error('restocking_fee')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Cost ({{ $currency ?? '$' }})</label>
                                <input type="number" step="0.01" min="0" name="shipping_cost"
                                       value="{{ old('shipping_cost', $purchaseReturn->shipping_cost) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       onchange="calculateReturnTotals()">
                                @error('shipping_cost')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between font-semibold text-lg">
                                    <span>Total Refund</span>
                                    <span id="returnTotal">{{ format_currency($purchaseReturn->total) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Total amount to be refunded to supplier</p>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Notes -->
                    <x-ui.card title="Notes & Reason">
                    <textarea name="reason" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="Reason for return...">{{ old('reason', $purchaseReturn->reason) }}</textarea>
                        @error('reason')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror

                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                            <textarea name="notes" rows="2"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                      placeholder="Additional notes...">{{ old('notes', $purchaseReturn->notes) }}</textarea>
                        </div>
                    </x-ui.card>

                    <!-- Actions -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="window.history.back()"
                                class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center justify-center">
                            <i class="lni lni-checkmark-circle mr-2"></i>
                            Update Return
                        </button>
                    </div>

                    <!-- Delete Button -->
                    <div class="pt-4 border-t border-gray-200">
                        <button type="button" onclick="confirmDelete()"
                                class="w-full bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors flex items-center justify-center">
                            <i class="lni lni-trash-can mr-2"></i>
                            Delete Return
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
        let itemCounter = {{ $purchaseReturn->items->count() }};
        let originalPurchaseOrderId = {{ $purchaseReturn->purchase_order_id }};

        // Store initial items for comparison
        {{--let initialItems = @json($purchaseReturn->items->map(function($item) {--}}
        {{--    return [--}}
        {{--        'id' => $item->id,--}}
        {{--        'purchase_order_item_id' => $item->purchase_order_item_id,--}}
        {{--        'quantity' => (float) $item->quantity,--}}
        {{--        'unit_cost' => (float) $item->unit_cost,--}}
        {{--    ];--}}
        {{--}));--}}

        // Calculate totals on page load
        calculateReturnTotals();

        // Track purchase order changes
        document.getElementById('purchaseOrderSelect').addEventListener('change', function() {
            const newPoId = this.value;
            if (newPoId && newPoId != originalPurchaseOrderId) {
                document.getElementById('poChangeWarning').classList.remove('hidden');
                document.getElementById('returnItems').innerHTML = '';
                document.getElementById('addItemSection').classList.remove('hidden');
                purchaseOrderItems = [];
            } else {
                document.getElementById('poChangeWarning').classList.add('hidden');
            }
        });

        async function loadPurchaseOrderItems() {
            const purchaseOrderId = document.getElementById('purchaseOrderSelect').value;
            const noItemsMessage = document.getElementById('noItemsMessage');

            if (!purchaseOrderId) {
                document.getElementById('returnItems').innerHTML = '';
                noItemsMessage.classList.remove('hidden');
                document.getElementById('selectedSupplier').textContent = 'Select a purchase order';
                document.getElementById('orderDate').textContent = 'Select a purchase order';
                document.getElementById('addItemSection').classList.add('hidden');
                return;
            }

            // Update supplier and date info
            const selectedOption = document.getElementById('purchaseOrderSelect').selectedOptions[0];
            document.getElementById('selectedSupplier').textContent = selectedOption.dataset.supplier;
            document.getElementById('orderDate').textContent = selectedOption.dataset.orderDate;

            try {
                // Fetch purchase order items via AJAX
                const response = await fetch(`/api/purchase-orders/${purchaseOrderId}/returnable-items`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Failed to load items');
                }

                purchaseOrderItems = data.data;

                // If changing PO, clear existing items and show add button
                if (purchaseOrderId != originalPurchaseOrderId) {
                    document.getElementById('returnItems').innerHTML = '';
                    document.getElementById('addItemSection').classList.remove('hidden');
                }

                calculateReturnTotals();
                noItemsMessage.classList.add('hidden');

            } catch (error) {
                console.error('Error loading items:', error);
                showNotification('Error loading items. Please try again.', 'error');
            }
        }

        function addNewItem() {
            if (purchaseOrderItems.length === 0) {
                showNotification('Please select a purchase order first.', 'error');
                return;
            }

            const itemId = 'new-item-' + itemCounter++;
            const itemHtml = `
                <div class="return-item border border-gray-200 rounded-lg p-4 bg-gray-50 mb-3" id="${itemId}">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <select name="items[${itemCounter}][purchase_order_item_id]" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent product-select"
                                    onchange="updateProductInfo(this)">
                                <option value="">Select Product</option>
                                ${purchaseOrderItems.map(item => `
                                    <option value="${item.id}"
                                            data-available="${item.available}"
                                            data-unit-cost="${item.unit_cost}"
                                            data-product-name="${item.product_name}"
                                            data-batch="${item.batch_number}">
                                        ${item.product_name} (Available: ${item.available}, Batch: ${item.batch_number || 'N/A'})
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <button type="button" onclick="removeReturnItem(this)"
                                class="ml-3 text-red-600 hover:text-red-700 p-1 rounded hover:bg-red-50">
                            <i class="lni lni-close"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Available Quantity</label>
                            <div class="text-sm font-semibold text-gray-900 available-qty">0</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Return Quantity *</label>
                            <input type="number"
                                   name="items[${itemCounter}][quantity]"
                                   min="1"
                                   step="1"
                                   max="0"
                                   value="0"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent return-quantity"
                                   oninput="calculateItemTotal(this); calculateReturnTotals()"
                                   required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unit Cost (${currencySymbol})</label>
                            <input type="number"
                                   name="items[${itemCounter}][unit_cost]"
                                   step="0.01"
                                   min="0"
                                   value="0"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent return-unit-cost"
                                   oninput="calculateItemTotal(this); calculateReturnTotals()"
                                   required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Total Cost</label>
                            <div class="px-3 py-2 text-sm font-medium bg-gray-100 rounded-lg item-return-total">
                                ${currencySymbol}0.00
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Reason Type</label>
                            <select name="items[${itemCounter}][reason_type]"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="damaged">Damaged</option>
                                <option value="defective">Defective</option>
                                <option value="expired">Expired</option>
                                <option value="wrong_item">Wrong Item</option>
                                <option value="excess_quantity">Excess Quantity</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Product Info</label>
                            <div class="px-3 py-2 text-xs bg-gray-100 rounded-lg product-info">
                                Select a product
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Reason Details</label>
                        <input type="text"
                               name="items[${itemCounter}][reason]"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="Additional details...">
                    </div>
                </div>
            `;

            document.getElementById('returnItems').insertAdjacentHTML('beforeend', itemHtml);
        }

        function updateProductInfo(selectElement) {
            const itemDiv = selectElement.closest('.return-item');
            const selectedOption = selectElement.options[selectElement.selectedIndex];

            if (selectedOption.value) {
                const availableQty = selectedOption.dataset.available;
                const unitCost = selectedOption.dataset.unitCost;
                const productName = selectedOption.dataset.productName;
                const batch = selectedOption.dataset.batch;

                // Update available quantity display
                itemDiv.querySelector('.available-qty').textContent = availableQty;

                // Update quantity input max value
                const quantityInput = itemDiv.querySelector('.return-quantity');
                quantityInput.max = availableQty;
                quantityInput.value = Math.min(parseFloat(quantityInput.value) || 0, parseFloat(availableQty));

                // Update unit cost
                const unitCostInput = itemDiv.querySelector('.return-unit-cost');
                unitCostInput.value = unitCost;

                // Update product info display
                itemDiv.querySelector('.product-info').textContent =
                    `${productName} | Batch: ${batch || 'N/A'}`;

                // Recalculate item total
                calculateItemTotal(quantityInput);
                calculateReturnTotals();
            }
        }

        function calculateItemTotal(inputElement) {
            const itemDiv = inputElement.closest('.return-item');
            const quantity = parseFloat(inputElement.value) || 0;
            const unitCostInput = itemDiv.querySelector('.return-unit-cost');
            const unitCost = parseFloat(unitCostInput.value) || 0;
            const totalElement = itemDiv.querySelector('.item-return-total');

            const total = quantity * unitCost;
            totalElement.textContent = currencySymbol + total.toFixed(2);
        }

        function calculateReturnTotals() {
            let subtotal = 0;

            // Calculate from all return items
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

        function removeReturnItem(button) {
            const itemDiv = button.closest('.return-item');
            const itemId = button.dataset.itemId;

            if (itemId) {
                // For existing items, add a hidden input to mark for deletion
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'deleted_items[]';
                deleteInput.value = itemId;
                document.getElementById('returnForm').appendChild(deleteInput);
            }

            itemDiv.remove();
            calculateReturnTotals();
        }

        function confirmDelete() {
            if (confirm('Are you sure you want to delete this return? This action cannot be undone.')) {
                window.location.href = '{{ route("purchases.returns.destroy", $purchaseReturn) }}';
            }
        }

        // Form submission handler
        document.getElementById('returnForm').addEventListener('submit', function(e) {
            e.preventDefault();

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
            submitButton.innerHTML = '<i class="lni lni-spinner animate-spin mr-2"></i> Updating...';
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
                        showNotification(data.message || 'Return updated successfully!', 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners for all existing quantity and unit cost inputs
            document.querySelectorAll('.return-quantity, .return-unit-cost').forEach(input => {
                input.addEventListener('input', function() {
                    calculateItemTotal(this);
                    calculateReturnTotals();
                });
            });

            // Add event listeners for restocking fee and shipping cost
            document.querySelector('input[name="restocking_fee"]').addEventListener('input', calculateReturnTotals);
            document.querySelector('input[name="shipping_cost"]').addEventListener('input', calculateReturnTotals);
        });
    </script>
@endpush
