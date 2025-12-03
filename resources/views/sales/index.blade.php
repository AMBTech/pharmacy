@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Sales History</h1>
                <p class="text-gray-600 mt-1">View and manage all sales transactions</p>
            </div>
            <div class="flex items-center space-x-4">
                <x-ui.button variant="success" icon="lni lni-download" href="{{ route('sales.export.excel', request()->query()) }}">
                    Export
                </x-ui.button>
                <x-ui.button variant="success" icon="lni lni-printer" href="{{ route('pos.index') }}">
                    New Sale
                </x-ui.button>
            </div>
        </div>

        <!-- Sales Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card class="border-l-4 border-l-primary-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mr-4">
                        <x-ui.icon name="bar-chart-dollar"></x-ui.icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($todaySales, 2) }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="border-l-4 border-l-success-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="lni lni-stats-up text-success-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">This Month</p>
                        <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($monthSales, 2) }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="border-l-4 border-l-warning-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="lni lni-layers text-warning-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Transactions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalSales }}</p>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Filters -->
        <x-ui.card title="Filters" padding="p-6">
            <form action="{{ route('sales.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Date Range -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment
                        Method</label>
                    <select name="payment_method" id="payment_method"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="digital" {{ request('payment_method') == 'digital' ? 'selected' : '' }}>Digital
                        </option>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Invoice, Customer..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <!-- Filter Actions -->
                <div class="md:col-span-4 flex justify-between items-center pt-4 border-t border-gray-200">
                    <a href="{{ route('reports.sales-by-cashier') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="lni lni-bar-chart mr-2"></i>
                        Sales by Cashier Report
                    </a>
                    <div class="flex space-x-3">
                        <a href="{{ route('sales.index') }}"
                           class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            Clear
                        </a>
                        <button type="submit"
                                class="bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        <!-- Sales Table -->
        <x-ui.card title="Sales" padding="p-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Invoice #
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date & Time
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Amount
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Payment
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cashier
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sales as $sale)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{$sale->invoice_number ?? "N/A"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{format_date_time($sale->created_at) ?? "N/A"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="lni lni-user text-gray-400"></i>
                                        </div>
                                        <div>
                                            <div
                                                class="text-sm font-medium text-gray-900">{{ $sale->customer_name ?? "Walk-in Customer" }}</div>
                                            <div
                                                class="text-sm text-gray-500">{{ $sale->customer_phone ?? "N/A" }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{count($sale->items) ?? "0"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{format_currency($sale->total_amount) ?? "0"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{$sale->payment_method ?? "Cash"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{optional($sale->cashier)->name ?? "Unknown"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('sales.print', $sale) }}" target="_blank"
                                       class="text-green-600 hover:text-green-900 flex items-center text-sm font-bold">
                                        <i class="lni lni-download mr-1"></i> Download
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="lni lni-package text-4xl text-gray-300 mb-2 block"></i>
                                    No sales found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-ui.card>

        <!-- Pagination -->
        @if($sales->hasPages())
            <div class="bg-white px-6 py-4 border-t border-gray-200">
                {{ $sales->links() }}
            </div>
    @endif
@endsection
