@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Profit & Loss Statement</h1>
                <p class="text-gray-600 mt-1">Financial performance and profitability analysis</p>
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
                            <form action="{{ route('reports.profit-loss.export') }}" method="POST" class="block">
                                @csrf
                                <input type="hidden" name="export_type" value="excel">
                                <input type="hidden" name="start_date" value="{{ $startDate }}">
                                <input type="hidden" name="end_date" value="{{ $endDate }}">
                                <button type="submit" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <x-ui.icon name="excel" class="w-3 h-3 mr-3"></x-ui.icon>
                                    Export as Excel
                                </button>
                            </form>
                            <form action="{{ route('reports.profit-loss.export') }}" method="POST" class="block">
                                @csrf
                                <input type="hidden" name="export_type" value="csv">
                                <input type="hidden" name="start_date" value="{{ $startDate }}">
                                <input type="hidden" name="end_date" value="{{ $endDate }}">
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

        <!-- Date Range Filter -->
        <x-ui.card title="Report Period" padding="p-6">
            <form action="{{ route('reports.profit-loss') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date"
                           value="{{ $startDate }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" id="end_date"
                           value="{{ $endDate }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors w-full">
                        Generate Report
                    </button>
                </div>
            </form>
        </x-ui.card>

        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-revenue text-2xl text-blue-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ format_currency($profitLossData['revenue']) }}</p>
                <p class="text-sm text-gray-600">Total Revenue</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-cart text-2xl text-red-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ format_currency($profitLossData['cost_of_goods']) }}</p>
                <p class="text-sm text-gray-600">Cost of Goods Sold</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-emoji-smile text-2xl text-green-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-green-600">{{ format_currency($profitLossData['gross_profit']) }}</p>
                <p class="text-sm text-gray-600">Gross Profit</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-stats-up text-2xl text-purple-600"></i>
                    </div>
                </div>
                @php
                    $profitMargin = $profitLossData['revenue'] > 0 ? ($profitLossData['gross_profit'] / $profitLossData['revenue']) * 100 : 0;
                @endphp
                <p class="text-2xl font-bold text-purple-600">{{ number_format($profitMargin, 1) }}%</p>
                <p class="text-sm text-gray-600">Profit Margin</p>
            </x-ui.card>
        </div>

        <!-- Financial Statement -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- P&L Statement -->
            <x-ui.card title="Profit & Loss Statement" padding="p-6">
                <div class="space-y-4">
                    <!-- Revenue Section -->
                    <div>
                        <div class="flex justify-between py-3 border-b-2 border-gray-300">
                            <span class="font-bold text-gray-900">Revenue</span>
                            <span class="font-bold text-gray-900">{{ format_currency($profitLossData['revenue']) }}</span>
                        </div>
                    </div>

                    <!-- Cost of Goods Sold -->
                    <div>
                        <div class="flex justify-between py-2">
                            <span class="text-gray-700">Cost of Goods Sold</span>
                            <span class="text-red-600">{{ format_currency($profitLossData['cost_of_goods']) }}</span>
                        </div>
                    </div>

                    <!-- Gross Profit -->
                    <div>
                        <div class="flex justify-between py-3 bg-green-50 px-3 rounded-lg">
                            <span class="font-semibold text-gray-900">Gross Profit</span>
                            <span class="font-semibold text-green-600">{{ format_currency($profitLossData['gross_profit']) }}</span>
                        </div>
                    </div>

                    <!-- Operating Expenses -->
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between py-2 text-sm font-medium text-gray-700">
                            <span>Operating Expenses</span>
                            <span></span>
                        </div>
                        <div class="flex justify-between py-2 pl-4">
                            <span class="text-gray-600">Discounts Given</span>
                            <span class="text-gray-900">{{ format_currency($profitLossData['discount_given']) }}</span>
                        </div>
                        <div class="flex justify-between py-2 pl-4">
                            <span class="text-gray-600">Tax Collected</span>
                            <span class="text-gray-900">{{ format_currency($profitLossData['tax_collected']) }}</span>
                        </div>
                    </div>

                    <!-- Net Profit -->
                    <div class="border-t-2 border-gray-300 pt-4">
                        <div class="flex justify-between py-3 bg-purple-50 px-3 rounded-lg">
                            <span class="font-bold text-lg text-gray-900">Net Profit</span>
                            <span class="font-bold text-lg text-purple-600">{{ format_currency($profitLossData['net_profit']) }}</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <!-- Revenue & Expenses Trend -->
            <x-ui.card title="Revenue & Expenses Trend" padding="p-6">
                <div class="h-80">
                    <canvas id="revenueExpensesChart"></canvas>
                </div>
            </x-ui.card>
        </div>

        <!-- Profit by Category -->
        <x-ui.card title="Profit by Category" padding="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Chart -->
                <div class="h-80">
                    <canvas id="profitByCategoryChart"></canvas>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($profitByCategory as $category)
                            @php
                                $margin = $category->revenue > 0 ? ($category->profit / $category->revenue) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $category->category ?? 'Uncategorized' }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ format_currency($category->revenue) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-red-600">{{ format_currency($category->cost) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">{{ format_currency($category->profit) }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $margin >= 30 ? 'bg-green-100 text-green-800' : ($margin >= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ number_format($margin, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    No data available for the selected period.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-ui.card>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        const CURRENCY_SYMBOL = '{{$currency_symbol}}';
        // Revenue & Expenses Trend Chart
        const revenueExpensesCtx = document.getElementById('revenueExpensesChart').getContext('2d');
        const revenueExpensesChart = new Chart(revenueExpensesCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenueExpenses->pluck('date')->map(function($date) { return date('M d', strtotime($date)); })) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($revenueExpenses->pluck('revenue')) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }, {
                    label: 'Discount',
                    data: {!! json_encode($revenueExpenses->pluck('discount')) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 13,
                                weight: '500'
                            },
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + `: ${CURRENCY_SYMBOL} ` + context.parsed.y.toLocaleString('en-PK', {minimumFractionDigits: 2});
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
                            callback: function(value) {
                                return `${CURRENCY_SYMBOL} ` + value.toLocaleString();
                            }
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

        // Profit by Category Chart
        const profitCategoryCtx = document.getElementById('profitByCategoryChart').getContext('2d');
        const profitCategoryChart = new Chart(profitCategoryCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($profitByCategory->pluck('category')->map(function($cat) { return $cat ?? 'Uncategorized'; })) !!},
                datasets: [{
                    label: 'Profit',
                    data: {!! json_encode($profitByCategory->pluck('profit')) !!},
                    backgroundColor: [
                        '#10b981',
                        '#3b82f6',
                        '#8b5cf6',
                        '#f59e0b',
                        '#ef4444',
                        '#06b6d4',
                        '#ec4899'
                    ],
                    borderWidth: 0,
                    borderRadius: 8
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
                            label: function(context) {
                                return `Profit: ${CURRENCY_SYMBOL} ` + context.parsed.y.toLocaleString('en-PK', {minimumFractionDigits: 2});
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
                            callback: function(value) {
                                return `${CURRENCY_SYMBOL} ` + value.toLocaleString();
                            }
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
