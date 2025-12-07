@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Expiring Products Report</h1>
                <p class="text-gray-600 mt-1">Track and manage products approaching expiration</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <button @click="open = !open" type="button"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        <i class="lni lni-download mr-2"></i>
                        Export Report
                        <i class="lni lni-chevron-down ml-2"></i>
                    </button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
                         style="display: none;">
                        <div class="py-1">
                            <form action="{{ route('reports.expiring-products.export') }}" method="POST" class="block">
                                @csrf
                                <input type="hidden" name="export_type" value="excel">
                                @foreach($filters as $key => $value)
                                    @if(!is_null($value) && $value !== '')
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endif
                                @endforeach
                                <button type="submit" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <x-ui.icon name="excel" class="w-3 h-3 mr-3"></x-ui.icon>
                                    Export as Excel
                                </button>
                            </form>
                            <form action="{{ route('reports.expiring-products.export') }}" method="POST" class="block">
                                @csrf
                                <input type="hidden" name="export_type" value="csv">
                                @foreach($filters as $key => $value)
                                    @if(!is_null($value) && $value !== '')
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endif
                                @endforeach
                                <button type="submit" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <x-ui.icon name="csv" class="w-3 h-3 mr-3"></x-ui.icon>
                                    Export as CSV
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-alarm text-2xl text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-yellow-600">{{ $totalBatchesExpiring }}</p>
                <p class="text-sm text-gray-600">Batches Expiring (30 days)</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-close text-2xl text-red-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-red-600">{{ $totalExpiredBatches }}</p>
                <p class="text-sm text-gray-600">Expired Batches</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-money-protection text-2xl text-orange-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-orange-600">{{ format_currency($totalValueAtRisk) }}</p>
                <p class="text-sm text-gray-600">Value at Risk</p>
            </x-ui.card>
        </div>

        <!-- Filters -->
        <x-ui.card title="Filters" padding="p-6">
            <form action="{{ route('reports.expiring-products') }}" method="GET">
                <div class="space-y-6">
                    <!-- Expiry Date Filters -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="lni lni-calendar mr-2"></i>
                            Expiry Date Filters
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="expiry_within_days" class="block text-sm font-medium text-gray-700 mb-1">Expiring Within (Days)</label>
                                <input type="number" name="expiry_within_days" id="expiry_within_days"
                                       value="{{ $filters['expiry_within_days'] }}"
                                       placeholder="e.g. 30"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="expiry_start" class="block text-sm font-medium text-gray-700 mb-1">Expiry Start Date</label>
                                <input type="date" name="expiry_start" id="expiry_start"
                                       value="{{ $filters['expiry_start'] }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="expiry_end" class="block text-sm font-medium text-gray-700 mb-1">Expiry End Date</label>
                                <input type="date" name="expiry_end" id="expiry_end"
                                       value="{{ $filters['expiry_end'] }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div class="flex items-end">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="expired_only" value="1"
                                           {{ $filters['expired_only'] ? 'checked' : '' }}
                                           class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Expired Items Only</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Batch Filters -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="lni lni-package mr-2"></i>
                            Batch Filters
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                                <input type="text" name="batch_number" id="batch_number"
                                       value="{{ $filters['batch_number'] }}"
                                       placeholder="Search batch..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="manufacture_start" class="block text-sm font-medium text-gray-700 mb-1">Manufacture Start</label>
                                <input type="date" name="manufacture_start" id="manufacture_start"
                                       value="{{ $filters['manufacture_start'] }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="manufacture_end" class="block text-sm font-medium text-gray-700 mb-1">Manufacture End</label>
                                <input type="date" name="manufacture_end" id="manufacture_end"
                                       value="{{ $filters['manufacture_end'] }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="batch_status" class="block text-sm font-medium text-gray-700 mb-1">Batch Status</label>
                                <select name="batch_status" id="batch_status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="all" {{ $filters['batch_status'] == 'all' ? 'selected' : '' }}>All Batches</option>
                                    <option value="active" {{ $filters['batch_status'] == 'active' ? 'selected' : '' }}>Active (Qty > 0)</option>
                                    <option value="inactive" {{ $filters['batch_status'] == 'inactive' ? 'selected' : '' }}>Inactive (Qty = 0)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Product Filters -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="lni lni-producthunt mr-2"></i>
                            Product Filters
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                                <input type="text" name="product_name" id="product_name"
                                       value="{{ $filters['product_name'] }}"
                                       placeholder="Search product..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1">SKU/Barcode</label>
                                <input type="text" name="barcode" id="barcode"
                                       value="{{ $filters['barcode'] }}"
                                       placeholder="Search barcode..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="category_id" id="category_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $filters['category_id'] == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <input type="text" name="brand" id="brand"
                                       value="{{ $filters['brand'] }}"
                                       placeholder="Search brand..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="generic_name" class="block text-sm font-medium text-gray-700 mb-1">Generic Name</label>
                                <input type="text" name="generic_name" id="generic_name"
                                       value="{{ $filters['generic_name'] }}"
                                       placeholder="Generic name..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Supplier & Stock Status Filters -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="lni lni-users mr-2"></i>
                            Supplier & Stock Status
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                                <select name="supplier_id" id="supplier_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">All Suppliers</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ $filters['supplier_id'] == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="stock_status" class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                                <select name="stock_status" id="stock_status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">All Stock Levels</option>
                                    <option value="in-stock" {{ $filters['stock_status'] == 'in-stock' ? 'selected' : '' }}>In Stock Only</option>
                                    <option value="low-stock" {{ $filters['stock_status'] == 'low-stock' ? 'selected' : '' }}>Low Stock (&lt; 10)</option>
                                    <option value="zero-stock" {{ $filters['stock_status'] == 'zero-stock' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="lni lni-sort-alpha-asc mr-2"></i>
                            Sort Options
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select name="sort_by" id="sort_by"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="expiry_date" {{ $filters['sort_by'] == 'expiry_date' ? 'selected' : '' }}>Expiry Date</option>
                                    <option value="product_name" {{ $filters['sort_by'] == 'product_name' ? 'selected' : '' }}>Product Name</option>
                                    <option value="batch_number" {{ $filters['sort_by'] == 'batch_number' ? 'selected' : '' }}>Batch Number</option>
                                </select>
                            </div>
                            <div>
                                <label for="sort_dir" class="block text-sm font-medium text-gray-700 mb-1">Sort Direction</label>
                                <select name="sort_dir" id="sort_dir"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="asc" {{ $filters['sort_dir'] == 'asc' ? 'selected' : '' }}>Ascending</option>
                                    <option value="desc" {{ $filters['sort_dir'] == 'desc' ? 'selected' : '' }}>Descending</option>
                                </select>
                            </div>
                            <div>
                                <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Items Per Page</label>
                                <select name="per_page" id="per_page"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="25" {{ $filters['per_page'] == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $filters['per_page'] == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $filters['per_page'] == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('reports.expiring-products') }}"
                           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Clear Filters
                        </a>
                        <button type="submit"
                                class="bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        <!-- Results Table -->
        <x-ui.card title="Expiring Products ({{ $expiringProducts->total() }} items)" padding="p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manufacture Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days to Expiry</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Value</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($expiringProducts as $batch)
                        @php
                            $daysToExpiry = $batch->days_until_expiry;
                            $isExpired = $daysToExpiry < 0;
                            $isCritical = $daysToExpiry >= 0 && $daysToExpiry <= 7;
                            $isWarning = $daysToExpiry > 7 && $daysToExpiry <= 30;
                            $totalValue = $batch->quantity * $batch->cost_price;
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isExpired ? 'bg-red-50' : ($isCritical ? 'bg-orange-50' : '') }}">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $batch->product->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        @if($batch->product->category)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $batch->product->category->name }}
                                            </span>
                                        @endif
                                        @if($batch->product->brand)
                                            <span class="ml-1 text-gray-600">{{ $batch->product->brand }}</span>
                                        @endif
                                    </div>
                                    @if($batch->product->generic_name)
                                        <div class="text-xs text-gray-500 mt-1">{{ $batch->product->generic_name }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $batch->batch_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $batch->manufacturing_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="font-medium {{ $isExpired ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $batch->expiry_date->format('M d, Y') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($isExpired)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Expired {{ format_number($daysToExpiry) }} days ago
                                    </span>
                                @elseif($isCritical)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                        {{ format_number($daysToExpiry) }} days
                                    </span>
                                @elseif($isWarning)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ format_number($daysToExpiry) }} days
                                    </span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ format_number($daysToExpiry) }} days
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-right">
                                <span class="font-medium {{ $batch->quantity == 0 ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ number_format($batch->quantity) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">
                                {{ format_currency($totalValue) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($batch->quantity == 0)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Depleted
                                    </span>
                                @elseif($batch->product->stock < 10)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        In Stock
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="lni lni-emoji-happy text-4xl text-gray-400 mb-2"></i>
                                    <p>No expiring products found with the current filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($expiringProducts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $expiringProducts->links() }}
                </div>
            @endif
        </x-ui.card>

        <!-- Expiry Trend Chart -->
        <x-ui.card title="Expiry Trend (Next 12 Months)" padding="p-6">
            <div class="h-80">
                <canvas id="expiryTrendChart"></canvas>
            </div>
        </x-ui.card>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        // Expiry Trend Chart
        const expiryTrendCtx = document.getElementById('expiryTrendChart').getContext('2d');
        const expiryTrendChart = new Chart(expiryTrendCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($expiryTrend->map(function($item) {
                    return date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                })) !!},
                datasets: [{
                    label: 'Batches Expiring',
                    data: {!! json_encode($expiryTrend->pluck('batch_count')) !!},
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                const quantity = {!! json_encode($expiryTrend->pluck('total_quantity')) !!}[index];
                                return 'Total Quantity: ' + quantity.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
@endpush
