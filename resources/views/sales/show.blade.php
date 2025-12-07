@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto">
        <x-ui.card>
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Sale Details</h1>
                    <p class="text-gray-600 mt-1">Invoice #{{ $sale->invoice_number }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <x-ui.button variant="secondary" icon="lni lni-arrow-left" href="{{ route('sales.index') }}">
                        Back to Sales
                    </x-ui.button>
                    <x-ui.button variant="primary" icon="lni lni-printer"
                                 href="{{ route('sales.print', $sale) }}" target="_blank">
                        Print Invoice
                    </x-ui.button>
                </div>
            </div>

            <!-- Sale Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Sale Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Invoice Number</p>
                            <p class="font-semibold text-gray-900">{{ $sale->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Date & Time</p>
                            <p class="font-semibold text-gray-900">{{ $sale->created_at->format('M j, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Payment Method</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $sale->payment_method == 'cash' ? 'bg-green-100 text-green-800' :
                               ($sale->payment_method == 'card' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                            {{ ucfirst($sale->payment_method) }}
                        </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Cashier</p>
                            <p class="font-semibold text-gray-900">{{ $sale->cashier->name ?? 'System' }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Customer Information</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm text-gray-600">Customer Name</p>
                            <p class="font-semibold text-gray-900">{{ $sale->customer_name ?: 'Walk-in Customer' }}</p>
                        </div>
                        @if($sale->customer_phone)
                            <div>
                                <p class="text-sm text-gray-600">Phone Number</p>
                                <p class="font-semibold text-gray-900">{{ $sale->customer_phone }}</p>
                            </div>
                        @endif
                        @if($sale->notes)
                            <div>
                                <p class="text-sm text-gray-600">Notes</p>
                                <p class="text-sm text-gray-900">{{ $sale->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sale Items -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Items Sold</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($sale->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $item->product->generic_name ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->batch->batch_number ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{$currency_symbol}} {{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{$currency_symbol}} {{ number_format($item->total_price, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sale Summary -->
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="max-w-md ml-auto space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-900">{{$currency_symbol}} {{ number_format($sale->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax (8%)</span>
                        <span class="text-gray-900">{{$currency_symbol}} {{ number_format($sale->tax_amount, 2) }}</span>
                    </div>
                    @if($sale->discount_amount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Discount</span>
                            <span class="text-red-600">- {{$currency_symbol}} {{ number_format($sale->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold border-t pt-3">
                        <span>Total Amount</span>
                        <span>{{$currency_symbol}} {{ number_format($sale->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
@endsection
