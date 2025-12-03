@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ isset($purchase) ? 'Edit Purchase Order' : 'New Purchase Order' }}
                </h1>
                <p class="text-gray-600 mt-1">
                    {{ isset($purchase) ? 'Update purchase order details' : 'Create a new purchase order' }}
                </p>
            </div>
            <div>
                <a href="{{ route('purchases.index') }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Orders
                </a>
            </div>
        </div>

        <form action="{{ isset($purchase) ? route('purchases.update', $purchase) : route('purchases.store') }}"
              method="POST" id="purchaseForm">
            @csrf
            @if(isset($purchase))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Order Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Order Items -->
                    <x-ui.card title="Order Items">
                        <div class="space-y-4">
                            <div id="orderItems">
                                <!-- Items will be added here dynamically -->
                            </div>

                            <button type="button"
                                    onclick="addOrderItem()"
                                    class="w-full border-2 border-dashed border-gray-300 rounded-lg py-4 text-gray-500 hover:text-gray-700 hover:border-gray-400 transition-colors flex items-center justify-center">
                                <i class="lni lni-plus mr-2"></i>
                                Add Product
                            </button>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="space-y-6">
                    <!-- Order Information -->
                    <x-ui.card title="Order Information">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier *</label>
                                <select name="supplier_id" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ (isset($purchase) && $purchase->supplier_id == $supplier->id) || old('supplier_id') == $supplier->id || request('supplier') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Order Date *</label>
                                    <input type="date" name="order_date" required
                                           value="{{ isset($purchase) ? $purchase->order_date->format('Y-m-d') : old('order_date', date('Y-m-d')) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Expected
                                        Delivery</label>
                                    <input type="date" name="expected_delivery_date"
                                           value="{{ isset($purchase) ? ($purchase->expected_delivery_date ? $purchase->expected_delivery_date->format('Y-m-d') : '') : old('expected_delivery_date') }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Order Summary -->
                    <x-ui.card title="Order Summary">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium" id="subtotal">{{ $currency ?? '$' }}0.00</span>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Cost</label>
                                    <input type="number" step="0.01" min="0" name="shipping_cost"
                                           value="{{ isset($purchase) ? $purchase->shipping_cost : old('shipping_cost', 0) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                           onchange="calculateTotals()">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount</label>
                                    <input type="number" step="0.01" min="0" name="discount"
                                           value="{{ isset($purchase) ? $purchase->discount : old('discount', 0) }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                           onchange="calculateTotals()">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="tax_rate"
                                       value="{{ isset($purchase) ? ($purchase->subtotal > 0 ? ($purchase->tax / $purchase->subtotal * 100) : 0) : old('tax_rate', 0) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       onchange="calculateTotals()">
                            </div>

                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between font-semibold text-lg">
                                    <span>Total</span>
                                    <span id="total">{{ $currency ?? '$' }}0.00</span>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Notes -->
                    <x-ui.card title="Notes">
                    <textarea name="notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="Add any notes about this order...">{{ isset($purchase) ? $purchase->notes : old('notes') }}</textarea>
                    </x-ui.card>

                    <!-- Actions -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="window.history.back()"
                                class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                            {{ isset($purchase) ? 'Update Order' : 'Create Order' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hidden field for items -->
            <input type="hidden" name="items" id="itemsData">
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        let orderItems = [];
        let itemCounter = 0;
        const currencySymbol = '{{ $currency ?? "$" }}';

        // Initialize with existing items if editing
        @if(isset($purchase) && $purchase->items)
        @foreach($purchase->items as $item)
        orderItems.push({
            id: {{ $item->id }},
            product_id: {{ $item->product_id }},
            product_name: '{{ $item->product->name }}',
            product_stock: {{ $item->product->stock ?? 0 }},
            quantity: {{ $item->quantity }},
            unit_cost: {{ $item->unit_cost }},
            batch_number: '{{ $item->batch_number }}',
            manufacturing_date: '{{ $item->manufacturing_date ? $item->manufacturing_date->format('Y-m-d') : '' }}',
            expiry_date: '{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '' }}',
            notes: '{{ $item->notes }}'
        });
        @endforeach
        renderOrderItems();
        calculateTotals();
        @endif

        function addOrderItem(productId = '', productName = '', productStock = 0) {
            const itemId = 'item-' + itemCounter++;
            orderItems.push({
                id: null,
                product_id: productId,
                product_name: productName,
                product_stock: productStock,
                quantity: 1,
                unit_cost: 0,
                batch_number: '',
                manufacturing_date: '',
                expiry_date: '',
                notes: ''
            });
            renderOrderItems();
        }

        function removeOrderItem(index) {
            orderItems.splice(index, 1);
            renderOrderItems();
            calculateTotals();
        }

        function updateOrderItem(index, field, value) {
            orderItems[index][field] = value;

            // Update item total cost display
            if (field === 'quantity' || field === 'unit_cost') {
                const totalCostElement = document.querySelector(`.item-total-cost[data-index="${index}"]`);
                if (totalCostElement) {
                    const quantity = parseFloat(orderItems[index].quantity) || 0;
                    const unitCost = parseFloat(orderItems[index].unit_cost) || 0;
                    totalCostElement.textContent = currencySymbol + (quantity * unitCost).toFixed(2);
                }
                calculateTotals();
            }
        }

        function renderOrderItems() {
            const container = document.getElementById('orderItems');
            container.innerHTML = '';

            orderItems.forEach((item, index) => {
                const itemHtml = `
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 mb-3">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent product-select"
                                    onchange="updateProduct(${index}, this.value, this.options[this.selectedIndex].text)"
                                    required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                <option value="{{ $product->id }}"
                                        data-stock="{{ $product->stock }}"
                                        ${item.product_id == {{ $product->id }} ? 'selected' : ''}>
                                    {{ $product->name }} (Stock: {{ $product->stock }})
                                </option>
                                @endforeach
                </select>
            </div>
            <button type="button" onclick="removeOrderItem(${index})"
                                class="ml-3 text-red-600 hover:text-red-700 p-1 rounded hover:bg-red-50">
                            <i class="lni lni-close"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Quantity</label>
                            <input type="number" step="0.01" min="0.01" required
                                   value="${item.quantity}"
                                   oninput="updateOrderItem(${index}, 'quantity', this.value)"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent item-quantity"
                                   data-index="${index}">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unit Cost ({{ $currency ?? '$' }})</label>
                            <input type="number" step="0.01" min="0" required
                                   value="${item.unit_cost}"
                                   oninput="updateOrderItem(${index}, 'unit_cost', this.value)"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent item-unit-cost"
                                   data-index="${index}">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Batch Number</label>
                            <input type="text"
                                   value="${item.batch_number}"
                                   oninput="updateOrderItem(${index}, 'batch_number', this.value)"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Total Cost</label>
                            <div class="px-3 py-2 text-sm font-medium bg-gray-100 rounded-lg item-total-cost" data-index="${index}">
                                ${currencySymbol}${(item.quantity * item.unit_cost).toFixed(2)}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Manufacturing Date</label>
                            <input type="date"
                                   value="${item.manufacturing_date}"
                                   oninput="updateOrderItem(${index}, 'manufacturing_date', this.value)"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label>
                            <input type="date"
                                   value="${item.expiry_date}"
                                   oninput="updateOrderItem(${index}, 'expiry_date', this.value)"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                        <input type="text"
                               value="${item.notes}"
                               oninput="updateOrderItem(${index}, 'notes', this.value)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="Item notes...">
                    </div>
                </div>
            `;
                container.innerHTML += itemHtml;
            });
        }

        function updateProduct(index, productId, productText) {
            const productName = productText.split('(')[0].trim();
            const stockMatch = productText.match(/Stock: (\d+)/);
            const productStock = stockMatch ? parseInt(stockMatch[1]) : 0;

            orderItems[index].product_id = productId;
            orderItems[index].product_name = productName;
            orderItems[index].product_stock = productStock;
        }

        function calculateTotals() {
            let subtotal = 0;

            orderItems.forEach(item => {
                subtotal += (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_cost) || 0);
            });

            const shippingCost = parseFloat(document.querySelector('input[name="shipping_cost"]').value) || 0;
            const discount = parseFloat(document.querySelector('input[name="discount"]').value) || 0;
            const taxRate = parseFloat(document.querySelector('input[name="tax_rate"]').value) || 0;

            const tax = subtotal * (taxRate / 100);
            const total = subtotal + tax + shippingCost - discount;

            document.getElementById('subtotal').textContent = currencySymbol + subtotal.toFixed(2);
            document.getElementById('total').textContent = currencySymbol + total.toFixed(2);
        }

        // Add event listeners for form fields to recalculate totals
        document.addEventListener('DOMContentLoaded', function() {
            const shippingInput = document.querySelector('input[name="shipping_cost"]');
            const discountInput = document.querySelector('input[name="discount"]');
            const taxRateInput = document.querySelector('input[name="tax_rate"]');

            if (shippingInput) {
                shippingInput.addEventListener('input', calculateTotals);
            }
            if (discountInput) {
                discountInput.addEventListener('input', calculateTotals);
            }
            if (taxRateInput) {
                taxRateInput.addEventListener('input', calculateTotals);
            }
        });

        // Form submission handler
        document.getElementById('purchaseForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="lni lni-spinner animate-spin mr-2"></i> Processing...';
            submitButton.disabled = true;

            // Validate form
            if (orderItems.length === 0) {
                alert('Please add at least one item to the order.');
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
                return;
            }

            // Validate all items have product selected
            for (let item of orderItems) {
                if (!item.product_id || item.product_id === '') {
                    alert('Please select a product for all items.');
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                    return;
                }
            }

            // Create FormData object from the form
            const formData = new FormData(this);

            // Add items to FormData as array
            orderItems.forEach((item, index) => {
                formData.append(`items[${index}][product_id]`, item.product_id || '');
                formData.append(`items[${index}][quantity]`, item.quantity || '');
                formData.append(`items[${index}][unit_cost]`, item.unit_cost || '');
                formData.append(`items[${index}][batch_number]`, item.batch_number || '');
                formData.append(`items[${index}][manufacturing_date]`, item.manufacturing_date || '');
                formData.append(`items[${index}][expiry_date]`, item.expiry_date || '');
                formData.append(`items[${index}][notes]`, item.notes || '');
                if (item.id) {
                    formData.append(`items[${index}][id]`, item.id);
                }
            });

            // Submit via fetch
            fetch(this.action, {
                method: this.method,
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.errors) {
                        // Handle validation errors
                        let errorMessages = [];
                        for (const field in data.errors) {
                            errorMessages.push(data.errors[field][0]);
                        }
                        alert('Validation errors:\n' + errorMessages.join('\n'));
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    } else if (data.success) {
                        // Success - redirect to show page
                        window.location.href = data.redirect;
                    } else if (data.redirect) {
                        // Success - redirect
                        window.location.href = data.redirect;
                    } else {
                        // Unknown response
                        console.error('Unknown response:', data);
                        alert('An error occurred. Please try again.');
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                });
        });

        // Add initial item if none exist
        @if(!isset($purchase) && empty($purchase->items))
        if (orderItems.length === 0) {
            addOrderItem();
        }
        @endif

        // Helper function for notifications
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
