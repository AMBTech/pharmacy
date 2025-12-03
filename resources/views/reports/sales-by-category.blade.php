@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Sales by Category Report</h1>
                <p class="text-gray-600 mt-1">Category performance and sales analysis</p>
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
                            <form action="{{ route('reports.sales-by-category.export') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="start_date" value="{{ $startDate }}">
                                <input type="hidden" name="end_date" value="{{ $endDate }}">
                                <input type="hidden" name="export_type" value="excel">
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <x-ui.icon name="excel" class="w-3 h-3 mr-3"></x-ui.icon>
                                    Export as Excel
                                </button>
                            </form>

                            <form action="{{ route('reports.sales-by-category.export') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="start_date" value="{{ $startDate }}">
                                <input type="hidden" name="end_date" value="{{ $endDate }}">
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

        <!-- Date Range Filter -->
        <x-ui.card title="Report Period" padding="p-6">
            <form action="{{ route('reports.sales-by-category') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $totalCategories = $categorySales->count();
                $totalRevenue = $categorySales->sum('revenue');
                $totalQuantitySold = $categorySales->sum('quantity_sold');
                $totalTransactions = $categorySales->sum('transaction_count');
                $topCategory = $categorySales->sortByDesc('revenue')->first();
            @endphp

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-grid-alt text-2xl text-blue-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $totalCategories }}</p>
                <p class="text-sm text-gray-600">Active Categories</p>
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
                        <i class="lni lni-package text-2xl text-purple-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalQuantitySold) }}</p>
                <p class="text-sm text-gray-600">Items Sold</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-crown text-2xl text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-lg font-bold text-gray-900">{{ $topCategory->category ?? 'N/A' }}</p>
                <p class="text-sm text-gray-600">Top Category</p>
            </x-ui.card>
        </div>

        <!-- Category Performance and Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Category Performance Table -->
            <x-ui.card title="Category Performance" padding="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Sold</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($categorySales->sortByDesc('revenue') as $category)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $category->category ?? 'Uncategorized' }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($category->quantity_sold) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">{{ format_currency($category->revenue) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($category->transaction_count) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">No category sales data available</td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">Total</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($totalQuantitySold) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-600">{{ format_currency($totalRevenue) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($totalTransactions) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </x-ui.card>

            <!-- Revenue Distribution Chart -->
            <x-ui.card title="Revenue Distribution" padding="p-6">
                <div class="h-80">
                    <canvas id="categoryRevenueChart"></canvas>
                </div>
            </x-ui.card>
        </div>

        <!-- Category Sales Trend -->
        @if($categoryTrend->count() > 0)
        <x-ui.card title="Category Sales Trend" padding="p-6">
            <div class="h-96">
                <canvas id="categoryTrendChart"></canvas>
            </div>
        </x-ui.card>
        @endif

        <!-- Detailed Category Breakdown -->
        <x-ui.card title="Detailed Category Breakdown" padding="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($categorySales->sortByDesc('revenue') as $category)
                    @php
                        $revenuePercentage = $totalRevenue > 0 ? ($category->revenue / $totalRevenue) * 100 : 0;
                        $avgPerTransaction = $category->transaction_count > 0 ? $category->revenue / $category->transaction_count : 0;
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-gray-900">{{ $category->category ?? 'Uncategorized' }}</h4>
                            <span class="px-2 py-1 text-xs font-medium bg-primary-100 text-primary-800 rounded-full">
                                {{ number_format($revenuePercentage, 1) }}%
                            </span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Revenue:</span>
                                <span class="text-sm font-semibold text-green-600">{{ format_currency($category->revenue) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Quantity Sold:</span>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($category->quantity_sold) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Transactions:</span>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($category->transaction_count) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Avg/Transaction:</span>
                                <span class="text-sm font-medium text-gray-900">{{ format_currency($avgPerTransaction) }}</span>
                            </div>
                        </div>
                        <!-- Progress Bar -->
                        <div class="mt-3 bg-gray-200 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $revenuePercentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        // Revenue Distribution Chart (Pie)
        const revenueCtx = document.getElementById('categoryRevenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($categorySales->pluck('category')) !!},
                datasets: [{
                    data: {!! json_encode($categorySales->pluck('revenue')) !!},
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                        '#06b6d4', '#ec4899', '#14b8a6', '#f97316', '#6366f1'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': Rs. ' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        @if($categoryTrend->count() > 0)
        // Category Trend Chart (Line)
        const trendCtx = document.getElementById('categoryTrendChart').getContext('2d');

        // Organize data by category
        const categories = [...new Set({!! json_encode($categoryTrend->pluck('category')) !!})];
        const dates = [...new Set({!! json_encode($categoryTrend->pluck('date')) !!})].sort();

        const datasets = categories.map((category, index) => {
            const categoryData = {!! json_encode($categoryTrend) !!}.filter(item => item.category === category);
            const dataPoints = dates.map(date => {
                const found = categoryData.find(item => item.date === date);
                return found ? found.revenue : 0;
            });

            const colors = [
                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                '#06b6d4', '#ec4899', '#14b8a6', '#f97316', '#6366f1'
            ];

            return {
                label: category,
                data: dataPoints,
                borderColor: colors[index % colors.length],
                backgroundColor: colors[index % colors.length] + '20',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            };
        });

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: datasets
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
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenue (Rs.)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rs. ' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
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
                                return context.dataset.label + ': Rs. ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        @endif
    </script>
@endpush
