@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Sales Trends & Analytics</h1>
                <p class="text-gray-600 mt-1">Revenue performance and sales patterns analysis</p>
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
                            <form action="{{ route('reports.sales-trends.export') }}" method="POST" class="block">
                                @csrf
                                <input type="hidden" name="export_type" value="excel">
                                <input type="hidden" name="period" value="{{ $period }}">
                                <input type="hidden" name="days" value="{{ $days }}">
                                <button type="submit" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <x-ui.icon name="excel" class="w-3 h-3 mr-3"></x-ui.icon>
                                    Export as Excel
                                </button>
                            </form>
                            <form action="{{ route('reports.sales-trends.export') }}" method="POST" class="block">
                                @csrf
                                <input type="hidden" name="export_type" value="csv">
                                <input type="hidden" name="period" value="{{ $period }}">
                                <input type="hidden" name="days" value="{{ $days }}">
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

        <!-- Filters -->
        <x-ui.card title="Report Filters" padding="p-6">
            <form action="{{ route('reports.sales-trends') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 mb-1">Time Period</label>
                    <select name="period" id="period" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>

                <div>
                    <label for="days" class="block text-sm font-medium text-gray-700 mb-1">Days to Show</label>
                    <select name="days" id="days" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="365" {{ $days == 365 ? 'selected' : '' }}>Last Year</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors w-full">
                        Update Report
                    </button>
                </div>
            </form>
        </x-ui.card>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Trend Chart -->
            <x-ui.card title="Revenue Trend" padding="p-6">
                <div class="h-80">
                    <canvas id="revenueChart"></canvas>
                </div>
            </x-ui.card>

            <!-- Payment Method Distribution -->
            <x-ui.card title="Payment Methods" padding="p-6">
                <div class="h-80">
                    <canvas id="paymentChart"></canvas>
                </div>
            </x-ui.card>

            <!-- Top Products -->
            <x-ui.card title="Top Selling Products" padding="p-6" class="lg:col-span-2">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Units Sold</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topProducts as $index => $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-primary-700 font-semibold text-sm">{{ $index + 1 }}</span>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $product->category ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ number_format($product->total_sold) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">{{ format_currency($product->total_revenue) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    <i class="lni lni-inbox text-4xl mb-2 block text-gray-400"></i>
                                    No sales data available for this period.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        const CURRENCY_SYMBOL = '{{ $currency_symbol }}';
        // Revenue Trend Chart with gradient
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 400);
        revenueGradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        revenueGradient.addColorStop(1, 'rgba(59, 130, 246, 0.01)');

        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($salesData->pluck('date')->map(function($date) { return date('M d', strtotime($date)); })) !!},
                datasets: [{
                    label: `Revenue (${CURRENCY_SYMBOL})`,
                    data: {!! json_encode($salesData->pluck('revenue')) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: revenueGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#3b82f6',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
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
                        labels: {
                            font: {
                                size: 13,
                                weight: '500'
                            },
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
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
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return `Revenue: ${CURRENCY_SYMBOL} ` + context.parsed.y.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return `${CURRENCY_SYMBOL} ` + value.toLocaleString();
                            },
                            padding: 10
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            padding: 10
                        }
                    }
                }
            }
        });

        // Payment Method Chart with modern styling
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($paymentMethods->pluck('payment_method')->map(function($method) { return ucfirst($method); })) !!},
                datasets: [{
                    data: {!! json_encode($paymentMethods->pluck('amount')) !!},
                    backgroundColor: [
                        '#10b981',
                        '#3b82f6',
                        '#8b5cf6',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderWidth: 0,
                    hoverOffset: 15,
                    spacing: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 13,
                                weight: '500'
                            },
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
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
                        borderWidth: 0,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + `: ${CURRENCY_SYMBOL} ` + value.toLocaleString('en-PK', {minimumFractionDigits: 2}) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>
@endpush
