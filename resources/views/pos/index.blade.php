@extends('layouts.app')

@section('content')
    <div class="h-full flex flex-col">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Point of Sale</h1>
                <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, F j, Y • h:i A') }}</p>
            </div>
            {{--<div class="grid grid-cols-2 gap-2 mt-4">
                <button class="bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors complete-sale-btn">
                    Complete Sale
                </button>
                <button class="bg-orange-600 text-white py-3 rounded-lg font-semibold hover:bg-orange-700 transition-colors hold-sale-btn">
                    Hold Sale
                </button>
            </div>--}}

            <div class="flex items-center space-x-3">
{{--                <button--}}
{{--                    class="bg-white border border-gray-300 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 flex items-center transition-colors"--}}
{{--                    id="holdSaleBtn">--}}
{{--                    <i class="lni lni-save mr-2 text-blue-600"></i>--}}
{{--                    Hold Sale--}}
{{--                </button>--}}
{{--                <button class="bg-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-purple-700 transition-colors flex items-center complete-sale-btn">--}}
{{--                    Complete Sale--}}
{{--                </button>--}}
                <button class="bg-orange-600 cursor-pointer text-white px-4 py-2 rounded-lg font-medium hover:bg-orange-700 transition-colors flex items-center hold-sale-btn"
                        id="hold-sale-btn">
                    Hold Sale
                </button>
                <button class="bg-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-purple-700 transition-colors flex items-center show-hold-management-btn">
                    <i class="lni lni-timer mr-2"></i>
                    Held Sales
                </button>
                <button
                    class="bg-white border border-gray-300 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 flex items-center transition-colors"
                    id="lastReceiptBtn">
                    <i class="lni lni-printer mr-2 text-gray-600"></i>
                    Last Receipt
                </button>
                <button
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 px-4 py-2 rounded-lg font-medium hover:from-blue-100 hover:to-indigo-100 flex items-center transition-all"
                    id="keyboardShortcuts">
                    <i class="lni lni-keyboard mr-2 text-blue-600"></i>
                    Shortcuts
                </button>
            </div>
        </div>

        <!-- Main POS Layout -->
        <div class="flex-1 flex gap-6">
            <!-- Products Section (60%) -->
            <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <!-- Search Bar -->
                <div class="mb-4">
                    <div class="relative">
                        <i class="lni lni-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                        <input
                            type="text"
                            id="productSearch"
                            placeholder="Search products by name, brand, or generic name..."
                            class="w-full pl-12 pr-36 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base transition-all"
                        >
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex items-center space-x-2">
                            <span class="text-sm text-gray-400">or</span>
                            <button
                                class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:from-blue-600 hover:to-blue-700 flex items-center shadow-sm transition-all"
                                id="scanBarcodeBtn">
                                <i class="lni lni-barcode mr-1.5"></i>
                                Scan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filters & Sort -->
                <div class="flex items-center justify-between mb-4 gap-4">
                    <div class="flex items-center space-x-2 flex-1 overflow-x-auto pb-1">
                        <button
                            class="category-filter px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-blue-600 text-white shadow-sm"
                            data-category="all">
                            All Products
                        </button>
                        <button
                            class="category-filter px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-gray-100 text-gray-700 hover:bg-gray-200"
                            data-category="tablet">
                            Tablets
                        </button>
                        <button
                            class="category-filter px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-gray-100 text-gray-700 hover:bg-gray-200"
                            data-category="syrup">
                            Syrups
                        </button>
                        <button
                            class="category-filter px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-gray-100 text-gray-700 hover:bg-gray-200"
                            data-category="injection">
                            Injections
                        </button>
                    </div>
                    <select id="sortProducts"
                            class="px-4 py-2 border-2 border-gray-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="name-asc">Name (A-Z)</option>
                        <option value="name-desc">Name (Z-A)</option>
                        <option value="price-asc">Price (Low-High)</option>
                        <option value="price-desc">Price (High-Low)</option>
                        <option value="stock-asc">Stock (Low-High)</option>
                        <option value="stock-desc">Stock (High-Low)</option>
                    </select>
                </div>

                <!-- Product Grid -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[calc(100vh-350px)] overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($products as $product)
                        <div
                            class="group relative border-2 border-gray-200 rounded-xl p-4 hover:border-blue-400 hover:shadow-lg transition-all duration-200 cursor-pointer product-card bg-gradient-to-br from-white to-gray-50"
                            data-product-id="{{ $product->id }}"
                            data-product-name="{{ $product->name }}"
                            data-product-barcode="{{ $product->barcode ?? $product->id }}"
                            data-product-price="{{ $product->price }}"
                            data-product-stock="{{ $product->stock }}"
                            data-product-unit="{{ $product->unit }}"
                            data-product-category="{{ strtolower($product->category ?? 'other') }}">

                            <!-- Stock Badge -->
                            @if($product->stock < 10)
                                <div class="absolute top-2 right-2 z-10">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $product->stock < 5 ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                                        Low Stock
                                    </span>
                                </div>
                            @endif

                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-base mb-1 truncate group-hover:text-blue-600 transition-colors">{{ $product->name }}</h3>
                                    @if($product->generic_name)
                                        <p class="text-xs text-gray-500 mb-0.5 truncate">{{ $product->generic_name }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 font-medium">{{ $product->brand }}</p>
                                </div>
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl flex items-center justify-center ml-3 group-hover:from-blue-100 group-hover:to-indigo-100 transition-all">
                                    <i class="lni lni-capsule text-blue-500 text-2xl"></i>
                                </div>
                            </div>

                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-2xl font-extrabold text-gray-900 mb-1">
                                        Rs. {{ number_format($product->price, 2) }}</p>
                                    <div class="flex items-center space-x-1">
                                        <i class="lni lni-package text-xs {{ $product->stock < 10 ? 'text-orange-500' : 'text-green-500' }}"></i>
                                        <p class="text-xs font-medium {{ $product->stock < 10 ? 'text-orange-600' : 'text-green-600' }}">
                                            {{ $product->stock }} {{ $product->unit }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-2.5 rounded-xl hover:from-blue-700 hover:to-blue-800 hover:shadow-lg transform hover:scale-105 transition-all duration-200 add-to-cart-btn">
                                    <i class="lni lni-plus text-lg"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Cart Section (40%) -->
            <div class="w-96 bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="lni lni-cart text-blue-600 mr-2"></i>
                        Cart
                    </h3>
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold" id="cartCount">0</span>
                </div>

                <!-- Customer Info -->
                <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center space-x-2 mb-2">
                        <i class="lni lni-user text-gray-400"></i>
                        <input type="text" id="customerName" placeholder="Customer Name (Optional)"
                               class="flex-1 bg-transparent border-0 focus:ring-0 text-sm placeholder-gray-400 px-0 py-2">
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="lni lni-phone text-gray-400"></i>
                        <input type="tel" id="customerPhone" placeholder="Phone Number"
                               class="flex-1 bg-transparent border-0 focus:ring-0 text-sm placeholder-gray-400 px-0 py-2 focus-visible:border-none">
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 space-y-2 overflow-y-auto max-h-[35vh] mb-4 pr-2 custom-scrollbar" id="cartItems">
                    <!-- Cart items will be dynamically added here -->
                    <div class="text-center text-gray-400 py-8" id="emptyCart">
                        <i class="lni lni-shopping-basket text-5xl text-gray-300 mb-3"></i>
                        <p class="font-medium">Your cart is empty</p>
                        <p class="text-sm mt-1">Start adding products</p>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="border-t-2 border-gray-200 pt-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 font-medium">Subtotal</span>
                        <span class="text-gray-900 font-semibold" id="subtotal">Rs. 0.00</span>
                    </div>

                    <!-- Discount Section -->
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-700 font-medium">Discount</span>
                            <div class="flex items-center space-x-1 bg-white rounded-lg p-1 border border-gray-200">
                                <button class="discount-type px-3 py-1 text-xs font-semibold rounded-md transition-all"
                                        data-type="fixed">Rs.
                                </button>
                                <button
                                    class="discount-type px-3 py-1 text-xs font-semibold rounded-md bg-blue-600 text-white transition-all"
                                    data-type="percentage">%
                                </button>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <input type="number"
                                   id="discountInput"
                                   placeholder="0"
                                   class="flex-1 px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   value="0">
                            <button
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors"
                                id="applyDiscountBtn">
                                Apply
                            </button>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-2" id="discountAmount"
                             style="display: none;">
                            <span>Discount Applied</span>
                            <span class="font-semibold text-red-600" id="discountValue">-Rs. 0.00</span>
                        </div>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 font-medium">Tax ({{$settings['tax_rate']}}%)</span>
                        <span class="text-gray-900 font-semibold" id="tax">Rs. 0.00</span>
                    </div>

                    <div
                        class="flex justify-between items-center bg-gradient-to-r from-blue-50 to-indigo-50 p-3 rounded-lg border-2 border-blue-200">
                        <span class="text-lg font-bold text-gray-900">Total</span>
                        <span class="text-2xl font-extrabold text-blue-600" id="total">Rs. 0.00</span>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <p class="text-xs font-semibold text-gray-700 mb-2">Payment Method</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                class="payment-method px-3 py-2.5 rounded-lg text-xs font-semibold transition-all bg-blue-600 text-white shadow-sm"
                                data-method="cash">
                                <i class="lni lni-money-location block text-lg mb-1"></i>
                                Cash
                            </button>
                            <button
                                class="payment-method px-3 py-2.5 rounded-lg text-xs font-semibold transition-all bg-gray-100 text-gray-700 hover:bg-gray-200"
                                data-method="card">
                                <i class="lni lni-credit-cards block text-lg mb-1"></i>
                                Card
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <button
                            class="bg-gradient-to-r from-green-600 to-green-700 text-white py-3.5 rounded-xl font-bold hover:from-green-700 hover:to-green-800 hover:shadow-lg transform hover:scale-105 transition-all complete-sale-btn">
                            <i class="lni lni-checkmark mr-1"></i>
                            Complete
                        </button>
                        <button
                            class="bg-white border-2 border-gray-300 text-gray-700 py-3.5 rounded-xl font-bold hover:bg-gray-50 hover:border-gray-400 transition-all"
                            id="clearCart">
                            <i class="lni lni-close mr-1"></i>
                            Clear
                        </button>
                    </div>

                    <!-- Quick Cash -->
                    <div class="mt-3">
                        <p class="text-xs font-semibold text-gray-700 mb-2">Quick Cash Amounts</p>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                class="bg-gradient-to-br from-gray-100 to-gray-50 border border-gray-300 py-2.5 rounded-lg text-sm font-bold hover:from-gray-200 hover:to-gray-100 transition-all quick-cash"
                                data-amount="500">Rs. 500
                            </button>
                            <button
                                class="bg-gradient-to-br from-gray-100 to-gray-50 border border-gray-300 py-2.5 rounded-lg text-sm font-bold hover:from-gray-200 hover:to-gray-100 transition-all quick-cash"
                                data-amount="1000">Rs. 1000
                            </button>
                            <button
                                class="bg-gradient-to-br from-gray-100 to-gray-50 border border-gray-300 py-2.5 rounded-lg text-sm font-bold hover:from-gray-200 hover:to-gray-100 transition-all quick-cash"
                                data-amount="2000">Rs. 2000
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Item Template (Hidden) -->
    <template id="cartItemTemplate">
        <div class="cart-item p-3 border-2 border-gray-200 rounded-xl bg-white hover:border-blue-300 transition-all"
             data-product-id="">
            <div class="flex justify-between items-start mb-2.5">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-900 text-sm product-name truncate"></div>
                    <div class="text-xs text-gray-500 product-unit"></div>
                </div>
                <div class="text-right ml-2">
                    <div class="font-bold text-gray-900 product-total">Rs. 0.00</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center bg-gray-100 rounded-lg border border-gray-300">
                    <button class="quantity-btn minus px-3 py-1.5 hover:bg-gray-200 transition-colors rounded-l-lg">
                        <i class="lni lni-minus text-sm"></i>
                    </button>
                    <input type="number"
                           class="quantity-input w-12 text-center border-0 bg-transparent py-1.5 text-sm font-semibold focus:ring-0"
                           value="1" min="1">
                    <button class="quantity-btn plus px-3 py-1.5 hover:bg-gray-200 transition-colors rounded-r-lg">
                        <i class="lni lni-plus text-sm"></i>
                    </button>
                </div>
                <span class="text-xs text-gray-500 product-price flex-1">Rs. 0.00 each</span>
                <button
                    class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-all remove-item">
                    <i class="lni lni-trash-can text-base"></i>
                </button>
            </div>
        </div>
    </template>

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
                        <span class="text-3xl font-bold text-green-600" id="modalTotal">Rs. 0.00</span>
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
                        <span class="text-2xl font-bold text-green-600" id="changeAmount">Rs. 0.00</span>
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

    <!-- Barcode Scanner Modal -->
    <div id="scannerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4">
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white flex items-center">
                        <i class="lni lni-barcode mr-3 text-3xl"></i>
                        Scan Barcode
                    </h3>
                    <button class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-all"
                            id="closeScannerModal">
                        <i class="lni lni-close text-2xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="inline-block p-6 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl mb-4">
                        <i class="lni lni-barcode text-6xl text-purple-600 animate-pulse"></i>
                    </div>
                    <p class="text-gray-700 font-medium mb-2">Ready to scan</p>
                    <p class="text-sm text-gray-500">Use your barcode scanner or enter code manually</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Barcode / Product Code</label>
                        <input
                            type="text"
                            id="barcodeInput"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-lg font-mono focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-center"
                            placeholder="Scan or type barcode"
                            autocomplete="off">
                    </div>

                    <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                        <div class="flex items-start space-x-2">
                            <i class="lni lni-information text-blue-600 mt-0.5"></i>
                            <div class="text-xs text-blue-700">
                                <p class="font-semibold mb-1">Tips:</p>
                                <ul class="space-y-1 ml-2">
                                    <li>• Point scanner at barcode and pull trigger</li>
                                    <li>• Or type the code manually and press Enter</li>
                                    <li>• Press ESC to close this dialog</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="scanResultArea" class="hidden">
                        <div class="bg-green-50 border border-green-200 p-4 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <i class="lni lni-checkmark-circle text-green-600 text-2xl"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-green-900">Product Found!</p>
                                    <p class="text-xs text-green-700" id="scannedProductName"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="scanErrorArea" class="hidden">
                        <div class="bg-red-50 border border-red-200 p-4 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <i class="lni lni-close-circle text-red-600 text-2xl"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-red-900">Product Not Found</p>
                                    <p class="text-xs text-red-700">Barcode: <span id="failedBarcode"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button
                        class="bg-gray-100 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-200 transition-colors"
                        id="cancelScanner">
                        Cancel
                    </button>
                    <button
                        class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 rounded-xl font-bold hover:from-purple-700 hover:to-indigo-700 transition-all"
                        id="manualScanBtn">
                        Scan Manually
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Keyboard Shortcuts Modal -->
    <div id="shortcutsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 rounded-t-2xl">
                <h3 class="text-2xl font-bold text-white flex items-center">
                    <i class="lni lni-keyboard mr-3 text-3xl"></i>
                    Keyboard Shortcuts
                </h3>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Complete Sale</span>
                    <kbd class="px-3 py-1 bg-white border-2 border-gray-300 rounded-lg font-mono text-sm font-semibold">Ctrl
                        + Enter</kbd>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Clear Cart</span>
                    <kbd class="px-3 py-1 bg-white border-2 border-gray-300 rounded-lg font-mono text-sm font-semibold">Ctrl
                        + Delete</kbd>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Focus Search</span>
                    <kbd class="px-3 py-1 bg-white border-2 border-gray-300 rounded-lg font-mono text-sm font-semibold">Ctrl
                        + K</kbd>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Hold Sale</span>
                    <kbd class="px-3 py-1 bg-white border-2 border-gray-300 rounded-lg font-mono text-sm font-semibold">Ctrl
                        + H</kbd>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-700 font-medium">Barcode Scanner</span>
                    <kbd class="px-3 py-1 bg-white border-2 border-gray-300 rounded-lg font-mono text-sm font-semibold">Ctrl
                        + B</kbd>
                </div>
            </div>
            <div class="p-4 border-t">
                <button
                    class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition-colors"
                    id="closeShortcuts">
                    Got it!
                </button>
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
        class POSCart {
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
                document.getElementById('sortProducts').addEventListener('change', (e) => {
                    this.sortProducts(e.target.value);
                });

                // Discount type toggle
                document.querySelectorAll('.discount-type').forEach(btn => {
                    btn.addEventListener('click', () => {
                        this.setDiscountType(btn.dataset.type);
                    });
                });

                // Apply discount
                document.getElementById('applyDiscountBtn').addEventListener('click', () => {
                    this.applyDiscount();
                });

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

                // Keyboard shortcuts modal
                document.getElementById('keyboardShortcuts').addEventListener('click', () => {
                    this.showShortcutsModal();
                });

                document.getElementById('closeShortcuts').addEventListener('click', () => {
                    this.hideShortcutsModal();
                });

                // Barcode scanner
                document.getElementById('scanBarcodeBtn').addEventListener('click', () => {
                    this.showScannerModal();
                });

                document.getElementById('closeScannerModal').addEventListener('click', () => {
                    this.hideScannerModal();
                });

                document.getElementById('cancelScanner').addEventListener('click', () => {
                    this.hideScannerModal();
                });

                document.getElementById('manualScanBtn').addEventListener('click', () => {
                    this.processBarcodeInput();
                });

                document.getElementById('barcodeInput').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.processBarcodeInput();
                    }
                });

                // In your POS initialization
                document.querySelector('.hold-sale-btn').addEventListener('click', () => {
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

            addToCart(productData) {
                const existingItem = this.cart.find(item => item.id === productData.productId);

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
                    this.cart.push({
                        id: productData.productId,
                        name: productData.productName,
                        price: parseFloat(productData.productPrice),
                        unit: productData.productUnit,
                        stock: parseInt(productData.productStock),
                        quantity: 1
                    });
                }

                this.updateCart();
                this.showNotification('✓ Added to cart', 'success');
            }

            removeFromCart(productId) {
                this.cart = this.cart.filter(item => item.id !== productId);
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

            updateCartDisplay() {
                const cartItems = document.getElementById('cartItems');
                const template = document.getElementById('cartItemTemplate');
                const cartCount = document.getElementById('cartCount');

                // Update cart count badge
                cartCount.textContent = this.cart.length;

                // Clear cart items but preserve the empty cart message structure
                const emptyCartHtml = `
            <div class="text-center text-gray-400 py-8" id="emptyCart">
                <i class="lni lni-shopping-basket text-5xl text-gray-300 mb-3"></i>
                <p class="font-medium">Your cart is empty</p>
                <p class="text-sm mt-1">Start adding products</p>
            </div>
        `;

                cartItems.innerHTML = '';

                if (this.cart.length === 0) {
                    cartItems.innerHTML = emptyCartHtml;
                    return;
                }

                this.cart.forEach(item => {
                    const clone = template.content.cloneNode(true);
                    const cartItem = clone.querySelector('.cart-item');

                    cartItem.dataset.productId = item.id;
                    cartItem.querySelector('.product-name').textContent = item.name;
                    cartItem.querySelector('.product-unit').textContent = `(${item.unit})`;
                    cartItem.querySelector('.product-price').textContent = `Rs. ${item.price.toFixed(2)} each`;
                    cartItem.querySelector('.quantity-input').value = item.quantity;
                    cartItem.querySelector('.product-total').textContent = `Rs. ${(item.price * item.quantity).toFixed(2)}`;

                    // Bind events for this item
                    cartItem.querySelector('.minus').addEventListener('click', () => {
                        this.updateQuantity(item.id, item.quantity - 1);
                    });

                    cartItem.querySelector('.plus').addEventListener('click', () => {
                        this.updateQuantity(item.id, item.quantity + 1);
                    });

                    cartItem.querySelector('.quantity-input').addEventListener('change', (e) => {
                        this.updateQuantity(item.id, parseInt(e.target.value));
                    });

                    cartItem.querySelector('.remove-item').addEventListener('click', () => {
                        this.removeFromCart(item.id);
                    });

                    cartItems.appendChild(clone);
                });
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

                document.getElementById('subtotal').textContent = `Rs. ${subtotal.toFixed(2)}`;
                document.getElementById('tax').textContent = `Rs. ${tax.toFixed(2)}`;
                document.getElementById('total').textContent = `Rs. ${total.toFixed(2)}`;

                // Show/hide discount amount
                if (discount > 0) {
                    document.getElementById('discountAmount').style.display = 'flex';
                    document.getElementById('discountValue').textContent = `-Rs. ${discount.toFixed(2)}`;
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
                    this.showNotification(`Discount of ${this.discountType === 'percentage' ? this.discountValue + '%' : 'Rs. ' + this.discountValue} applied`, 'success');
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

                document.getElementById('modalTotal').textContent = `Rs. ${total.toFixed(2)}`;
                document.getElementById('modalItemCount').textContent = `${this.cart.length} item${this.cart.length > 1 ? 's' : ''}`;
                document.getElementById('modalPaymentMethod').textContent = this.paymentMethod.charAt(0).toUpperCase() + this.paymentMethod.slice(1);
                document.getElementById('amountReceived').value = '';
                document.getElementById('changeAmount').textContent = 'Rs. 0.00';

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
                document.getElementById('changeAmount').textContent = `Rs. ${change.toFixed(2)}`;
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
                const products = document.querySelectorAll('.product-card');
                const term = searchTerm.toLowerCase();

                products.forEach(product => {
                    const name = product.dataset.productName.toLowerCase();
                    const category = product.dataset.productCategory || '';
                    const unit = (product.dataset.productUnit).toLowerCase() || '';

                    console.log("category", unit, this.currentCategory);
                    const matchesSearch = !term || name.includes(term);
                    const matchesCategory = this.currentCategory === 'all' || unit === this.currentCategory;

                    product.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
                });
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

            showShortcutsModal() {
                const modal = document.getElementById('shortcutsModal');
                modal.style.display = 'flex';
            }

            hideShortcutsModal() {
                const modal = document.getElementById('shortcutsModal');
                modal.style.display = 'none';
            }

            showScannerModal() {
                const modal = document.getElementById('scannerModal');
                const input = document.getElementById('barcodeInput');
                const resultArea = document.getElementById('scanResultArea');
                const errorArea = document.getElementById('scanErrorArea');

                // Reset modal state
                input.value = '';
                resultArea.classList.add('hidden');
                errorArea.classList.add('hidden');

                modal.style.display = 'flex';
                setTimeout(() => input.focus(), 100);
            }

            hideScannerModal() {
                const modal = document.getElementById('scannerModal');
                modal.style.display = 'none';
            }

            processBarcodeInput() {
                const input = document.getElementById('barcodeInput');
                const barcode = input.value.trim();

                if (!barcode) {
                    this.showNotification('Please enter a barcode', 'error');
                    return;
                }

                this.searchProductByBarcode(barcode);
            }

            searchProductByBarcode(barcode) {
                const resultArea = document.getElementById('scanResultArea');
                const errorArea = document.getElementById('scanErrorArea');
                const input = document.getElementById('barcodeInput');

                // Search for product by barcode in the product cards
                const products = document.querySelectorAll('.product-card');
                let found = false;

                products.forEach(product => {
                    // Check if product has a barcode data attribute or if the barcode matches the product ID
                    const productBarcode = product.dataset.productBarcode || product.dataset.productId;
                    const productName = product.dataset.productName;

                    if (productBarcode === barcode || productName.toLowerCase().includes(barcode.toLowerCase())) {
                        // Product found - add to cart
                        this.addToCart(product.dataset);

                        // Show success feedback
                        document.getElementById('scannedProductName').textContent = productName;
                        resultArea.classList.remove('hidden');
                        errorArea.classList.add('hidden');

                        found = true;

                        // Auto-close modal after 1.5 seconds
                        setTimeout(() => {
                            this.hideScannerModal();
                        }, 1500);
                    }
                });

                if (!found) {
                    // Product not found
                    document.getElementById('failedBarcode').textContent = barcode;
                    errorArea.classList.remove('hidden');
                    resultArea.classList.add('hidden');

                    // Clear input and refocus
                    setTimeout(() => {
                        input.value = '';
                        input.focus();
                    }, 1500);
                }
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
                        <p class="text-lg font-bold text-gray-900">Rs. ${parseFloat(held.total_amount).toFixed(2)}</p>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${held.is_expired ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                            ${held.is_expired ? 'Expired' : 'Active'}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 mb-3">
                    <div>Items: ${held.cart_data.cart.length}</div>
                    <div>Subtotal: Rs. ${parseFloat(held.subtotal).toFixed(2)}</div>
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
        }


        // Initialize POS when page loads
        document.addEventListener('DOMContentLoaded', () => {
            this.posCart = new POSCart();
        });

        document.getElementById('lastReceiptBtn').addEventListener('click', () => {
            const lastReceipt = localStorage.getItem('lastReceipt');
            if (lastReceipt) {
                const receipt = JSON.parse(lastReceipt);
                window.open(`/sales/${receipt.id}/print`);
                // window.open(`/sales/1/print`, '_blank');
            } else {
                this.showNotification('No recent receipt found!', 'error');
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'Enter') {
                // completeSale();
                const postCart = new POSCart();
                postCart.showPaymentModal();
                // e.preventDefault();
                // document.querySelector('.complete-sale-btn').click();
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
                document.getElementById('hold-sale-btn').click();
            }
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                document.getElementById('scanBarcodeBtn').click();
            }
            // ESC key to close scanner modal
            if (e.key === 'Escape') {
                const scannerModal = document.getElementById('scannerModal');
                if (scannerModal && scannerModal.style.display === 'flex') {
                    scannerModal.style.display = 'none';
                }
            }
        });
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
    </style>
@endpush
