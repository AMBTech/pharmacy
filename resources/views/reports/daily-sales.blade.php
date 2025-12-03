@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Daily Sales Report</h1>
                <p class="text-gray-600 mt-1">Detailed sales analysis for {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('reports.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Reports
                </a>

                <!-- Export Button -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="lni lni-download mr-2"></i>
                        Export Report
                        <i class="lni lni-chevron-down ml-2"></i>
                    </button>

                    <!-- Export Dropdown -->
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                        <div class="py-1">
                            <form action="{{ route('reports.daily-sales.export') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="export_type" value="excel">
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <x-ui.icon name="excel" class="w-3 h-3 mr-3"></x-ui.icon>
                                    Export as Excel
                                </button>
                            </form>

                            <form action="{{ route('reports.daily-sales.export') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="export_type" value="csv">
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <x-ui.icon name="csv" class="w-3 h-3 mr-3"></x-ui.icon>
                                    Export as CSV
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Selector -->
        <x-ui.card title="Select Date" padding="p-6">
            <form action="{{ route('reports.daily-sales') }}" method="GET" class="flex items-end gap-4">
                <div class="flex-1">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Report Date</label>
                    <input type="date" name="date" id="date"
                           value="{{ $date }}"
                           max="{{ now()->format('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                    Generate Report
                </button>
            </form>
        </x-ui.card>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $totalTransactions = $dailySales->count();
                $totalRevenue = $dailySales->sum('total_amount');
                $totalTax = $dailySales->sum('tax_amount');
                $totalDiscount = $dailySales->sum('discount_amount');
                $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
            @endphp

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-cart text-2xl text-blue-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalTransactions) }}</p>
                <p class="text-sm text-gray-600">Total Transactions</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-revenue text-2xl text-green-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-green-600">{{ format_currency($totalRevenue) }}</p>
                <p class="text-sm text-gray-600">Total Revenue</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-stats-up text-2xl text-purple-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ format_currency($avgTransaction) }}</p>
                <p class="text-sm text-gray-600">Average Transaction</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-offer text-2xl text-orange-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-orange-600">{{ format_currency($totalDiscount) }}</p>
                <p class="text-sm text-gray-600">Total Discounts</p>
            </x-ui.card>
        </div>

        <!-- Hourly Sales Chart -->
        <x-ui.card title="Sales by Hour" padding="p-6">
            <div class="h-80">
                <canvas id="hourlySalesChart"></canvas>
            </div>
        </x-ui.card>

        <!-- Top Products and Payment Methods -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Products -->
            <x-ui.card title="Top Selling Products" padding="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Sold</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topProductsDaily as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $product->category ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($product->quantity_sold) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">{{ format_currency($product->revenue) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">No sales data available</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <!-- Payment Methods -->
            <x-ui.card title="Payment Methods" padding="p-6">
                @php
                    $paymentMethods = $dailySales->groupBy('payment_method')->map(function($sales, $method) {
                        return [
                            'method' => $method,
                            'count' => $sales->count(),
                            'amount' => $sales->sum('total_amount')
                        ];
                    });
                @endphp
                <div class="space-y-4">
                    @forelse($paymentMethods as $payment)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="lni lni-{{ $payment['method'] === 'cash' ? 'money-protection' : 'credit-cards' }} text-primary-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ ucfirst($payment['method']) }}</p>
                                    <p class="text-xs text-gray-500">{{ $payment['count'] }} transactions</p>
                                </div>
                            </div>
                            <p class="text-lg font-bold text-green-600">{{ format_currency($payment['amount']) }}</p>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">No payment data available</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <!-- Sales Transactions -->
        <x-ui.card title="Sales Transactions" padding="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dailySales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-primary-600">{{ $sale->invoice_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($sale->created_at)->format('h:i A') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $sale->cashier->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $sale->payment_method === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($sale->payment_method) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $sale->items->count() }}</td>
                            <td class="px-4 py-3 text-sm text-right text-orange-600">{{ format_currency($sale->discount_amount) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">{{ format_currency($sale->total_amount) }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('sales.show', $sale) }}"
                                   class="text-primary-600 hover:text-primary-700">
                                    <i class="lni lni-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">No sales transactions for this date</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        // Hourly Sales Chart
        const hourlySalesCtx = document.getElementById('hourlySalesChart').getContext('2d');

        // Prepare data for all 24 hours
        const hours = Array.from({length: 24}, (_, i) => i);
        const hourlyData = @json($hourlySales->keyBy('hour'));

        const chartData = hours.map(hour => {
            return hourlyData[hour] ? hourlyData[hour].revenue : 0;
        });

        const transactionCounts = hours.map(hour => {
            return hourlyData[hour] ? hourlyData[hour].transaction_count : 0;
        });

        new Chart(hourlySalesCtx, {
            type: 'bar',
            data: {
                labels: hours.map(h => h.toString().padStart(2, '0') + ':00'),
                datasets: [{
                    label: 'Revenue (Rs.)',
                    data: chartData,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'Transactions',
                    data: transactionCounts,
                    type: 'line',
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    yAxisID: 'y1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (Rs.)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Transactions'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.yAxisID === 'y') {
                                    label += 'Rs. ' + context.parsed.y.toLocaleString();
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
@endpush
