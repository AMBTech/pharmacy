@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Purchase Order #{{ $purchase->po_number }}</h1>
                <p class="text-gray-600 mt-1">
                    Ordered on {{ $purchase->order_date->format('F j, Y') }}
                    @if($purchase->expected_delivery_date)
                        • Expected: {{ $purchase->expected_delivery_date->format('F j, Y') }}
                    @endif
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('purchases.index') }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Orders
                </a>

                @if($purchase->status === 'draft')
                    <a href="{{ route('purchases.edit', $purchase) }}"
                       class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                        <i class="lni lni-pencil mr-2"></i>
                        Edit
                    </a>
                    <form action="{{ route('purchases.mark-ordered', $purchase) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center">
                            <i class="lni lni-shopping-basket mr-2"></i>
                            Mark as Ordered
                        </button>
                    </form>
                @endif

                @if(in_array($purchase->status, ['ordered', 'partial']))
                    <a href="{{ route('purchases.receive', $purchase) }}"
                       class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center">
                        <i class="lni lni-checkmark-circle mr-2"></i>
                        Receive Items
                    </a>
                @endif
            </div>
        </div>

        <!-- Status & Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Status Card -->
            <x-ui.card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <div class="flex items-center mt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $purchase->status_color }}">
                            <i class="lni {{ $purchase->status_icon }} mr-1"></i>
                            {{ ucfirst($purchase->status) }}
                        </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="text-2xl font-bold text-gray-900">{{ format_currency($purchase->total) }}</p>
                    </div>
                </div>
            </x-ui.card>

            <!-- Supplier Info -->
            <x-ui.card>
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Supplier</h3>
                <div class="space-y-2">
                    <p class="font-medium">{{ $purchase->supplier->name }}</p>
                    @if($purchase->supplier->contact_person)
                        <p class="text-sm text-gray-600">{{ $purchase->supplier->contact_person }}</p>
                    @endif
                    @if($purchase->supplier->email)
                        <p class="text-sm text-gray-600">{{ $purchase->supplier->email }}</p>
                    @endif
                    @if($purchase->supplier->phone)
                        <p class="text-sm text-gray-600">{{ $purchase->supplier->phone }}</p>
                    @endif
                </div>
            </x-ui.card>

            <!-- Order Info -->
            <x-ui.card>
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Order Details</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Order Date:</span>
                        <span class="text-sm font-medium">{{ $purchase->order_date->format('M j, Y') }}</span>
                    </div>
                    @if($purchase->expected_delivery_date)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Expected Delivery:</span>
                            <span class="text-sm font-medium">{{ $purchase->expected_delivery_date->format('M j, Y') }}</span>
                        </div>
                    @endif
                    @if($purchase->delivery_date)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Delivered On:</span>
                            <span class="text-sm font-medium">{{ $purchase->delivery_date->format('M j, Y') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Created By:</span>
                        <span class="text-sm font-medium">{{ $purchase->user->name }}</span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Order Items -->
        <x-ui.card title="Order Items">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Product</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Quantity</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Unit Cost</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Total Cost</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Received</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Pending</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Batch</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Expiry</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($purchase->items as $item)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="lni lni-package text-gray-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $item->product->category->name ?? 'Uncategorized' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-gray-600">{{ $item->quantity }}</td>
                            <td class="py-4 px-4 font-medium">{{ format_currency($item->unit_cost) }}</td>
                            <td class="py-4 px-4 font-semibold">{{ format_currency($item->total_cost) }}</td>
                            <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $item->received_quantity == $item->quantity ? 'bg-green-100 text-green-800' :
                                   ($item->received_quantity > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $item->received_quantity }}
                            </span>
                            </td>
                            <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $item->quantity - $item->received_quantity }}
                            </span>
                            </td>
                            <td class="py-4 px-4 text-gray-600 text-sm">
                                {{ $item->batch_number ?: 'N/A' }}
                            </td>
                            <td class="py-4 px-4 text-gray-600 text-sm">
                                {{ $item->expiry_date ? $item->expiry_date->format('M j, Y') : 'N/A' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <!-- Order Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Notes -->
            @if($purchase->notes)
                <x-ui.card title="Notes">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $purchase->notes }}</p>
                </x-ui.card>
        @endif

        <!-- Financial Summary -->
            <x-ui.card title="Financial Summary">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">{{ format_currency($purchase->subtotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax</span>
                        <span class="font-medium">{{ format_currency($purchase->tax) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shipping Cost</span>
                        <span class="font-medium">{{ format_currency($purchase->shipping_cost) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Discount</span>
                        <span class="font-medium text-green-600">-{{ format_currency($purchase->discount) }}</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3">
                        <div class="flex justify-between font-semibold text-lg">
                            <span>Total Amount</span>
                            <span>{{ format_currency($purchase->total) }}</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection
