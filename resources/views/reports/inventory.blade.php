{{-- resources/views/reports/inventory-report.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Inventory Report</h1>
                <p class="text-gray-600 mt-1">Comprehensive inventory analysis and stock management</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        <i class="lni lni-download mr-2"></i>
                        Export Report
                        <i class="lni lni-chevron-down ml-2 text-sm"></i>
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">

                        <form action="{{ route('reports.inventory.export') }}" method="POST" target="_blank">
                            @csrf
                            <input type="hidden" name="filters" :value="JSON.stringify(currentFilters)">

                            <button type="submit" name="export_type" value="excel"
                                    class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <x-ui.icon name="excel" class="w-3 h-3 mr-3"></x-ui.icon>
                                Export to Excel
                            </button>

                            <button type="submit" name="export_type" value="csv"
                                    class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <x-ui.icon name="csv" class="w-3 h-3 mr-3"></x-ui.icon>
                                Export to CSV
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalItems) }}</p>
                <p class="text-sm text-gray-600">Total Items in Stock</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-green-600">{{ format_currency($totalStockValue) }}</p>
                <p class="text-sm text-gray-600">Total Stock Value</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-blue-600">
                    {{ $products->where('stock', 0)->count() }}
                </p>
                <p class="text-sm text-gray-600">Out of Stock Items</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                @php
                    $settings = \App\Models\SystemSetting::getSettings();
                    $lowStockCount = $products->where('stock', '>', 0)
                                              ->where('stock', '<=', $settings->low_stock_threshold)
                                              ->count();
                @endphp
                <p class="text-2xl font-bold text-yellow-600">{{ $lowStockCount }}</p>
                <p class="text-sm text-gray-600">Low Stock Items</p>
            </x-ui.card>
        </div>

        <!-- Advanced Filters -->
        <x-ui.card title="Inventory Filters" padding="p-6" class="overflow-hidden">
            <div x-data="inventoryFilters()" x-init="init()" class="space-y-6">
                <!-- Tabs for Filter Groups -->
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px space-x-8 overflow-x-auto">
                        <button @click="activeTab = 'product'"
                                :class="activeTab === 'product' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                            <i class="lni lni-package mr-2"></i>
                            Product Filters
                        </button>

                        <button @click="activeTab = 'stock'"
                                :class="activeTab === 'stock' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                            <i class="lni lni-stats-up mr-2"></i>
                            Stock Status
                        </button>

                        <button @click="activeTab = 'batch'"
                                :class="activeTab === 'batch' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                            <i class="lni lni-agenda mr-2"></i>
                            Batch Filters
                        </button>

                        <button @click="activeTab = 'pricing'"
                                :class="activeTab === 'pricing' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                            <i class="lni lni-money-protection mr-2"></i>
                            Pricing
                        </button>

                        <button @click="activeTab = 'movement'"
                                :class="activeTab === 'movement' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                            <i class="lni lni-timer mr-2"></i>
                            Movement Analysis
                        </button>

                        <button @click="activeTab = 'advanced'"
                                :class="activeTab === 'advanced' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                            <i class="lni lni-cog mr-2"></i>
                            Advanced
                        </button>
                    </nav>
                </div>

                <!-- Filter Forms -->
                <form action="{{ route('reports.inventory') }}" method="GET" id="inventoryFilterForm">
                    <!-- Product Filters -->
                    <div x-show="activeTab === 'product'" class="space-y-4 animate-fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                                <input type="text" name="name" value="{{ $filters['name'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Search by name...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Barcode/SKU</label>
                                <input type="text" name="barcode" value="{{ $filters['barcode'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Enter barcode...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="all">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ ($filters['category'] ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <select name="brand" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="all">All Brands</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand }}" {{ ($filters['brand'] ?? '') == $brand ? 'selected' : '' }}>
                                            {{ $brand }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                                <select name="supplier" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="all">All Suppliers</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ ($filters['supplier'] ?? '') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Status Filters -->
                    <div x-show="activeTab === 'stock'" class="space-y-4 animate-fade-in">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Stock Status</label>
                            <div class="flex flex-wrap gap-3">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="stock_status" value="all"
                                           {{ ($filters['stock_status'] ?? 'all') == 'all' ? 'checked' : '' }}
                                           class="rounded-full border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">All</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="stock_status" value="in-stock"
                                           {{ ($filters['stock_status'] ?? '') == 'in-stock' ? 'checked' : '' }}
                                           class="rounded-full border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">In Stock</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="stock_status" value="out-of-stock"
                                           {{ ($filters['stock_status'] ?? '') == 'out-of-stock' ? 'checked' : '' }}
                                           class="rounded-full border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Out of Stock</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="stock_status" value="low-stock"
                                           {{ ($filters['stock_status'] ?? '') == 'low-stock' ? 'checked' : '' }}
                                           class="rounded-full border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Low Stock</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="stock_status" value="zero-stock"
                                           {{ ($filters['stock_status'] ?? '') == 'zero-stock' ? 'checked' : '' }}
                                           class="rounded-full border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Zero Stock</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Range (Min)</label>
                                <input type="number" name="stock_min" value="{{ $filters['stock_min'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Minimum stock">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Range (Max)</label>
                                <input type="number" name="stock_max" value="{{ $filters['stock_max'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Maximum stock">
                            </div>
                        </div>
                    </div>

                    <!-- Batch Filters -->
                    <div x-show="activeTab === 'batch'" class="space-y-4 animate-fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                                <input type="text" name="batch_number" value="{{ $filters['batch_number'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Search by batch number...">
                            </div>

                            <div class="flex items-center pt-6">
                                <label class="flex items-center">
                                    <input type="checkbox" name="show_batches" value="1"
                                           {{ ($filters['show_batches'] ?? false) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Show Batch Details</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Expiry Date Range</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">From</label>
                                    <input type="date" name="expiry_start" value="{{ $filters['expiry_start'] ?? '' }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">To</label>
                                    <input type="date" name="expiry_end" value="{{ $filters['expiry_end'] ?? '' }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Filters -->
                    <div x-show="activeTab === 'pricing'" class="space-y-4 animate-fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price Range (Min)</label>
                                <input type="number" step="0.01" name="price_min" value="{{ $filters['price_min'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Minimum price">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price Range (Max)</label>
                                <input type="number" step="0.01" name="price_max" value="{{ $filters['price_max'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Maximum price">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cost Range (Min)</label>
                                <input type="number" step="0.01" name="cost_min" value="{{ $filters['cost_min'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Minimum cost">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cost Range (Max)</label>
                                <input type="number" step="0.01" name="cost_max" value="{{ $filters['cost_max'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Maximum cost">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Profit Margin Min (%)</label>
                                <input type="number" step="0.01" name="margin_min" value="{{ $filters['margin_min'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Min margin %">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Profit Margin Max (%)</label>
                                <input type="number" step="0.01" name="margin_max" value="{{ $filters['margin_max'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Max margin %">
                            </div>
                        </div>
                    </div>

                    <!-- Movement Analysis -->
                    <div x-show="activeTab === 'movement'" class="space-y-4 animate-fade-in">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Movement Category</label>
                            <div class="flex flex-wrap gap-3">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="movement[]" value="fast-moving"
                                           {{ in_array('fast-moving', $filters['movement'] ?? []) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Fast Moving</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="movement[]" value="medium-moving"
                                           {{ in_array('medium-moving', $filters['movement'] ?? []) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Medium Moving</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="movement[]" value="slow-moving"
                                           {{ in_array('slow-moving', $filters['movement'] ?? []) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Slow Moving</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="movement[]" value="non-moving"
                                           {{ in_array('non-moving', $filters['movement'] ?? []) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Non Moving</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Sold (From)</label>
                                <input type="date" name="last_sold_start" value="{{ $filters['last_sold_start'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Sold (To)</label>
                                <input type="date" name="last_sold_end" value="{{ $filters['last_sold_end'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Filters -->
                    <div x-show="activeTab === 'advanced'" class="space-y-4 animate-fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="name" {{ ($filters['sort_by'] ?? 'name') == 'name' ? 'selected' : '' }}>Product Name</option>
                                    <option value="stock" {{ ($filters['sort_by'] ?? '') == 'stock' ? 'selected' : '' }}>Stock Quantity</option>
                                    <option value="price" {{ ($filters['sort_by'] ?? '') == 'price' ? 'selected' : '' }}>Price</option>
                                    <option value="category" {{ ($filters['sort_by'] ?? '') == 'category' ? 'selected' : '' }}>Category</option>
                                    <option value="created_at" {{ ($filters['sort_by'] ?? '') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                                    <option value="sales_count" {{ ($filters['sort_by'] ?? '') == 'sales_count' ? 'selected' : '' }}>Sales Count</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Direction</label>
                                <select name="sort_dir" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="asc" {{ ($filters['sort_dir'] ?? 'asc') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                    <option value="desc" {{ ($filters['sort_dir'] ?? '') == 'desc' ? 'selected' : '' }}>Descending</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Items Per Page</label>
                                <select name="per_page" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="25" {{ ($filters['per_page'] ?? 50) == 25 ? 'selected' : '' }}>25 items</option>
                                    <option value="50" {{ ($filters['per_page'] ?? 50) == 50 ? 'selected' : '' }}>50 items</option>
                                    <option value="100" {{ ($filters['per_page'] ?? 50) == 100 ? 'selected' : '' }}>100 items</option>
                                    <option value="250" {{ ($filters['per_page'] ?? 50) == 250 ? 'selected' : '' }}>250 items</option>
                                    <option value="500" {{ ($filters['per_page'] ?? 50) == 500 ? 'selected' : '' }}>500 items</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">As-of Date</label>
                                <input type="date" name="as_of_date" value="{{ $filters['as_of_date'] ?? date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <div>
                            <button type="button" @click="clearFilters()"
                                    class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="lni lni-close mr-1"></i>
                                Clear All Filters
                            </button>
                        </div>

                        <div class="flex space-x-3">
                            <button type="submit"
                                    class="bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors flex items-center">
                                <i class="lni lni-search-alt mr-2"></i>
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </x-ui.card>

        <!-- Results Table -->
        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Inventory Results</h3>
                <div class="text-sm text-gray-600">
                    Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} items
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Movement</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @forelse($products as $product)
                        @php
                            $settings = \App\Models\SystemSetting::getSettings();
                            $stockStatus = $product->stock > 0 ?
                                ($product->stock <= $settings->low_stock_threshold ? 'low' : 'good') :
                                'out';
                            $movement = $movementData[$product->id]['movement'] ?? 'unknown';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-primary-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="lni lni-package text-primary-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $product->barcode }}</div>
                                        @if($product->brand)
                                            <div class="text-xs text-gray-500">{{ $product->brand }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $product->category ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ number_format($product->stock) }}</div>
                                @if($filters['show_batches'] ?? false && $product->batches->count() > 0)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $product->batches->count() }} batch(es)
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ format_currency($product->price) }}
                                @if(optional(optional($product->batches)->first())->cost_price)
                                    <div class="text-xs text-gray-500">
                                        Cost: {{ format_currency(optional($product->batches->first())->cost_price) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                {{ format_currency($product->stock * $product->price) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($stockStatus === 'good')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="lni lni-checkmark-circle mr-1"></i>
                                In Stock
                            </span>
                                @elseif($stockStatus === 'low')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="lni lni-warning mr-1"></i>
                                Low Stock
                            </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="lni lni-close mr-1"></i>
                                Out of Stock
                            </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($movement === 'fast-moving')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Fast Moving
                            </span>
                                @elseif($movement === 'medium-moving')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Medium Moving
                            </span>
                                @elseif($movement === 'slow-moving')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Slow Moving
                            </span>
                                @elseif($movement === 'non-moving')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Non Moving
                            </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Unknown
                            </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('inventory.edit', $product) }}"
                                       class="text-gray-600 hover:text-gray-700 p-1 rounded hover:bg-gray-100"
                                       title="Edit">
                                        <i class="lni lni-pencil"></i>
                                    </a>
                                    @if($filters['show_batches'] ?? false)
                                        <button type="button" onclick="toggleBatches({{ $product->id }})"
                                                class="text-primary-600 hover:text-primary-700 p-1 rounded hover:bg-primary-50"
                                                title="View Batches">
                                            <i class="lni lni-agenda"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- Batch Details (Hidden by default) -->
                                @if($filters['show_batches'] ?? false && $product->batches->count() > 0)
                                    <div id="batches-{{ $product->id }}" class="hidden mt-3 p-3 bg-gray-50 rounded-lg">
                                        <h4 class="text-xs font-semibold text-gray-700 mb-2">Batch Details:</h4>
                                        <div class="space-y-2">
                                            @foreach($product->batches as $batch)
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="font-medium">{{ $batch->batch_number }}</span>
                                                    <span class="text-gray-600">{{ $batch->quantity }} units</span>
                                                    <span class="{{ $batch->days_to_expiry < 30 ? 'text-red-600' : 'text-gray-600' }}">
                                            Expires: {{ $batch->expiry_date->format('M d, Y') }}
                                                        @if($batch->days_to_expiry < 30)
                                                            ({{ $batch->days_to_expiry }} days)
                                                        @endif
                                        </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="lni lni-inbox text-4xl mb-2 block text-gray-400"></i>
                                No products found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
@endsection

@push('scripts')
    <script>
        function inventoryFilters() {
            return {
                activeTab: 'product',
                currentFilters: @json($filters),

                init() {
                    // Set active tab based on which filters are applied
                    this.setActiveTab();
                },

                setActiveTab() {
                    const filters = this.currentFilters;

                    if (filters.name || filters.barcode || filters.category !== 'all' || filters.brand !== 'all' || filters.supplier !== 'all') {
                        this.activeTab = 'product';
                    } else if (filters.stock_status !== 'all' || filters.stock_min || filters.stock_max) {
                        this.activeTab = 'stock';
                    } else if (filters.batch_number || filters.expiry_start || filters.expiry_end || filters.show_batches) {
                        this.activeTab = 'batch';
                    } else if (filters.price_min || filters.price_max || filters.cost_min || filters.cost_max || filters.margin_min || filters.margin_max) {
                        this.activeTab = 'pricing';
                    } else if (filters.movement || filters.last_sold_start || filters.last_sold_end) {
                        this.activeTab = 'movement';
                    } else {
                        this.activeTab = 'product';
                    }
                },

                clearFilters() {
                    // Clear all form inputs
                    const form = document.getElementById('inventoryFilterForm');
                    const inputs = form.querySelectorAll('input, select, textarea');

                    inputs.forEach(input => {
                        if (input.type === 'text' || input.type === 'number' || input.type === 'date') {
                            input.value = '';
                        } else if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else if (input.tagName === 'SELECT') {
                            input.selectedIndex = 0;
                        }
                    });

                    // Submit the form to reset
                    form.submit();
                }
            }
        }

        function toggleBatches(productId) {
            const batchesDiv = document.getElementById('batches-' + productId);
            if (batchesDiv) {
                batchesDiv.classList.toggle('hidden');
            }
        }
    </script>
@endpush
