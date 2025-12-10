@extends('layouts.app')

@section('title', 'Transaction Statistics')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Transaction Statistics</h1>
                <p class="text-gray-600">Financial overview and transaction analytics</p>
            </div>
            <div class="flex space-x-3">
                <!-- Date Range Filter -->
                <div class="relative">
                    <button id="dateRangeButton"
                            class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="lni lni-calendar mr-2"></i>
                        <span id="dateRangeText">{{ date('M d, Y') }} - {{ date('M d, Y') }}</span>
                        <i class="lni lni-chevron-down ml-2"></i>
                    </button>
                    <div id="dateRangeDropdown"
                         class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border z-10">
                        <div class="p-4">
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                                <input type="date" id="fromDate" class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                                <input type="date" id="toDate" class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div class="flex justify-between">
                                <button onclick="applyDateRange()"
                                        class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                    Apply
                                </button>
                                <button onclick="resetDateRange()"
                                        class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50 text-sm">
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Button -->
                <button onclick="exportStatistics()"
                        class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="lni lni-download mr-2"></i>
                    Export
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Today's Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Today</h3>
                    <span class="text-sm text-gray-500">{{ date('D, M d') }}</span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Sales</span>
                        <span class="font-semibold text-green-600">₹{{ number_format($transactions['today']['sales'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Refunds</span>
                        <span class="font-semibold text-red-600">₹{{ number_format($transactions['today']['refunds'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Expenses</span>
                        <span class="font-semibold text-orange-600">₹{{ number_format($transactions['today']['expenses'], 2) }}</span>
                    </div>
                    <div class="pt-3 border-t">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Net</span>
                            <span class="text-lg font-bold {{ $transactions['today']['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ₹{{ number_format(abs($transactions['today']['net']), 2) }}
                                @if($transactions['today']['net'] >= 0)
                                    <i class="lni lni-arrow-up"></i>
                                @else
                                    <i class="lni lni-arrow-down"></i>
                                @endif
                        </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- This Week's Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">This Week</h3>
                    <span class="text-sm text-gray-500">{{ date('M d') }} - {{ date('M d', strtotime('+6 days')) }}</span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Sales</span>
                        <span class="font-semibold text-green-600">₹{{ number_format($transactions['this_week']['sales'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Refunds</span>
                        <span class="font-semibold text-red-600">₹{{ number_format($transactions['this_week']['refunds'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Expenses</span>
                        <span class="font-semibold text-orange-600">₹{{ number_format($transactions['this_week']['expenses'], 2) }}</span>
                    </div>
                    <div class="pt-3 border-t">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Net</span>
                            <span class="text-lg font-bold {{ $transactions['this_week']['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ₹{{ number_format(abs($transactions['this_week']['net']), 2) }}
                                @if($transactions['this_week']['net'] >= 0)
                                    <i class="lni lni-arrow-up"></i>
                                @else
                                    <i class="lni lni-arrow-down"></i>
                                @endif
                        </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- This Month's Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">This Month</h3>
                    <span class="text-sm text-gray-500">{{ date('M Y') }}</span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Sales</span>
                        <span class="font-semibold text-green-600">₹{{ number_format($transactions['this_month']['sales'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Refunds</span>
                        <span class="font-semibold text-red-600">₹{{ number_format($transactions['this_month']['refunds'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Expenses</span>
                        <span class="font-semibold text-orange-600">₹{{ number_format($transactions['this_month']['expenses'], 2) }}</span>
                    </div>
                    <div class="pt-3 border-t">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Net</span>
                            <span class="text-lg font-bold {{ $transactions['this_month']['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ₹{{ number_format(abs($transactions['this_month']['net']), 2) }}
                                @if($transactions['this_month']['net'] >= 0)
                                    <i class="lni lni-arrow-up"></i>
                                @else
                                    <i class="lni lni-arrow-down"></i>
                                @endif
                        </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overall Summary -->
            @php
                $totalSales = $transactions['this_month']['sales'] ?? 0;
                $totalRefunds = $transactions['this_month']['refunds'] ?? 0;
                $totalExpenses = $transactions['this_month']['expenses'] ?? 0;
                $totalNet = $totalSales - $totalRefunds - $totalExpenses;
                $growthRate = 0; // You can calculate this from previous month data
            @endphp
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Overall</h3>
                    <span class="text-sm px-2 py-1 bg-blue-100 text-blue-800 rounded-full">{{ $growthRate >= 0 ? '+' : '' }}{{ $growthRate }}%</span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Sales</span>
                        <span class="font-semibold text-green-600">₹{{ number_format($totalSales, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Refunds</span>
                        <span class="font-semibold text-red-600">₹{{ number_format($totalRefunds, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Expenses</span>
                        <span class="font-semibold text-orange-600">₹{{ number_format($totalExpenses, 2) }}</span>
                    </div>
                    <div class="pt-3 border-t">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Total Net</span>
                            <span class="text-lg font-bold {{ $totalNet >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ₹{{ number_format(abs($totalNet), 2) }}
                        </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Sales vs Refunds Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Sales vs Refunds Trend</h3>
                    <select id="trendPeriod" class="text-sm border-gray-300 rounded-md">
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
                <div class="h-64">
                    <canvas id="salesRefundsChart"></canvas>
                </div>
            </div>

            <!-- Payment Methods Distribution -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Payment Methods Distribution</h3>
                <div class="h-64">
                    <canvas id="paymentMethodsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Transaction Types Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction Types</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600">Sales</span>
                            <span class="text-sm font-medium">{{ $totalSales > 0 ? round(($totalSales / ($totalSales + $totalRefunds + $totalExpenses)) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full"
                                 style="width: {{ $totalSales > 0 ? ($totalSales / ($totalSales + $totalRefunds + $totalExpenses)) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600">Refunds</span>
                            <span class="text-sm font-medium">{{ $totalRefunds > 0 ? round(($totalRefunds / ($totalSales + $totalRefunds + $totalExpenses)) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full"
                                 style="width: {{ $totalRefunds > 0 ? ($totalRefunds / ($totalSales + $totalRefunds + $totalExpenses)) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600">Expenses</span>
                            <span class="text-sm font-medium">{{ $totalExpenses > 0 ? round(($totalExpenses / ($totalSales + $totalRefunds + $totalExpenses)) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-orange-600 h-2 rounded-full"
                                 style="width: {{ $totalExpenses > 0 ? ($totalExpenses / ($totalSales + $totalRefunds + $totalExpenses)) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performing Days -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Performing Days</h3>
                <div class="space-y-3">
                    @php
                        $topDays = [
                            ['day' => 'Monday', 'amount' => 4500],
                            ['day' => 'Tuesday', 'amount' => 5200],
                            ['day' => 'Wednesday', 'amount' => 3800],
                            ['day' => 'Thursday', 'amount' => 6100],
                            ['day' => 'Friday', 'amount' => 7300],
                        ];
                    @endphp
                    @foreach($topDays as $day)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ $day['day'] }}</span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                         style="width: {{ ($day['amount'] / 10000) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-medium">₹{{ number_format($day['amount'], 0) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                    <a href="{{ route('transactions.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        View All
                    </a>
                </div>
                <div class="space-y-3">
                    @php
                        $recentTransactions = [
                            ['type' => 'sale', 'amount' => 2500, 'time' => '2 hours ago'],
                            ['type' => 'refund', 'amount' => 500, 'time' => '4 hours ago'],
                            ['type' => 'sale', 'amount' => 1200, 'time' => '6 hours ago'],
                            ['type' => 'expense', 'amount' => 300, 'time' => '1 day ago'],
                            ['type' => 'sale', 'amount' => 1800, 'time' => '1 day ago'],
                        ];
                    @endphp
                    @foreach($recentTransactions as $transaction)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3
                            {{ $transaction['type'] == 'sale' ? 'bg-green-100 text-green-600' :
                               ($transaction['type'] == 'refund' ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600') }}">
                                    <i class="lni
                                {{ $transaction['type'] == 'sale' ? 'lni-arrow-up' :
                                   ($transaction['type'] == 'refund' ? 'lni-arrow-down' : 'lni-wallet') }}"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium capitalize">{{ $transaction['type'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction['time'] }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold
                        {{ $transaction['type'] == 'sale' ? 'text-green-600' :
                           ($transaction['type'] == 'refund' ? 'text-red-600' : 'text-orange-600') }}">
                        {{ $transaction['type'] == 'sale' ? '+' : '-' }}₹{{ number_format($transaction['amount'], 2) }}
                    </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Detailed Statistics Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Detailed Transaction Statistics</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Refunds</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expenses</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Transaction</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $periods = [
                            'Today' => $transactions['today'],
                            'This Week' => $transactions['this_week'],
                            'This Month' => $transactions['this_month'],
                        ];
                    @endphp
                    @foreach($periods as $periodName => $periodData)
                        @php
                            $transactionCount = [
                                'today' => 15,
                                'this_week' => 85,
                                'this_month' => 320,
                            ][strtolower(str_replace(' ', '_', $periodName))] ?? 0;

                            $avgTransaction = $periodData['sales'] > 0 ? $periodData['sales'] / $transactionCount : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">{{ $periodName }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-green-600 font-medium">₹{{ number_format($periodData['sales'], 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-red-600 font-medium">₹{{ number_format($periodData['refunds'], 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-orange-600 font-medium">₹{{ number_format($periodData['expenses'], 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-bold {{ $periodData['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ₹{{ number_format($periodData['net'], 2) }}
                            </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-700">{{ $transactionCount }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-blue-600 font-medium">₹{{ number_format($avgTransaction, 2) }}</span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Date Range Picker
        document.getElementById('dateRangeButton').addEventListener('click', function() {
            document.getElementById('dateRangeDropdown').classList.toggle('hidden');
        });

        function applyDateRange() {
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;

            if (fromDate && toDate) {
                const from = new Date(fromDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                const to = new Date(toDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                document.getElementById('dateRangeText').textContent = `${from} - ${to}`;

                // Here you would typically make an AJAX request to update the statistics
                // fetchStatistics(fromDate, toDate);
            }

            document.getElementById('dateRangeDropdown').classList.add('hidden');
        }

        function resetDateRange() {
            document.getElementById('fromDate').value = '';
            document.getElementById('toDate').value = '';
            document.getElementById('dateRangeText').textContent = '{{ date("M d, Y") }} - {{ date("M d, Y") }}';
            document.getElementById('dateRangeDropdown').classList.add('hidden');
        }

        // Export Functionality
        function exportStatistics() {
            // Implement export to CSV/Excel functionality
            alert('Export functionality would be implemented here.');
        }

        // Charts Initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Sales vs Refunds Chart
            const salesRefundsCtx = document.getElementById('salesRefundsChart').getContext('2d');
            const salesRefundsChart = new Chart(salesRefundsCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [
                        {
                            label: 'Sales',
                            data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Refunds',
                            data: [2000, 3000, 2500, 4000, 3500, 5000, 4500],
                            borderColor: '#EF4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Payment Methods Chart
            const paymentMethodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
            const paymentMethodsChart = new Chart(paymentMethodsCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Cash', 'Credit Card', 'Bank Transfer', 'Digital Wallet'],
                    datasets: [{
                        data: [45, 30, 15, 10],
                        backgroundColor: [
                            '#F59E0B', // Amber
                            '#3B82F6', // Blue
                            '#8B5CF6', // Violet
                            '#10B981'  // Green
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Period Selector for Trend Chart
            document.getElementById('trendPeriod').addEventListener('change', function(e) {
                // Here you would update the chart data based on selected period
                console.log('Period changed to:', e.target.value);
                // You would typically make an AJAX request to fetch new data
            });
        });

        // Close date picker when clicking outside
        document.addEventListener('click', function(event) {
            const dateRangeButton = document.getElementById('dateRangeButton');
            const dateRangeDropdown = document.getElementById('dateRangeDropdown');

            if (!dateRangeButton.contains(event.target) && !dateRangeDropdown.contains(event.target)) {
                dateRangeDropdown.classList.add('hidden');
            }
        });
    </script>

    <style>
        /* Custom styles for better UI */
        #salesRefundsChart, #paymentMethodsChart {
            max-height: 250px;
        }

        .bg-green-100 { background-color: #D1FAE5; }
        .bg-red-100 { background-color: #FEE2E2; }
        .bg-orange-100 { background-color: #FFEDD5; }
        .bg-blue-100 { background-color: #DBEAFE; }

        .text-green-600 { color: #059669; }
        .text-red-600 { color: #DC2626; }
        .text-orange-600 { color: #EA580C; }
        .text-blue-600 { color: #2563EB; }
    </style>
@endsection
