@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Sales by Cashier Report</h1>
                <p class="text-gray-600 mt-1">Cashier performance and sales analysis</p>
            </div>
            <div class="flex items-center space-x-4">

                <a href="{{ route('sales.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Sales
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
                            <form action="{{ route('reports.sales-by-cashier.export') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="start_date" value="{{ $startDate }}">
                                <input type="hidden" name="end_date" value="{{ $endDate }}">
                                <input type="hidden" name="cashier_id" value="{{ $cashierId }}">
                                <input type="hidden" name="export_type" value="excel">
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <x-ui.icon name="excel" class="w-3 h-3 mr-3"></x-ui.icon>
                                    Export as Excel
                                </button>
                            </form>

                            <form action="{{ route('reports.sales-by-cashier.export') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="start_date" value="{{ $startDate }}">
                                <input type="hidden" name="end_date" value="{{ $endDate }}">
                                <input type="hidden" name="cashier_id" value="{{ $cashierId }}">
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
            <form action="{{ route('reports.sales-by-cashier') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

                <div>
                    <label for="cashier_id" class="block text-sm font-medium text-gray-700 mb-1">Cashier</label>
                    <select name="cashier_id" id="cashier_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Cashiers</option>
                        @foreach($allCashiers as $cashier)
                            <option value="{{ $cashier->id }}" {{ $cashierId == $cashier->id ? 'selected' : '' }}>
                                {{ $cashier->name }} - {{ $cashier->role->display_name ?? 'Cashier' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors w-full">
                        Generate Report
                    </button>
                </div>
            </form>
        </x-ui.card>

        <!-- Cashier Performance Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $totalSales = $cashierPerformance->sum('total_sales');
                $totalRevenue = $cashierPerformance->sum('total_revenue');
                $avgSale = $totalSales > 0 ? $totalRevenue / $totalSales : 0;
                $topCashier = $cashierPerformance->sortByDesc('total_revenue')->first();
            @endphp

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-users text-2xl text-blue-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $cashierPerformance->count() }}</p>
                <p class="text-sm text-gray-600">Active Cashiers</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-cart text-2xl text-green-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalSales) }}</p>
                <p class="text-sm text-gray-600">Total Transactions</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-revenue text-2xl text-purple-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-green-600">{{ format_currency($totalRevenue) }}</p>
                <p class="text-sm text-gray-600">Total Revenue</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="lni lni-stats-up text-2xl text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ format_currency($avgSale) }}</p>
                <p class="text-sm text-gray-600">Average Sale</p>
            </x-ui.card>
        </div>

        <!-- Cashier Performance Table and Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Performance Table -->
            <x-ui.card title="Cashier Performance" padding="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Sale</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($cashierPerformance->sortByDesc('total_revenue') as $index => $cashier)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full font-semibold text-sm
                                        {{ $index === 0 ? 'bg-yellow-100 text-yellow-800' : ($index === 1 ? 'bg-gray-200 text-gray-700' : ($index === 2 ? 'bg-orange-100 text-orange-800' : 'bg-blue-50 text-blue-600')) }}">
                                        {{ $index + 1 }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-white font-semibold text-sm">{{ strtoupper(substr($cashier->name, 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $cashier->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $cashier->role->display_name ?? 'Cashier' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                    {{ number_format($cashier->total_sales) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-green-600">
                                    {{ format_currency($cashier->total_revenue) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-900">
                                    {{ format_currency($cashier->average_sale) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    No cashier data available for the selected period.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <!-- Revenue by Cashier Chart -->
            <x-ui.card title="Revenue Distribution" padding="p-6">
                <div class="h-80">
                    <canvas id="cashierRevenueChart"></canvas>
                </div>
            </x-ui.card>
        </div>

        <!-- Sales Trend by Cashier -->
        @if($cashierTrends->count() > 0)
        <x-ui.card title="Sales Trends by Cashier" padding="p-6">
            <div class="h-96">
                <canvas id="cashierTrendsChart"></canvas>
            </div>
        </x-ui.card>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        // Revenue by Cashier Chart (Doughnut)
        const revenueCtx = document.getElementById('cashierRevenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($cashierPerformance->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($cashierPerformance->pluck('total_revenue')) !!},
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#ec4899',
                        '#06b6d4'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
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

        // Sales Trends by Cashier (Line Chart)
        @if($cashierTrends->count() > 0)
        const trendsCtx = document.getElementById('cashierTrendsChart').getContext('2d');

        // Group trends by cashier
        const cashierData = {};
        @foreach($cashierPerformance as $cashier)
            cashierData[{{ $cashier->id }}] = {
                name: '{{ $cashier->name }}',
                data: []
            };
        @endforeach

        // Get unique dates
        const dates = {!! json_encode($cashierTrends->pluck('date')->unique()->sort()->values()) !!};

        // Populate data for each cashier
        @foreach($cashierTrends as $trend)
            if (cashierData[{{ $trend->cashier_id }}]) {
                const dateIndex = dates.indexOf('{{ $trend->date }}');
                if (dateIndex !== -1) {
                    cashierData[{{ $trend->cashier_id }}].data[dateIndex] = {{ $trend->revenue }};
                }
            }
        @endforeach

        // Fill missing dates with 0
        Object.keys(cashierData).forEach(cashierId => {
            dates.forEach((date, index) => {
                if (!cashierData[cashierId].data[index]) {
                    cashierData[cashierId].data[index] = 0;
                }
            });
        });

        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];
        const datasets = Object.keys(cashierData).map((cashierId, index) => ({
            label: cashierData[cashierId].name,
            data: cashierData[cashierId].data,
            borderColor: colors[index % colors.length],
            backgroundColor: colors[index % colors.length] + '20',
            borderWidth: 2,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6
        }));

        const trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
                datasets: datasets
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
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rs. ' + context.parsed.y.toLocaleString();
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
                                return 'Rs. ' + value.toLocaleString();
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
        @endif
    </script>
@endpush
