@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Purchase Orders</h1>
                <p class="text-gray-600 mt-1">Manage incoming products and supplier orders</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('purchases.suppliers.index') }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-users mr-2"></i>
                    Suppliers
                </a>
                <a href="{{ route('purchases.create') }}"
                   class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center">
                    <i class="lni lni-plus mr-2"></i>
                    New Purchase Order
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $purchaseOrders->total() }}</p>
                <p class="text-sm text-gray-600">Total Orders</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-blue-600">
                    {{ \App\Models\PurchaseOrder::where('status', 'ordered')->count() }}
                </p>
                <p class="text-sm text-gray-600">Ordered</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-yellow-600">
                    {{ \App\Models\PurchaseOrder::where('status', 'partial')->count() }}
                </p>
                <p class="text-sm text-gray-600">Partial</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-green-600">
                    {{ \App\Models\PurchaseOrder::where('status', 'received')->count() }}
                </p>
                <p class="text-sm text-gray-600">Received</p>
            </x-ui.card>
        </div>

        <!-- Purchase Orders Table -->
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">PO Number</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Supplier</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Order Date</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Expected Delivery</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Total Amount</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($purchaseOrders as $order)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4">
                                <a href="{{ route('purchases.show', $order) }}"
                                   class="font-medium text-primary-600 hover:text-primary-700">
                                    {{ $order->po_number }}
                                </a>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="lni lni-user text-gray-400"></i>
                                    </div>
                                    <span class="font-medium">{{ $order->supplier->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-gray-600">
                                {{ $order->order_date->format('M d, Y') }}
                            </td>
                            <td class="py-4 px-4 text-gray-600">
                                {{ $order->expected_delivery_date ? $order->expected_delivery_date->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="py-4 px-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $order->status_color }}">
                                <i class="lni {{ $order->status_icon }} mr-1"></i>
                                {{ ucfirst($order->status) }}
                            </span>
                            </td>
                            <td class="py-4 px-4 font-semibold text-gray-900">
                                {{ format_currency($order->total) }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('purchases.show', $order) }}"
                                       class="text-gray-600 hover:text-gray-700 p-2 rounded hover:bg-gray-100"
                                       title="View">
                                        <i class="lni lni-eye"></i>
                                    </a>

                                    @if($order->status === 'draft')
                                        <a href="{{ route('purchases.edit', $order) }}"
                                           class="text-gray-600 hover:text-gray-700 p-2 rounded hover:bg-gray-100"
                                           title="Edit">
                                            <i class="lni lni-pencil"></i>
                                        </a>
                                    @endif

                                    @if(in_array($order->status, ['ordered', 'partial']))
                                        <a href="{{ route('purchases.receive', $order) }}"
                                           class="text-green-600 hover:text-green-700 p-2 rounded hover:bg-green-50"
                                           title="Receive Items">
                                            <i class="lni lni-checkmark-circle"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($purchaseOrders->hasPages())
                <div class="px-4 py-4 border-t border-gray-200">
                    {{ $purchaseOrders->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
@endsection
