@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-600 mt-1">Welcome back! Here's what's happening today.</p>
            </div>
            <div class="text-sm text-gray-500 bg-white px-4 py-2 rounded-xl border border-gray-200">
                <i class="lni lni-calendar mr-2"></i>
                {{ now()->format('l, F j, Y') }}
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Today's Sales -->
            <x-ui.card class="border-l-4 border-l-primary-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mr-4">
{{--                        <i class="lni lni-shopping-cart text-primary-600 text-xl"></i>--}}
                        <x-ui.icon name="tag" class="w-5 h-5"></x-ui.icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($todaySales, 2) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm {{ $todayChange >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                    <i class="lni lni-{{ $todayChange >= 0 ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                    <span>{{ abs(round($todayChange, 1)) }}% from yesterday</span>
                </div>
            </x-ui.card>

            <!-- Total Products -->
            <x-ui.card class="border-l-4 border-l-blue-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="lni lni-package text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Products</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalProducts }}</p>
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    {{ $activeProducts }} active • {{ $lowStockProducts }} low stock
                </div>
            </x-ui.card>

            <!-- Expiring Soon -->
            <x-ui.card class="border-l-4 border-l-warning-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="lni lni-timer text-warning-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Expiring Soon</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $expiringSoon }}</p>
                    </div>
                </div>
                <div class="mt-4 text-sm text-danger-600">
                    Within 30 days
                </div>
            </x-ui.card>

            <!-- Monthly Revenue -->
            <x-ui.card class="border-l-4 border-l-purple-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="lni lni-stats-up text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Monthly Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($monthSales, 2) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm {{ $monthChange >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                    <i class="lni lni-{{ $monthChange >= 0 ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                    <span>{{ abs(round($monthChange, 1)) }}% from last month</span>
                </div>
            </x-ui.card>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Sales -->
            <x-ui.card title="Recent Sales" padding="p-6">
                <div class="space-y-4">
                    @forelse($recentSales as $sale)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-primary-300 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
{{--                                    <i class="lni lni-receipt text-primary-600"></i>--}}
                                    <x-ui.icon name="report" class="w-5 h-5"></x-ui.icon>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $sale->invoice_number }}</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $sale->customer_name ?: 'Walk-in Customer' }} •
                                        {{ $sale->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">Rs. {{ number_format($sale->total_amount, 2) }}</p>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            {{ $sale->payment_method == 'cash' ? 'bg-green-100 text-green-800' :
                               ($sale->payment_method == 'card' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                            {{ ucfirst($sale->payment_method) }}
                        </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="lni lni-receipt text-4xl mb-2"></i>
                            <p>No recent sales</p>
                        </div>
                    @endforelse
                </div>

                @if($recentSales->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('sales.index') }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm flex items-center justify-center">
                            View All Sales
                            <i class="lni lni-arrow-right ml-1"></i>
                        </a>
                    </div>
                @endif
            </x-ui.card>

            <!-- Stock Alerts -->
            <x-ui.card title="Stock Alerts" padding="p-6">
                <div class="space-y-3">
                    @forelse($lowStockAlerts as $product)
                        <div class="flex items-center justify-between p-3 border-l-4 border-warning-500 bg-warning-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="lni lni-exclamation-circle text-warning-500 mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-sm text-gray-600">
                                        Stock: {{ $product->stock }} {{ $product->unit }} •
                                        @if($product->category)
                                            <span class="text-xs px-2 py-1 bg-gray-100 rounded">{{ $product->category->name }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('inventory.edit', $product) }}"
                               class="text-warning-600 hover:text-warning-700 font-medium text-sm">
                                Restock
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="lni lni-checkmark-circle text-4xl mb-2"></i>
                            <p>All products have sufficient stock</p>
                        </div>
                    @endforelse
                </div>

                @if($lowStockAlerts->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('inventory.index') }}?stock_status=low" class="text-warning-600 hover:text-warning-700 font-medium text-sm flex items-center justify-center">
                            View All Low Stock Items
                            <i class="lni lni-arrow-right ml-1"></i>
                        </a>
                    </div>
                @endif
            </x-ui.card>

            <!-- Recent Products -->
            <x-ui.card title="Recently Added Products" padding="p-6">
                <div class="space-y-4">
                    @forelse($recentProducts as $product)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-green-300 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="lni lni-package text-green-600"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ $product->name }}</h4>
                                    <p class="text-sm text-gray-600">
                                        @if($product->category)
                                            <span class="text-xs px-2 py-1 bg-gray-100 rounded mr-2">{{ $product->category->name }}</span>
                                        @endif
                                        Stock: {{ $product->stock }} {{ $product->unit }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">Rs. {{ number_format($product->price, 2) }}</p>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            {{ $product->stock == 0 ? 'bg-red-100 text-red-800' :
                               ($product->stock < 10 ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800') }}">
                            {{ $product->stock == 0 ? 'Out of Stock' : ($product->stock < 10 ? 'Low Stock' : 'In Stock') }}
                        </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="lni lni-package text-4xl mb-2"></i>
                            <p>No products added yet</p>
                        </div>
                    @endforelse
                </div>

                @if($recentProducts->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('inventory.index') }}" class="text-green-600 hover:text-green-700 font-medium text-sm flex items-center justify-center">
                            View All Products
                            <i class="lni lni-arrow-right ml-1"></i>
                        </a>
                    </div>
                @endif
            </x-ui.card>

            <!-- Expiring Products -->
            <x-ui.card title="Expiring Soon" padding="p-6">
                <div class="space-y-3">
                    @forelse($expiringProducts as $batch)
                        <div class="flex items-center justify-between p-3 border-l-4 border-danger-500 bg-danger-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="lni lni-timer text-danger-500 mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $batch->product->name }}</p>
                                    <p class="text-sm text-gray-600">
                                        Batch: {{ $batch->batch_number }} •
                                        Expires: {{ $batch->expiry_date->format('M d, Y') }} •
                                        Qty: {{ $batch->quantity }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-800">
                            {{ $batch->expiry_date->diffForHumans() }}
                        </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="lni lni-checkmark-circle text-4xl mb-2"></i>
                            <p>No products expiring soon</p>
                        </div>
                    @endforelse
                </div>

                @if($expiringProducts->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('reports.expiring-products') }}" class="text-danger-600 hover:text-danger-700 font-medium text-sm flex items-center justify-center">
                            View All Expiring Products
                            <i class="lni lni-arrow-right ml-1"></i>
                        </a>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- Quick Actions -->
        <x-ui.card title="Quick Actions" padding="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-ui.action-button variant="primary" href="{{ route('pos.index') }}" class="flex-col h-24">
                    <i class="lni lni-cart text-2xl mb-2"></i>
{{--                    <x-ui.icon name="nav/cart" class="white"></x-ui.icon>--}}
                    <span>New Sale</span>
                </x-ui.action-button>

                <x-ui.action-button variant="blue" href="{{ route('inventory.create') }}" class="flex-col h-24">
                    <i class="lni lni-plus text-2xl mb-2"></i>
                    <span>Add Product</span>
                </x-ui.action-button>

                <x-ui.action-button variant="warning" href="{{ route('inventory.index') }}" class="flex-col h-24">
                    <i class="lni lni-package text-2xl mb-2"></i>
                    <span>Manage Stock</span>
                </x-ui.action-button>

                <x-ui.action-button variant="secondary" href="{{ route('reports.index') }}" class="flex-col h-24">
                    <i class="lni lni-stats-up text-2xl mb-2"></i>
                    <span>View Reports</span>
                </x-ui.action-button>
            </div>
        </x-ui.card>
    </div>
@endsection
