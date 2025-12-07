@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Analytics & Reports</h1>
                <p class="text-gray-600 mt-1">Comprehensive insights into your pharmacy performance</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-500 bg-white px-4 py-2 rounded-xl border border-gray-200 gap-2 flex flex-row">
{{--                    <i class="lni lni-calendar mr-2"></i>--}}
                    <x-ui.icon name="calendar"></x-ui.icon>
                    {{ now()->format('F j, Y') }}
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-ui.card class="border-l-4 border-l-primary-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mr-4">
{{--                        <i class="lni lni-shopping-cart text-primary-600 text-xl"></i>--}}
                        <x-ui.icon name="bar-chart-dollar"></x-ui.icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $currency_symbol }} {{ number_format($todaySales, 2) }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="border-l-4 border-l-success-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center mr-4">
{{--                        <i class="lni lni-stats-up text-success-600 text-xl"></i>--}}
                        <x-ui.icon name="trending-up"></x-ui.icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Monthly Sales</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $currency_symbol }} {{ number_format($monthSales, 2) }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="border-l-4 border-l-warning-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center mr-4">
{{--                        <i class="lni lni-exclamation-circle text-warning-600 text-xl"></i>--}}
                        <x-ui.icon name="alert-triangle"></x-ui.icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Low Stock Items</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lowStockProducts }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="border-l-4 border-l-danger-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-danger-100 rounded-xl flex items-center justify-center mr-4">
{{--                        <i class="lni lni-timer text-danger-600 text-xl"></i>--}}
                        <x-ui.icon name="clock"></x-ui.icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Expiring Soon</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $expiringSoon }}</p>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Report Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Sales Reports -->
            <x-ui.card title="Sales Reports" padding="p-6">
                <div class="space-y-3">
                    <a href="{{ route('reports.sales-trends') }}"
                       class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
{{--                            <i class="lni lni-stats-up text-blue-600"></i>--}}
                            <x-ui.icon name="trending-up"></x-ui.icon>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-blue-600">Sales Trends</h3>
                            <p class="text-sm text-gray-600">Revenue trends and performance analytics</p>
                        </div>
                        <i class="lni lni-arrow-right text-gray-400 group-hover:text-blue-600"></i>
                    </a>

                    <a href="{{ route('reports.sales-by-cashier') }}"
                       class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
{{--                            <i class="lni lni-users text-green-600"></i>--}}
                            <x-ui.icon name="users"></x-ui.icon>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-green-600">Sales by Cashier</h3>
                            <p class="text-sm text-gray-600">Performance analysis by staff members</p>
                        </div>
                        <i class="lni lni-arrow-right text-gray-400 group-hover:text-green-600"></i>
                    </a>

                    <a href="{{ route('reports.sales-by-category') }}"
                       class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="lni lni-tag text-purple-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-purple-600">Sales by Category</h3>
                            <p class="text-sm text-gray-600">Product category performance analysis</p>
                        </div>
                        <i class="lni lni-arrow-right text-gray-400 group-hover:text-purple-600"></i>
                    </a>

                    <a href="{{ route('reports.daily-sales') }}"
                       class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="lni lni-calendar text-orange-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-orange-600">Daily Sales Report</h3>
                            <p class="text-sm text-gray-600">Detailed daily transaction analysis</p>
                        </div>
                        <i class="lni lni-arrow-right text-gray-400 group-hover:text-orange-600"></i>
                    </a>
                </div>
            </x-ui.card>

            <!-- Inventory Reports -->
            <x-ui.card title="Inventory Reports" padding="p-6">
                <div class="space-y-3">
                    <a href="{{ route('reports.inventory') }}"
                       class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="lni lni-package text-indigo-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600">Inventory Overview</h3>
                            <p class="text-sm text-gray-600">Stock levels and valuation report</p>
                        </div>
                        <i class="lni lni-arrow-right text-gray-400 group-hover:text-indigo-600"></i>
                    </a>

                    <a href="{{ route('reports.expiring-products') }}"
                       class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
{{--                            <i class="lni lni-timer text-red-600"></i>--}}
                            <x-ui.icon name="clock"></x-ui.icon>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-red-600">Expiring Products</h3>
                            <p class="text-sm text-gray-600">Products nearing expiry date</p>
                        </div>
                        <i class="lni lni-arrow-right text-gray-400 group-hover:text-red-600"></i>
                    </a>
                </div>
            </x-ui.card>

            <!-- Financial Reports -->
            <x-ui.card title="Financial Reports" padding="p-6">
                <div class="space-y-3">
                    <a href="{{ route('reports.profit-loss') }}"
                       class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="lni lni-money-protection text-emerald-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-emerald-600">Profit & Loss</h3>
                            <p class="text-sm text-gray-600">Revenue, costs, and profitability analysis</p>
                        </div>
                        <i class="lni lni-arrow-right text-gray-400 group-hover:text-emerald-600"></i>
                    </a>
                </div>
            </x-ui.card>
        </div>

        <!-- Recent Activity -->
        <x-ui.card title="Recent Sales Activity" padding="p-6">
            <div class="space-y-4">
                @php
                    $recentSales = \App\Models\Sale::with('cashier')
                        ->latest()
                        ->limit(5)
                        ->get();
                @endphp

                @foreach($recentSales as $sale)
                    <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center mr-3">
{{--                                <i class="lni lni-receipt text-primary-600"></i>--}}
                                <x-ui.icon name="receipt"></x-ui.icon>
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
                            <p class="font-bold text-gray-900">{{ $currency_symbol }} {{ number_format($sale->total_amount, 2) }}</p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        {{ $sale->payment_method == 'cash' ? 'bg-green-100 text-green-800' :
                           ($sale->payment_method == 'card' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                        {{ ucfirst($sale->payment_method) }}
                    </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>
@endsection
