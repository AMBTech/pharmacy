@extends('layouts.app')

@push('styles')
    <style>
        /* Search Results Dropdown */
        #productResults {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #E5E7EB;
            border-top: none;
            border-radius: 0 0 0.75rem 0.75rem;
            max-height: 300px;
            overflow-y: auto;
            z-index: 50;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: none;
        }

        #productResults:not(:empty) {
            display: block;
        }

        #productResults li {
            border-bottom: 1px solid #F3F4F6;
        }

        #productResults li:last-child {
            border-bottom: none;
        }

        /* Cart Table Inputs */
        #cartTableBody input[type="number"],
        #cartTableBody select {
            border: 1px solid #D1D5DB;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            width: 100%;
        }

        #cartTableBody input[type="number"]:focus,
        #cartTableBody select:focus {
            outline: none;
            border-color: #3B82F6;
            ring: 2px;
            ring-color: #3B82F6;
        }
    </style>
@endpush

@section('content')
    <div class="h-full flex flex-col">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Point of Sale</h1>
                <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, F j, Y • h:i A') }}</p>
            </div>

            <div class="flex items-center space-x-3">
                <button
                    class="bg-orange-600 cursor-pointer text-white px-4 py-2 rounded-lg font-medium hover:bg-orange-700 transition-colors flex items-center holdInvoiceBtn"
                    id="holdInvoiceBtn">
                    Hold Sale
                </button>
                <button
                    class="bg-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-purple-700 transition-colors flex items-center show-hold-management-btn">
                    <i class="lni lni-timer mr-2"></i>
                    Held Sales
                </button>
                <button
                    class="bg-white border border-gray-300 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 flex items-center transition-colors"
                    id="lastReceiptBtn">
                    <i class="lni lni-printer mr-2 text-gray-600"></i>
                    Last Receipt
                </button>
            </div>
        </div>

        <!-- Main POS Layout - Full Width -->
        <div class="flex-1 flex flex-col gap-6">
            <!-- Products Section with Search and Table -->
            <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-200 p-6 overflow-visible flex flex-col">
                <!-- Search Bar -->
                <div class="mb-4 flex-shrink-0">
                    <!-- Quick Actions Bar -->
                    <div class="flex items-center gap-4 mb-4">
                        <span class="font-light sm:inline">Search: <code>F1</code></span>
                        <span class="font-light sm:inline">Customer Information: <code>F2</code></span>
                        <span class="font-light sm:inline">Hold: <code>F3</code></span>
                        <span class="font-light sm:inline">Last Receipt: <code>F4</code></span>
                        <span class="font-light sm:inline">Apply Discount: <code>F7</code></span>
                        <span class="font-light sm:inline">Clear Cart: <code>Ctrl + Delete</code></span>
                    </div>
                    <div class="relative">
                        <i class="lni lni-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                        <input
                            type="text"
                            id="productSearch"
                            data-pos-role="product-search"
                            placeholder="Search products by name, brand, or generic name... (Press Enter to select)"
                            class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base transition-all"
                        >
                        <!-- Results list (keyboard navigable) -->
                        <ul id="productResults" data-pos-role="product-results" role="listbox" tabindex="-1"
                            class="absolute left-0 right-0 bg-white shadow-xl border border-gray-200 rounded-lg z-[9999] max-h-96 overflow-y-auto"></ul>
                    </div>
                </div>

                <!-- Products Table - Scrollable -->
                <div class="flex-1 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product Name
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                Stock
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                                Qty
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                Rate
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                Total
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                Action
                            </th>
                        </tr>
                        </thead>
                        <tbody id="cartTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Cart items will be added here dynamically -->
                        <tr id="emptyCartRow">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                <i class="lni lni-shopping-basket text-4xl block mb-2"></i>
                                <p class="font-medium">No items in cart</p>
                                <p class="text-sm mt-1">Search and add products to get started</p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cart Summary Section - Sticky at Bottom -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 flex-shrink-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Side: Customer Info -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                            <i class="lni lni-user text-blue-600 mr-2"></i>
                            Customer Information
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Name (Optional)</label>
                                <input type="text" id="customerName" placeholder="Customer Name"
                                       class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                                <input type="tel" id="customerPhone" placeholder="Phone Number"
                                       class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Totals and Actions -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                            <i class="lni lni-calculator text-blue-600 mr-2"></i>
                            Order Summary
                            <span class="ml-auto bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-semibold"
                                  id="cartCount">0 items</span>
                        </h3>

                        <div class="space-y-3">
                            <!-- Subtotal -->
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 font-medium">Subtotal</span>
                                <span class="text-gray-900 font-semibold" id="subtotal">{{$currency_symbol}} 0.00</span>
                            </div>

                            <!-- Discount -->
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs text-gray-700 font-medium">Discount</span>
                                    <div class="flex items-center space-x-1">
                                        <button
                                            class="discount-type px-2 py-1 text-xs font-semibold rounded transition-all"
                                            data-type="fixed">{{ $currency_symbol }}
                                        </button>
                                        <button
                                            class="discount-type px-2 py-1 text-xs font-semibold rounded bg-blue-600 text-white transition-all"
                                            data-type="percentage">%
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <input type="number" id="discountInput" placeholder="0"
                                           class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           value="0.0">
                                    <p class="text-xs text-gray-500 mt-1">Press Enter to apply.</p>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-2" id="discountAmount"
                                     style="display: none;">
                                    <span>Discount Applied</span>
                                    <span class="font-semibold text-red-600" id="discountValue">-{{ $currency_symbol }} 0.00</span>
                                </div>
                            </div>

                            <!-- Tax -->
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 font-medium">Tax ({{$settings['tax_rate']}}%)</span>
                                <span class="text-gray-900 font-semibold" id="tax">{{ $currency_symbol }} 0.00</span>
                            </div>

                            <!-- Total -->
                            <div
                                class="flex justify-between items-center bg-gradient-to-r from-blue-50 to-indigo-50 p-3 rounded-lg border-2 border-blue-200">
                                <span class="text-lg font-bold text-gray-900">Total</span>
                                <span class="text-2xl font-extrabold text-blue-600" id="total">{{ $currency_symbol }} 0.00</span>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <p class="text-xs font-semibold text-gray-700 mb-2">Payment Method</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        class="payment-method px-3 py-2 rounded-lg text-xs font-semibold transition-all bg-blue-600 text-white shadow-sm"
                                        data-method="cash">
                                        <i class="lni lni-money-location block text-lg mb-1"></i>
                                        Cash
                                    </button>
                                    <button
                                        class="payment-method px-3 py-2 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-700 hover:bg-gray-200"
                                        data-method="card">
                                        <i class="lni lni-credit-cards block text-lg mb-1"></i>
                                        Card
                                    </button>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <button
                                    class="bg-gradient-to-r from-green-600 to-green-700 text-white py-3 rounded-xl font-bold hover:from-green-700 hover:to-green-800 hover:shadow-lg transition-all complete-sale-btn">
                                    <i class="lni lni-checkmark mr-1"></i>
                                    Complete
                                </button>
                                <button
                                    class="bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-50 hover:border-gray-400 transition-all"
                                    id="clearCart">
                                    <i class="lni lni-close mr-1"></i>
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
            <div class="bg-gradient-to-r from-green-600 to-green-700 p-6 rounded-t-2xl">
                <h3 class="text-2xl font-bold text-white flex items-center">
                    <i class="lni lni-checkmark-circle mr-3 text-3xl"></i>
                    Complete Sale
                </h3>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 p-4 rounded-xl mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Total Amount</span>
                        <span class="text-3xl font-bold text-green-600" id="modalTotal">{{ $currency_symbol }} 0.00</span>
                    </div>
                    <div class="text-sm text-gray-500" id="modalItemCount">0 items</div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Method</label>
                    <div class="text-lg font-semibold text-blue-600 bg-blue-50 p-3 rounded-lg" id="modalPaymentMethod">
                        Cash
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Amount Received</label>
                    <input type="number" id="amountReceived"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-lg font-semibold focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="0.00">
                </div>

                <div class="bg-green-50 p-4 rounded-xl mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Change to Return</span>
                        <span class="text-2xl font-bold text-green-600" id="changeAmount">{{ $currency_symbol }} 0.00</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button
                        class="bg-gray-100 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-200 transition-colors"
                        id="cancelPayment">
                        Cancel
                    </button>
                    <button
                        class="bg-gradient-to-r from-green-600 to-green-700 text-white py-3 rounded-xl font-bold hover:from-green-700 hover:to-green-800 transition-all"
                        id="confirmPayment">
                        Confirm Sale
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Hold Management Modal -->
    <div id="holdManagementModal" class="fixed inset-0 bg-gray-600/50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Held Sales Management</h3>
                <button class="text-gray-400 hover:text-gray-600" id="closeHeldSaleModal">
                    <i class="lni lni-close text-2xl"></i>
                </button>
            </div>

            <div id="heldSalesList" class="space-y-4 max-h-96 overflow-y-auto">
                <!-- Held sales will be loaded here -->
            </div>

            <div class="mt-6 flex justify-end">
                <button id="closeHeldSaleModalBtn"
                        class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const CURRENCY_SYMBOL = '{{ $currency_symbol }}';
        class POSCart {
            self = this;

            constructor() {
                this.cart = [];
                this.discountType = 'percentage';
                this.discountValue = 0;
                this.paymentMethod = 'cash';
                this.currentCategory = 'all';
                this.searchTimeout = null;
                this.init();
            }

            init() {
                this.bindEvents();
                this.loadCartFromStorage();
                this.updateCartDisplay();
            }

            bindEvents() {
                // Product card clicks
                document.querySelectorAll('.product-card').forEach(card => {
                    card.addEventListener('click', (e) => {
                        if (!e.target.closest('.add-to-cart-btn')) {
                            this.addToCart(card.dataset);
                        }
                    });
                });

                // Add to cart buttons
                document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const card = btn.closest('.product-card');
                        this.addToCart(card.dataset);
                    });
                });

                // Category filters
                document.querySelectorAll('.category-filter').forEach(btn => {
                    btn.addEventListener('click', () => {
                        this.filterByCategory(btn.dataset.category);
                        document.querySelectorAll('.category-filter').forEach(b => {
                            b.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
                            b.classList.add('bg-gray-100', 'text-gray-700');
                        });
                        btn.classList.remove('bg-gray-100', 'text-gray-700');
                        btn.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
                    });
                });

                // Sort products
                /*document.getElementById('sortProducts').addEventListener('change', (e) => {
                    this.sortProducts(e.target.value);
                });*/

                // Discount type toggle
                document.querySelectorAll('.discount-type').forEach(btn => {
                    btn.addEventListener('click', () => {
                        this.setDiscountType(btn.dataset.type);
                    });
                });

                // Apply discount on Enter (button removed)
                const discountEl = document.getElementById('discountInput');
                if (discountEl) {
                    discountEl.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            this.applyDiscount();
                        }
                    });
                }

                // Payment method selection
                document.querySelectorAll('.payment-method').forEach(btn => {
                    btn.addEventListener('click', () => {
                        this.selectPaymentMethod(btn.dataset.method);
                    });
                });

                // Clear cart
                document.getElementById('clearCart').addEventListener('click', () => {
                    this.clearCart();
                });

                // Complete sale
                document.querySelector('.complete-sale-btn').addEventListener('click', () => {
                    this.showPaymentModal();
                });

                // Quick cash
                document.querySelectorAll('.quick-cash').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const total = this.calculateTotal();
                        document.getElementById('amountReceived').value = btn.dataset.amount;
                        this.updateChange();
                    });
                });

                // Search with debouncing
                document.getElementById('productSearch').addEventListener('input', (e) => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.filterProducts(e.target.value);
                    }, 300);
                });

                // Quick Actions bindings
                const qa = id => document.getElementById(id);
                qa('qaFocusSearch')?.addEventListener('click', () => {
                    const el = document.getElementById('productSearch');
                    if (el) {
                        el.focus();
                        el.select();
                    }
                });
                qa('qaHoldSale')?.addEventListener('click', () => this.holdSale());
                qa('qaHeldSales')?.addEventListener('click', () => this.showHoldManagement());
                qa('qaClearCart')?.addEventListener('click', () => this.clearCart());
                qa('qaCompleteSale')?.addEventListener('click', () => this.showPaymentModal());
                qa('qaLastReceipt')?.addEventListener('click', () => document.getElementById('lastReceiptBtn')?.click());

                // Payment modal events
                document.getElementById('cancelPayment').addEventListener('click', () => {
                    this.hidePaymentModal();
                });

                document.getElementById('confirmPayment').addEventListener('click', () => {
                    this.confirmSale();
                });

                document.getElementById('amountReceived').addEventListener('input', () => {
                    this.updateChange();
                });

                // Enter key on amountReceived to confirm payment
                document.getElementById('amountReceived').addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('confirmPayment').click();
                    }
                });

                // In your POS initialization
                document.querySelector('.holdInvoiceBtn').addEventListener('click', () => {
                    this.holdSale();
                });

                document.querySelector('.show-hold-management-btn').addEventListener('click', () => {
                    this.showHoldManagement();
                });

                // Close modal when clicking outside
                document.getElementById('holdManagementModal').addEventListener('click', (e) => {
                    if (e.target.id === 'holdManagementModal') {
                        this.hideHoldManagement();
                    }
                });

                // Close modal when clicking cross button or close button
                document.getElementById('closeHeldSaleModal').addEventListener('click', (e) => {
                    this.hideHoldManagement();
                });
                document.getElementById('closeHeldSaleModalBtn').addEventListener('click', (e) => {
                    this.hideHoldManagement();
                });
            }

            // Example modification to addToCart
//             async addToCart(product, qty = 1, opts = {}) {
// // existing product lookup
//                 const line = {
//                     product_id: product.id,
//                     product_name: product.name,
//                     qty,
//                     price: product.price,
//                     allocations: []
//                 };
//
//
//                 try {
//                     const {allocations, remaining} = await this.allocateBatches(product.id, qty);
//                     line.allocations = allocations; // store allocations per line
//
//
//                     if (remaining > 0) {
// // handle partial allocation per your business rule
// // e.g., alert the user or allow backorder
//                         console.warn('Not enough stock to fully allocate. Remaining:', remaining);
//                     }
//
//
//                     cart.push(line);
//                     renderCart();
//                 } catch (err) {
//                     console.error(err);
//                     alert('Could not allocate batches for this product.');
//                 }
//             }

            async addToCart(productData) {
                const existingItem = this.cart.find(item => item.id === productData.productId);
                let isNewItem = false;

                if (existingItem) {
                    if (existingItem.quantity >= parseInt(productData.productStock)) {
                        this.showNotification('Insufficient stock!', 'error');
                        return;
                    }
                    existingItem.quantity++;
                } else {
                    if (parseInt(productData.productStock) < 1) {
                        this.showNotification('Product out of stock!', 'error');
                        return;
                    }
                    let line = {
                        id: productData.productId,
                        name: productData.productName,
                        price: parseFloat(productData.productPrice),
                        unit: productData.productUnit,
                        stock: parseInt(productData.productStock),
                        quantity: 1
                    };

                    const {allocations, remaining} = await this.allocateBatches(productData.productId, 1);
                    line.allocations = allocations; // store allocations per line

                    if (remaining > 0) {
                        showNotification('Not enough stock to fully allocate.', 'error');
                    }

                    this.cart.push(line);

                    console.log("cart", this.cart);

                    isNewItem = true;
                }

                this.saveCartToStorage();
                this.updateCartDisplay(isNewItem); // Focus on new item if it's new
                this.calculateTotals();
                this.showNotification('✓ Added to cart', 'success');
            }

            removeFromCart(productId) {
                // Robust compare (string/number)
                this.cart = this.cart.filter(item => String(item.id) !== String(productId));
                this.updateCart();
            }

            updateQuantity(productId, newQuantity) {
                const item = this.cart.find(item => item.id === productId);
                if (item) {
                    if (newQuantity < 1) {
                        this.removeFromCart(productId);
                        return;
                    }
                    if (newQuantity > item.stock) {
                        this.showNotification('Insufficient stock!', 'error');
                        return;
                    }
                    item.quantity = newQuantity;
                    this.updateCart();
                }
            }

            updateCart() {
                this.saveCartToStorage();
                this.updateCartDisplay();
                this.calculateTotals();
            }

            updateCartDisplay(focusOnNewItem = false) {
                const cartTableBody = document.getElementById('cartTableBody');
                const cartCount = document.getElementById('cartCount');
                const emptyCartRow = document.getElementById('emptyCartRow');

                // Update cart count badge
                cartCount.textContent = `${this.cart.length} item${this.cart.length !== 1 ? 's' : ''}`;

                // Clear table body
                cartTableBody.innerHTML = '';

                if (this.cart.length === 0) {
                    // Show empty cart row
                    cartTableBody.innerHTML = `
                        <tr id="emptyCartRow">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                <i class="lni lni-shopping-basket text-4xl block mb-2"></i>
                                <p class="font-medium">No items in cart</p>
                                <p class="text-sm mt-1">Search and add products to get started</p>
                            </td>
                        </tr>
                    `;
                    return;
                }

                let lastAddedRow = null;

                // Add each cart item as a table row
                this.cart.forEach((item, index) => {
                    console.log(item);
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    row.dataset.productId = item.id;

                    const itemTotal = item.price * item.quantity;

                    row.innerHTML = `
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">${this.escapeHtml(item.name)}</div>
                            <div class="text-xs text-gray-500">${this.escapeHtml(item.unit)}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="stock">
                                ${item.stock}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" min="1" value="${item.quantity}" id="qty-input"
                                    class="qty-input px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    data-product-id="${item.id}" />
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" min="0" step="0.01" value="${item.price.toFixed(2)}"
                                   class="rate-input px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled data-product-id="${item.id}" />
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 item-total" data-product-id="${item.id}">
                            {{ $currency_symbol }} ${itemTotal.toFixed(2)}
                        </td>
                        <td class="px-6 py-4">
                            <button class="text-red-600 hover:text-red-800 remove-item" data-product-id="${item.id}">
                                <i class="lni lni-trash-can text-lg"></i>
                            </button>
                        </td>
                    `;

                    cartTableBody.appendChild(row);

                    // Populate batches for this item (async)
                    this.loadBatchesForItem(item, row);

                    // Track the last added row (most recent item)
                    if (index === this.cart.length - 1) {
                        lastAddedRow = row;
                    }
                });

                // Bind events for all inputs and buttons
                this.bindCartEvents();

                // Focus on qty input of newly added item
                if (focusOnNewItem && lastAddedRow) {
                    const qtyInput = lastAddedRow.querySelector('.qty-input');
                    if (qtyInput) {
                        setTimeout(() => {
                            qtyInput.focus();
                            qtyInput.select();
                        }, 100);
                    }
                }
            }

            bindCartEvents() {
                // Quantity inputs
                document.querySelectorAll('.qty-input').forEach(input => {
                    const commitQty = (el) => {
                        const productId = el.dataset.productId;
                        const row = el.closest('tr');
                        const item = this.cart.find(i => String(i.id) === String(productId));
                        if (!item) return;
                        let newQty = parseInt(el.value) || 1;
                        if (newQty < 1) newQty = 1;
                        if (item.stock && newQty > item.stock) {
                            this.showNotification('Insufficient stock!', 'error');
                            newQty = item.stock;
                        }
                        el.value = newQty;
                        item.quantity = newQty;
                        const cell = row.querySelector('.item-total');
                        if (cell) cell.textContent = `${CURRENCY_SYMBOL} ${(item.price * item.quantity).toFixed(2)}`;
                        this.saveCartToStorage();
                        this.calculateTotals();
                    };

                    input.addEventListener('input', (e) => commitQty(e.target));
                    input.addEventListener('change', (e) => commitQty(e.target));

                    // Keyboard navigation and delete
                    input.addEventListener('keydown', (e) => {
                        const productId = e.target.dataset.productId;
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            commitQty(e.target);
                            const row = e.target.closest('tr');
                            const batchSelect = row.querySelector('.batch-select');
                            if (batchSelect && batchSelect.querySelector('option:not([value=""])')) {
                                batchSelect.focus();
                            } else {
                                // const rateInput = row.querySelector('.rate-input');
                                // if (rateInput) rateInput.focus();

                                const searchInput = document.getElementById('productSearch');
                                if (searchInput) {
                                    searchInput.focus();
                                    searchInput.select();
                                }

                            }
                        } else if (e.key === 'Delete' || (e.key === 'Backspace' && e.ctrlKey)) {
                            e.preventDefault();
                            this.removeFromCart(productId);
                        }
                    });
                });

                // Batch selects
                document.querySelectorAll('.batch-select').forEach(select => {
                    select.addEventListener('change', (e) => {
                        const productId = e.target.dataset.productId;
                        const item = this.cart.find(i => String(i.id) === String(productId));
                        if (item) {
                            item.batchId = e.target.value || null;
                            this.saveCartToStorage();
                        }
                    });
                    select.addEventListener('keydown', (e) => {
                        const productId = e.target.dataset.productId;
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const row = e.target.closest('tr');
                            // const rateInput = row.querySelector('.rate-input');
                            // if (rateInput) rateInput.focus();
                        } else if (e.key === 'Delete' || (e.key === 'Backspace' && e.ctrlKey)) {
                            e.preventDefault();
                            this.removeFromCart(productId);
                        }
                    });
                });

                // Rate inputs
                document.querySelectorAll('.rate-input').forEach(input => {
                    const commitRate = (el) => {
                        const productId = el.dataset.productId;
                        const row = el.closest('tr');
                        const item = this.cart.find(i => String(i.id) === String(productId));
                        if (!item) return;
                        item.price = parseFloat(el.value) || 0;
                        const cell = row.querySelector('.item-total');
                        if (cell) cell.textContent = `${CURRENCY_SYMBOL} ${(item.price * item.quantity).toFixed(2)}`;
                        this.saveCartToStorage();
                        this.calculateTotals();
                    };

                    input.addEventListener('input', (e) => commitRate(e.target));
                    input.addEventListener('change', (e) => commitRate(e.target));

                    input.addEventListener('keydown', (e) => {
                        const productId = e.target.dataset.productId;
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            commitRate(e.target);
                            this.focusSearch();
                        } else if (e.key === 'Delete' || (e.key === 'Backspace' && e.ctrlKey)) {
                            e.preventDefault();
                            this.removeFromCart(productId);
                        }
                    });
                });

                // Remove buttons
                document.querySelectorAll('.remove-item').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const productId = e.currentTarget.dataset.productId;
                        this.removeFromCart(productId);
                    });
                });
            }

            focusSearch()
            {
                const searchInput = document.getElementById('productSearch');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Populate batch select for a cart item
            async loadBatchesForItem(item, row) {
                try {
                    const select = row.querySelector('.batch-select');
                    if (!select) return;
                    // If already populated (more than placeholder), skip
                    if (select.options.length > 1) return;

                    const res = await fetch(`/inventory/api/${item.id}/batches/list`, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
                    if (!res.ok) return;
                    const data = await res.json();
                    const batches = Array.isArray(data?.batches) ? data.batches : (Array.isArray(data) ? data : []);
                    console.log("batches", batches);

                    if (batches.length) {
                        select.innerHTML = '<option value="">Select</option>';
                        batches.forEach(b => {
                            const opt = document.createElement('option');
                            opt.value = b.id ?? b.batch_id ?? b.code ?? '';
                            const labelParts = [];
                            if (b.code || b.batch_number) labelParts.push(b.code || b.batch_number);
                            if (b.expiry) labelParts.push(`Exp ${b.expiry}`);
                            const stock = (b.stock ?? b.qty);
                            if (stock != null) labelParts.push(`Stock ${stock}`);
                            opt.textContent = labelParts.join(' • ') || 'Batch';
                            select.appendChild(opt);
                        });
                        if (item.batchId) select.value = item.batchId;
                    }
                } catch (e) {
                    console.warn('Failed loading batches for', item.id, e);
                }
            }

            calculateTotals() {
                const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                const taxPercentage = {{$settings['tax_rate']}} / 100;

                const tax = subtotal * taxPercentage;
                let discount = 0;

                if (this.discountType === 'percentage') {
                    discount = subtotal * (this.discountValue / 100);
                } else {
                    discount = this.discountValue;
                }

                const total = Math.max(0, subtotal + tax - discount);

                document.getElementById('subtotal').textContent = `${CURRENCY_SYMBOL} ${subtotal.toFixed(2)}`;
                document.getElementById('tax').textContent = `${CURRENCY_SYMBOL} ${tax.toFixed(2)}`;
                document.getElementById('total').textContent = `${CURRENCY_SYMBOL} ${total.toFixed(2)}`;

                // Show/hide discount amount
                if (discount > 0) {
                    document.getElementById('discountAmount').style.display = 'flex';
                    document.getElementById('discountValue').textContent = `-${CURRENCY_SYMBOL} ${discount.toFixed(2)}`;
                } else {
                    document.getElementById('discountAmount').style.display = 'none';
                }
            }

            calculateTotal() {
                const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                const taxPercentage = {{$settings['tax_rate']}} / 100;
                const tax = subtotal * taxPercentage;
                let discount = 0;

                if (this.discountType === 'percentage') {
                    discount = subtotal * (this.discountValue / 100);
                } else {
                    discount = this.discountValue;
                }

                return Math.max(0, subtotal + tax - discount);
            }

            setDiscountType(type) {
                this.discountType = type;
                document.querySelectorAll('.discount-type').forEach(btn => {
                    if (btn.dataset.type === type) {
                        btn.classList.add('bg-blue-600', 'text-white');
                        btn.classList.remove('bg-white', 'text-gray-700');
                    } else {
                        btn.classList.remove('bg-blue-600', 'text-white');
                        btn.classList.add('bg-white', 'text-gray-700');
                    }
                });
            }

            applyDiscount() {
                const input = document.getElementById('discountInput');
                this.discountValue = parseFloat(input.value) || 0;

                if (this.discountValue < 0) {
                    this.showNotification('Invalid discount value', 'error');
                    this.discountValue = 0;
                    input.value = 0;
                    return;
                }

                this.calculateTotals();
                if (this.discountValue > 0) {
                    this.showNotification(`Discount of ${this.discountType === 'percentage' ? this.discountValue + '%' : `${CURRENCY_SYMBOL} ` + this.discountValue} applied`, 'success');
                }
            }

            selectPaymentMethod(method) {
                this.paymentMethod = method;
                document.querySelectorAll('.payment-method').forEach(btn => {
                    if (btn.dataset.method === method) {
                        btn.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
                        btn.classList.remove('bg-gray-100', 'text-gray-700');
                    } else {
                        btn.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
                        btn.classList.add('bg-gray-100', 'text-gray-700');
                    }
                });
            }

            clearCart(notify = true) {
                if (this.cart.length === 0) return;

                if (confirm('Are you sure you want to clear the cart?')) {
                    this.cart = [];
                    this.discountValue = 0;
                    document.getElementById('discountInput').value = '0';
                    this.updateCart();
                    if (notify)
                        this.showNotification('🗑️ Cart cleared', 'success');
                }
            }

            showPaymentModal() {
                if (this.cart.length === 0) {
                    this.showNotification('Cart is empty!', 'error');
                    return;
                }

                const modal = document.getElementById('paymentModal');
                const total = this.calculateTotal();

                document.getElementById('modalTotal').textContent = `${CURRENCY_SYMBOL} ${total.toFixed(2)}`;
                document.getElementById('modalItemCount').textContent = `${this.cart.length} item${this.cart.length > 1 ? 's' : ''}`;
                document.getElementById('modalPaymentMethod').textContent = this.paymentMethod.charAt(0).toUpperCase() + this.paymentMethod.slice(1);
                document.getElementById('amountReceived').value = '';
                document.getElementById('changeAmount').textContent = `${CURRENCY_SYMBOL} 0.00`;

                modal.style.display = 'flex';
                setTimeout(() => modal.querySelector('.bg-white').classList.add('scale-100'), 10);
            }

            hidePaymentModal() {
                const modal = document.getElementById('paymentModal');
                modal.style.display = 'none';
            }

            updateChange() {
                const total = this.calculateTotal();
                const received = parseFloat(document.getElementById('amountReceived').value) || 0;
                const change = Math.max(0, received - total);
                document.getElementById('changeAmount').textContent = `${CURRENCY_SYMBOL} ${change.toFixed(2)}`;
            }

            async confirmSale() {
                const taxPercentage = {{$settings['tax_rate']}} / 100;
                const total = this.calculateTotal();
                const received = parseFloat(document.getElementById('amountReceived').value) || 0;

                if (received < total) {
                    this.showNotification('Insufficient amount received!', 'error');
                    return;
                }

                try {
                    // Prepare sale data for API
                    const saleData = {
                        items: this.cart.map(item => ({
                            product_id: item.id,
                            name: item.name,
                            price: item.price,
                            quantity: item.quantity
                        })),
                        subtotal: this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0),
                        discount: this.discountValue,
                        discountType: this.discountType,
                        tax: this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) * taxPercentage,
                        total: total,
                        payment_method: this.paymentMethod,
                        amountReceived: received,
                        change: received - total,
                        customerName: document.getElementById('customerName').value,
                        customerPhone: document.getElementById('customerPhone').value
                    };

                    console.log("sale data", saleData);

                    // Call the API
                    const response = await fetch('/sales/complete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(saleData)
                    });

                    const result = await response.json();

                    console.log("result==>>", result);

                    if (result.success) {
                        localStorage.setItem('lastReceipt', JSON.stringify({
                            id: result.sale_id,
                            invoice_number: result.invoice_number,
                            items: this.cart,
                            total: total,
                            timestamp: new Date().toISOString()
                        }));
                        this.hidePaymentModal();
                        this.showNotification('✓ Sale completed successfully!', 'success');

                        // Optional: Print receipt or show success details
                        if (result.invoice_number) {
                            setTimeout(() => {
                                window.open(`/sales/${result.sale_id}/print`, '_blank');
                            }, 1000);
                        }

                        // Reset everything
                        this.cart = [];
                        this.discountValue = 0;
                        document.getElementById('discountInput').value = '0';
                        document.getElementById('customerName').value = '';
                        document.getElementById('customerPhone').value = '';
                        this.updateCart();
                    } else {
                        this.showNotification(result.message || 'Sale failed!', 'error');
                    }

                } catch (error) {
                    console.error('Error completing sale:', error);
                    this.showNotification('Error completing sale. Please try again.', 'error');
                }
            }

            // holdSale() {
            //     if (this.cart.length === 0) {
            //         this.showNotification('Cart is empty!', 'error');
            //         return;
            //     }
            //
            //     // Save to localStorage with timestamp
            //     const heldSales = JSON.parse(localStorage.getItem('heldSales') || '[]');
            //     heldSales.push({
            //         cart: this.cart,
            //         discount: this.discountValue,
            //         discountType: this.discountType,
            //         timestamp: new Date().toISOString()
            //     });
            //     localStorage.setItem('heldSales', JSON.stringify(heldSales));
            //
            //     this.showNotification('💾 Sale held successfully', 'success');
            //     this.cart = [];
            //     this.discountValue = 0;
            //     document.getElementById('discountInput').value = '0';
            //     this.updateCart();
            // }

            filterProducts(searchTerm) {
                // const products = document.querySelectorAll('.product-card');
                // const term = searchTerm.toLowerCase();

                /*
                                products.forEach(product => {
                                    const name = product.dataset.productName.toLowerCase();
                                    const category = product.dataset.productCategory || '';
                                    const unit = (product.dataset.productUnit).toLowerCase() || '';

                                    console.log("category", unit, this.currentCategory);
                                    const matchesSearch = !term || name.includes(term);
                                    const matchesCategory = this.currentCategory === 'all' || unit === this.currentCategory;

                                    product.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
                                });
                */
            }

            filterByCategory(category) {
                this.currentCategory = category;
                const searchTerm = document.getElementById('productSearch').value;
                this.filterProducts(searchTerm);
            }

            sortProducts(sortType) {
                const container = document.querySelector('.grid.grid-cols-1');
                const products = Array.from(container.querySelectorAll('.product-card'));

                products.sort((a, b) => {
                    const [criteria, order] = sortType.split('-');
                    let aVal, bVal;

                    switch (criteria) {
                        case 'name':
                            aVal = a.dataset.productName.toLowerCase();
                            bVal = b.dataset.productName.toLowerCase();
                            break;
                        case 'price':
                            aVal = parseFloat(a.dataset.productPrice);
                            bVal = parseFloat(b.dataset.productPrice);
                            break;
                        case 'stock':
                            aVal = parseInt(a.dataset.productStock);
                            bVal = parseInt(b.dataset.productStock);
                            break;
                    }

                    if (order === 'asc') {
                        return aVal > bVal ? 1 : -1;
                    } else {
                        return aVal < bVal ? 1 : -1;
                    }
                });

                products.forEach(product => container.appendChild(product));
            }

            showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.style.cssText = 'z-index: 9999; animation: slideInRight 0.3s ease-out;';
                notification.className = `fixed top-4 right-4 p-4 rounded-xl shadow-2xl text-white font-semibold flex items-center space-x-3 transform transition-all ${
                    type === 'success' ? 'bg-gradient-to-r from-green-500 to-green-600' :
                        type === 'error' ? 'bg-gradient-to-r from-red-500 to-red-600' :
                            'bg-gradient-to-r from-blue-500 to-blue-600'
                }`;

                const icon = type === 'success' ? 'checkmark-circle' : type === 'error' ? 'close-circle' : 'information';
                notification.innerHTML = `
                    <i class="lni lni-${icon} text-2xl"></i>
                    <span>${message}</span>
                `;

                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.style.animation = 'slideOutRight 0.3s ease-in';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }

            saveCartToStorage() {
                localStorage.setItem('posCart', JSON.stringify(this.cart));
            }

            loadCartFromStorage() {
                const saved = localStorage.getItem('posCart');
                if (saved) {
                    this.cart = JSON.parse(saved);
                }
            }

            // Complete sale methods
            // Sale completion function
            async completeSalePost(cartData, customerInfo, paymentMethod, discount = 0) {
                try {
                    const response = await fetch('{{ route("sales.complete") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            items: cartData,
                            customer_name: customerInfo.name || '',
                            customer_phone: customerInfo.phone || '',
                            payment_method: paymentMethod,
                            discount_amount: discount,
                            notes: customerInfo.notes || ''
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        return data;
                    } else {
                        throw new Error(data.message || 'Sale completion failed');
                    }
                } catch (error) {
                    console.error('Error completing sale:', error);
                    throw error;
                }
            }

            // Update your POSCart completeSale method:
            // In your existing POSCart class, replace the completeSale method with:

            async completeSale() {
                if (this.cart.length === 0) {
                    this.showNotification('Cart is empty!', 'error');
                    return;
                }

                // Show payment modal or collect customer info
                const paymentMethod = await this.collectPaymentInfo();
                if (!paymentMethod) return;

                try {
                    const saleData = {
                        items: this.cart.map(item => ({
                            product_id: item.id,
                            quantity: item.quantity
                        })),
                        customerInfo: {
                            name: document.getElementById('customerName')?.value || '',
                            phone: document.getElementById('customerPhone')?.value || '',
                            notes: document.getElementById('saleNotes')?.value || ''
                        },
                        paymentMethod: paymentMethod,
                        discount: this.discountValue
                    };

                    const result = await this.completeSalePost(saleData.items, saleData.customerInfo, saleData.paymentMethod, saleData.discount);

                    this.showNotification('Sale completed successfully!', 'success');

                    // Show receipt or redirect
                    if (result.sale_id) {
                        setTimeout(() => {
                            window.open(`/sales/${result.sale_id}/print`, '_blank');
                        }, 1000);
                    }

                    this.clearCart();
                } catch (error) {
                    this.showNotification(error.message || 'Error completing sale!', 'error');
                }
            }

            // Simple payment method collection
            collectPaymentInfo() {
                return new Promise((resolve) => {
                    const method = prompt('Select payment method:\n1. Cash\n2. Card\n3. Digital\n\nEnter 1, 2, or 3:');

                    switch (method) {
                        case '1':
                            resolve('cash');
                            break;
                        case '2':
                            resolve('card');
                            break;
                        case '3':
                            resolve('digital');
                            break;
                        default:
                            this.showNotification('Invalid payment method!', 'error');
                            resolve(null);
                    }
                });
            }

            // Complete sale methods

            // Hold current sale
            async holdSale() {
                if (this.cart.length === 0) {
                    this.showNotification('Cart is empty! Nothing to hold.', 'error');
                    return;
                }

                const customerName = document.getElementById('customerName')?.value || '';
                const customerPhone = document.getElementById('customerPhone')?.value || '';
                const notes = document.getElementById('saleNotes')?.value || '';
                const taxPercentage = {{$settings['tax_rate']}} / 100;
                const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

                const holdData = {
                    cart: this.cart,
                    subtotal: subtotal,
                    tax: taxPercentage,
                    discount: this.discountValue,
                    discountType: this.discountType,
                    total: this.calculateTotal(),
                    customerName: customerName,
                    customerPhone: customerPhone,
                    notes: notes
                };

                try {
                    const response = await fetch('/pos/hold-sale', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(holdData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showNotification(`Sale held successfully! Hold ID: ${result.hold_id}`, 'success');

                        // Clear current cart after successful hold
                        this.clearCart();

                        // Show hold management modal
                        this.showHoldManagement();
                    } else {
                        this.showNotification(result.message || 'Failed to hold sale!', 'error');
                    }
                } catch (error) {
                    console.error('Error holding sale:', error);
                    this.showNotification('Error holding sale. Please try again.', 'error');
                }
            }

            // Release held sale into current cart
            async releaseSale(holdId) {
                try {
                    const response = await fetch(`/pos/release-sale/${holdId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Load the held cart data into current cart
                        this.cart = result.cart_data.cart || [];
                        this.discountValue = result.cart_data.discount || 0;
                        this.discountType = result.cart_data.discountType || 'percentage';

                        // Populate customer fields
                        if (document.getElementById('customerName')) {
                            document.getElementById('customerName').value = result.cart_data.customerName || '';
                        }
                        if (document.getElementById('customerPhone')) {
                            document.getElementById('customerPhone').value = result.cart_data.customerPhone || '';
                        }
                        if (document.getElementById('saleNotes')) {
                            document.getElementById('saleNotes').value = result.cart_data.notes || '';
                        }

                        this.updateCart();
                        this.showNotification('Sale released successfully!', 'success');
                        this.hideHoldManagement();
                    } else {
                        this.showNotification(result.message || 'Failed to release sale!', 'error');
                    }
                } catch (error) {
                    console.error('Error releasing sale:', error);
                    this.showNotification('Error releasing sale. Please try again.', 'error');
                }
            }

            // Delete held sale
            async deleteHeldSale(holdId) {
                if (!confirm('Are you sure you want to delete this held sale? This action cannot be undone.')) {
                    return;
                }

                try {
                    const response = await fetch(`/pos/delete-held-sale/${holdId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showNotification('Held sale deleted successfully!', 'success');
                        this.loadHeldSales(); // Refresh the list
                    } else {
                        this.showNotification(result.message || 'Failed to delete held sale!', 'error');
                    }
                } catch (error) {
                    console.error('Error deleting held sale:', error);
                    this.showNotification('Error deleting held sale. Please try again.', 'error');
                }
            }

            // Load held sales for management
            async loadHeldSales() {
                try {
                    const response = await fetch('/pos/held-sales');
                    const result = await response.json();

                    if (result.success) {
                        this.displayHeldSales(result.held_sales);
                    }
                } catch (error) {
                    console.error('Error loading held sales:', error);
                }
            }

            // Display held sales in management modal
            displayHeldSales(heldSales) {
                const container = document.getElementById('heldSalesList');
                if (!container) return;

                if (heldSales.length === 0) {
                    container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="lni lni-inbox text-4xl mb-2"></i>
                    <p>No held sales found</p>
                </div>
            `;
                    return;
                }

                container.innerHTML = heldSales.map(held => `
            <div class="border border-gray-200 rounded-lg p-4 hover:border-primary-300 transition-colors ${held.is_expired ? 'opacity-60' : ''}">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-semibold text-gray-900">${held.hold_id}</h4>
                        <p class="text-sm text-gray-600">Held: ${new Date(held.held_at).toLocaleTimeString()}</p>
                        ${held.customer_name ? `<p class="text-sm text-gray-600">Customer: ${held.customer_name}</p>` : ''}
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-gray-900">${CURRENCY_SYMBOL} ${parseFloat(held.total_amount).toFixed(2)}</p>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${held.is_expired ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                            ${held.is_expired ? 'Expired' : 'Active'}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 mb-3">
                    <div>Items: ${held.cart_data.cart.length}</div>
                    <div>Subtotal: ${CURRENCY_SYMBOL} ${parseFloat(held.subtotal).toFixed(2)}</div>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">
                        Expires: ${new Date(held.expires_at).toLocaleString()}
                    </span>
                    <div class="flex space-x-2">
                        ${!held.is_expired ? `
                            <button onclick="posCart.releaseSale('${held.hold_id}')"
                                    class="bg-primary-600 text-white px-3 py-1 rounded text-sm hover:bg-primary-700 transition-colors">
                                Release
                            </button>
                        ` : ''}
                        <button onclick="posCart.deleteHeldSale('${held.hold_id}')"
                                class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
            }

            // Show hold management modal
            showHoldManagement() {
                this.loadHeldSales();
                document.getElementById('holdManagementModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            // Hide hold management modal
            hideHoldManagement() {
                document.getElementById('holdManagementModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            async allocateBatches(productId, qty) {
                const res = await fetch('/api/pos/allocate-batches', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({product_id: productId, qty})
                });


                if (!res.ok) {
                    const text = await res.text();
                    throw new Error('Allocation failed: ' + text);
                }
                return await res.json();
            }
        }

        // updateCartLineAllocation updates the cart line and UI
        async function updateCartLineAllocation(cartLine, qty) {
            try {
                const {allocations, remaining} = await this.allocateBatches(cartLine.product_id, qty);
                cartLine.allocations = allocations;
                cartLine.allocatedRemaining = remaining; // if you want to show that
                // this.renderCart();
            } catch (err) {
                console.error(err);
                // optionally show user a warning
            }
        }

        const debouncedUpdate = debounce((cartLine, qty) => updateCartLineAllocation(cartLine, qty), 300);

        /*if (document.querySelectorAll('.qty-input')) document.querySelectorAll('.qty-input').forEach((input) => {
            input.addEventListener('change', function (e) {
                // onQtyChange(this.cart, )
                console.log('quantity', e.target.value);
            });

        });*/

        // Call this when qty changes (input handler or +/- buttons)
        /*function onQtyChange(cartLine, newQty) {
            cartLine.qty = newQty;
            // immediate small UI update (optimistic)
            renderCartLine(cartLine);
            // update allocations but debounced
            debouncedUpdate(cartLine, newQty);
        }*/

        // simple debounce helper
        function debounce(fn, wait = 300) {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...args), wait);
            };
        }


        // Initialize POS when page loads
        document.addEventListener('DOMContentLoaded', () => {
            window.posCart = new POSCart();
        });

        document.getElementById('lastReceiptBtn').addEventListener('click', getLastReceipt);

        function getLastReceipt() {
            const lastReceipt = localStorage.getItem('lastReceipt');
            if (lastReceipt) {
                const receipt = JSON.parse(lastReceipt);
                window.open(`/sales/${receipt.id}/print`);
                // window.open(`/sales/1/print`, '_blank');
            } else {
                this.showNotification('No recent receipt found!', 'error');
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'Enter') {
                window.posCart.showPaymentModal();
                setTimeout(() => {
                    document.querySelector('#amountReceived').focus();
                }, 100);
            }
            if (e.key === 'Escape') {
                setTimeout(() => {
                    document.querySelector('#cancelPayment').click();
                }, 100);
            }
            if (e.ctrlKey && e.key === 'Delete') {
                e.preventDefault();
                document.getElementById('clearCart').click();
            }
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                document.getElementById('productSearch').focus();
            }
            if (e.ctrlKey && e.key === 'h') {
                e.preventDefault();
                document.getElementById('holdInvoiceBtn').click();
            }
        });
    </script>


    <script>
        (function () {
            /* ====== Config ====== */
            const cfg = {
                selectors: {
                    productSearch: '#productSearch',
                    productResults: '#productResults',
                    productDetailsModal: '#productDetailsModal',
                    modalQuantity: '#modalQuantity',
                    modalBatch: '#modalBatch',
                    modalRate: '#modalRate',
                    completeSaleBtn: '.complete-sale-btn',
                    holdInvoiceBtn: '#holdInvoiceBtn',
                    lastReceiptBtn: '#lastReceiptBtn',
                    heldSalesBtn: '#lastReceiptBtn',
                    customerName: '#customerName',
                    amountReceived: '#amountReceived'
                },
                searchDebounceMs: 120
            };

            /* ====== Utility helpers ====== */
            const $ = s => document.querySelector(s);
            const $$ = s => Array.from(document.querySelectorAll(s));

            function on(el, ev, fn) {
                if (!el) return;
                el.addEventListener(ev, fn);
            }

            function preventDefault(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            /* ====== Keyboard hotkeys ====== */
            const hotkeys = {
                F1: () => focusProductSearch(),
                F2: () => {
                    const el = $(cfg.selectors.customerName);
                    if (el) {
                        el.focus();
                        el.select();
                    }
                },
                F3: () => {
                    const hold = $(cfg.selectors.holdInvoiceBtn);
                    if (hold) hold.click();
                },
                F4: () => {
                    const recall = $(cfg.selectors.lastReceiptBtn);
                    if (recall) recall.click();
                },
                F7: () => toggleDiscountFocus(),
                F8: () => removeSelectedCartRow(),
                'Ctrl+Enter': () => {
                    const btn = $(cfg.selectors.completeSaleBtn);
                    if (btn) btn.click();
                }
            };

            function focusProductSearch() {
                const el = $(cfg.selectors.productSearch);
                if (el) {
                    el.focus();
                    el.select();
                }
            }

            function toggleDiscountFocus() {
                // If you have discount input give it id discountInput
                const d = document.getElementById('discountInput');
                if (d) {
                    d.focus();
                    d.select();
                }
            }

            function showHoldSaleModal()
            {
                this.loadHeldSales();
                document.getElementById('holdManagementModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function removeSelectedCartRow() {
                const active = document.activeElement;
                if (active && active.dataset && active.dataset.posRole === 'cart-row') {
                    // remove the row
                    const row = active;
                    const removeBtn = row.querySelector('[data-pos-action="remove"]');
                    if (removeBtn) removeBtn.click();
                    else row.remove();
                    // move focus to next row or search
                    const next = row.nextElementSibling || row.previousElementSibling;
                    if (next) setActiveCartRow(next);
                    else focusProductSearch();
                }
            }

            /* ====== Intercept keys globally ====== */
            document.addEventListener('keydown', function (e) {
                // compose key string
                const key = (e.ctrlKey ? 'Ctrl+' : '') + (e.key.length === 1 ? e.key : e.key);
                // Cancel browser default for some keys
                if (['F1', 'F2', 'F3', 'F4', 'F6', 'F7', 'F8'].includes(e.key)) {
                    preventDefault(e);
                }
                // Hotkey mapping
                if (e.key === 'F1') {
                    hotkeys.F1();
                }
                if (e.key === 'F2') {
                    hotkeys.F2();
                }
                if (e.key === 'F3') {
                    hotkeys.F3();
                }
                if (e.key === 'F4') {
                    hotkeys.F4();
                }
                if (e.key === 'F6') {
                    hotkeys.F6();
                }
                if (e.key === 'F7') {
                    hotkeys.F7();
                }
                if (e.key === 'F8') {
                    hotkeys.F8();
                }
                if (e.key === 'Enter' && e.ctrlKey) {
                    hotkeys['Ctrl+Enter']();
                }
                // Open help with '?'
                if (e.key === '?') {
                    toggleHotkeyHelp();
                }
            });

            /* ====== Search Autocomplete with keyboard nav ====== */
            const searchEl = $(cfg.selectors.productSearch);
            const resultsEl = $(cfg.selectors.productResults);
            let debounceTimer = null;
            let cachedResults = {}; // map term -> results
            let results = [];       // current results array
            let highlightedIndex = -1;

            if (searchEl && resultsEl) {
                on(searchEl, 'input', (e) => {
                    clearTimeout(debounceTimer);
                    const term = e.target.value.trim();
                    debounceTimer = setTimeout(() => performSearch(term), cfg.searchDebounceMs);
                });

                on(searchEl, 'keydown', (e) => {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (results.length) highlightResult(Math.min(results.length - 1, highlightedIndex + 1));
                        else { /* no results */
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        highlightResult(Math.max(0, highlightedIndex - 1));
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (highlightedIndex >= 0) selectResult(results[highlightedIndex]);
                        else if (results.length === 1) selectResult(results[0]);
                    } else if (e.key === 'Escape') {
                        clearResults();
                    }
                });

                // click handlers on results (mouse allowed but not necessary)
                resultsEl.addEventListener('click', (ev) => {
                    const opt = ev.target.closest('[data-pos-role="product-option"]');
                    if (opt) {
                        const idx = parseInt(opt.dataset.idx, 10);
                        selectResult(results[idx]);
                    }
                });
            }

            let currentProduct = null; // Store currently selected product

            function performSearch(term) {
                if (!term) return clearResults();
                if (cachedResults[term]) {
                    renderResults(cachedResults[term]);
                    return;
                }
                fetch(`/pos/search?q=${encodeURIComponent(term)}`)
                    .then(r => r.json()).then(data => {
                    cachedResults[term] = data;
                    renderResults(data);
                }).catch(err => {
                    console.error('search failed', err);
                });
            }

            function renderResults(items) {
                results = items || [];
                highlightedIndex = results.length ? 0 : -1;
                resultsEl.innerHTML = results.map((it, idx) => {
                    const escaped = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;');
                    const isHighlighted = idx === highlightedIndex;
                    return `<li role="option" tabindex="-1" data-pos-role="product-option" data-idx="${idx}"
                          class="px-2 py-1 cursor-pointer ${isHighlighted ? 'bg-blue-100' : 'hover:bg-gray-100'}" aria-selected="${isHighlighted}">
                        <div class="text-sm">${escaped(it.name)} <small class="opacity-70">[${escaped(it.sku || '')}]</small></div>
                        <div class="text-xs opacity-60">Price: ${it.price} | Stock: ${it.stock}</div>
                      </li>`;
                }).join('');
                // Keep focus on search input for keyboard flow
            }

            function highlightResult(idx) {
                if (!results.length) return;
                highlightedIndex = idx;
                Array.from(resultsEl.children).forEach((c, i) => {
                    c.setAttribute('aria-selected', i === idx ? 'true' : 'false');
                    if (i === idx) {
                        c.classList.add('bg-blue-100');
                        c.classList.remove('hover:bg-gray-100');
                        c.scrollIntoView({block: 'nearest', behavior: 'smooth'});
                    } else {
                        c.classList.remove('bg-blue-100');
                        c.classList.add('hover:bg-gray-100');
                    }
                });
            }

            function selectResult(item) {
                if (!item) return;
                clearResults();
                searchEl.value = ''; // Clear search

                // Add product directly to cart via POSCart instance
                if (window.posCart) {
                    window.posCart.addToCart({
                        productId: item.id,
                        productName: item.name,
                        productPrice: item.price,
                        productStock: item.stock,
                        productUnit: item.unit || 'units'
                    });
                }

                // Return focus to search for next product
                focusProductSearch();
            }

            function clearResults() {
                results = [];
                resultsEl.innerHTML = '';
                highlightedIndex = -1;
            }

            /* ====== Cart roving tabindex logic ====== */
            const cartBody = $(cfg.selectors.cartBody);

            function setActiveCartRow(row) {
                if (!row) return;
                // remove old
                $$(cfg.selectors.cartBody + ' [data-pos-role="cart-row"]').forEach(r => {
                    r.setAttribute('tabindex', '-1');
                });
                row.setAttribute('tabindex', '0');
                row.focus();
            }

            function attachCartRowKeyboard(row) {
                row.setAttribute('data-pos-role', 'cart-row');
                row.setAttribute('tabindex', '-1'); // default -1
                row.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        const next = row.nextElementSibling;
                        if (next) setActiveCartRow(next);
                    }
                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        const prev = row.previousElementSibling;
                        if (prev) setActiveCartRow(prev); else focusProductSearch();
                    }
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        editCartRowQty(row);
                    }
                    if (e.key === 'Delete' || (e.key === 'Backspace' && e.shiftKey)) {
                        e.preventDefault();
                        removeRow(row);
                    }
                    if (e.key === 'Escape') {
                        focusProductSearch();
                    }
                });
            }

            function addToCart(item, qty) {
                // Basic DOM row creation; adapt to your actual cart data model & backend
                const tr = document.createElement('tr');
                tr.innerHTML = `
                  <td>${item.sku || ''}</td>
                  <td>${item.name}</td>
                  <td><span data-pos-field="qty">${qty}</span></td>
                  <td>${item.price}</td>
                  <td><button data-pos-action="remove" class="btn">Del</button></td>
                `;
                cartBody.appendChild(tr);
                attachCartRowKeyboard(tr);
                // set this new row active
                setActiveCartRow(tr);
                // update totals (you should implement recalc)
                recalcTotals();
            }

            function editCartRowQty(row) {
                const qtySpan = row.querySelector('[data-pos-field="qty"]');
                if (!qtySpan) return;
                const current = qtySpan.textContent.trim();
                const newQty = prompt('Edit qty', current);
                if (newQty !== null) {
                    qtySpan.textContent = parseFloat(newQty) || current;
                    recalcTotals();
                }
            }

            function removeRow(row) {
                const next = row.nextElementSibling || row.previousElementSibling;
                row.remove();
                if (next) setActiveCartRow(next);
                else focusProductSearch();
                recalcTotals();
            }

            function recalcTotals() {
                // implement your total, tax, discounts updates (server or client)
                // example: sum qty * price
                // After recalculation, focus remains where it should be.
            }

            /* ====== Modal focus trap (generic) ====== */
            function trapFocus(modalEl) {
                const focusable = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
                const nodes = Array.from(modalEl.querySelectorAll(focusable)).filter(n => !n.disabled);
                if (!nodes.length) return;
                const first = nodes[0], last = nodes[nodes.length - 1];

                function keyHandler(e) {
                    if (e.key !== 'Tab') return;
                    if (e.shiftKey && document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    } else if (!e.shiftKey && document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }

                modalEl.addEventListener('keydown', keyHandler);
                // cleanup should remove this handler when modal closes
            }

            /* ====== Hotkey help toggle ====== */
            function toggleHotkeyHelp() {
                const el = document.getElementById('posHotkeys');
                if (!el) return;
                el.style.display = el.style.display === 'none' ? 'block' : 'none';
            }

            // initial focus
            focusProductSearch();

        })();
    </script>


    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Smooth transitions for cart items */
        .cart-item {
            animation: fadeInUp 0.3s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Product Search Results Dropdown */
        #productResults {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 400px;
            overflow-y: auto;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            margin-top: 8px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            z-index: 50;
            list-style: none;
            padding: 0;
        }

        #productResults:empty {
            display: none;
        }

        #productResults li {
            padding: 12px 16px;
            cursor: pointer;
            transition: all 0.15s ease;
            border-bottom: 1px solid #f3f4f6;
        }

        #productResults li:last-child {
            border-bottom: none;
        }

        #productResults li:hover,
        #productResults li[aria-selected="true"] {
            background-color: #eff6ff;
            color: #1e40af;
        }

        #productResults li:focus {
            outline: none;
            background-color: #dbeafe;
            color: #1e3a8a;
        }
    </style>
@endpush
