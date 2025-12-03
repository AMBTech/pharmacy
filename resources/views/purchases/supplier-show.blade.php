@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $supplier->name }}</h1>
                <p class="text-gray-600 mt-1">
                    Supplier since {{ $supplier->created_at->format('F j, Y') }}
                    • Total Purchases: ${{ number_format($supplier->total_purchases, 2) }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('purchases.suppliers.index') }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Suppliers
                </a>
                <a href="{{ route('purchases.suppliers.edit', $supplier) }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-pencil mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('purchases.create') }}?supplier={{ $supplier->id }}"
                   class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center">
                    <i class="lni lni-plus mr-2"></i>
                    New Order
                </a>
            </div>
        </div>

        <!-- Supplier Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Contact Info -->
            <x-ui.card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                <div class="space-y-4">
                    @if($supplier->contact_person)
                        <div>
                            <p class="text-sm text-gray-600">Contact Person</p>
                            <p class="font-medium">{{ $supplier->contact_person }}</p>
                        </div>
                    @endif

                    @if($supplier->email)
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium">{{ $supplier->email }}</p>
                        </div>
                    @endif

                    @if($supplier->phone)
                        <div>
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="font-medium">{{ $supplier->phone }}</p>
                        </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    </div>
                </div>
            </x-ui.card>

            <!-- Address & Tax -->
            <x-ui.card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Address & Tax Information</h3>
                <div class="space-y-4">
                    @if($supplier->address)
                        <div>
                            <p class="text-sm text-gray-600">Address</p>
                            <p class="font-medium whitespace-pre-wrap">{{ $supplier->address }}</p>
                        </div>
                    @endif

                    @if($supplier->tax_number)
                        <div>
                            <p class="text-sm text-gray-600">Tax Number</p>
                            <p class="font-medium">{{ $supplier->tax_number }}</p>
                        </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Purchase Stats -->
            <x-ui.card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Purchase Statistics</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Orders</span>
                        <span class="font-medium text-lg">{{ $supplier->total_orders }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Purchases</span>
                        <span class="font-medium text-lg">{{$currency}} {{ number_format($supplier->total_purchases, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Pending Orders</span>
                        <span class="font-medium text-lg">{{ $supplier->pending_orders }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Average Order Value</span>
                        <span class="font-medium text-lg">
                        {{$currency}} {{ $supplier->total_orders > 0 ? number_format($supplier->total_purchases / $supplier->total_orders, 2) : '0.00' }}
                    </span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Notes -->
        @if($supplier->notes)
            <x-ui.card title="Notes">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $supplier->notes }}</p>
            </x-ui.card>
    @endif

    <!-- Recent Orders -->
        <x-ui.card title="Recent Purchase Orders">
            @if($supplier->purchaseOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">PO Number</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Date</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Total</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($supplier->purchaseOrders as $order)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <a href="{{ route('purchases.show', $order) }}"
                                       class="font-medium text-primary-600 hover:text-primary-700">
                                        {{ $order->po_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-gray-600">
                                    {{ $order->order_date->format('M j, Y') }}
                                </td>
                                <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $order->status_color }}">
                                {{ ucfirst($order->status) }}
                            </span>
                                </td>
                                <td class="py-3 px-4 font-medium">
                                    {{$currency}} {{ number_format($order->total, 2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('purchases.show', $order) }}"
                                       class="text-gray-600 hover:text-gray-700 p-1 rounded hover:bg-gray-100">
                                        <i class="lni lni-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="lni lni-shopping-basket text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600">No purchase orders found for this supplier.</p>
                </div>
            @endif

            @if($supplier->total_orders > 10)
                <div class="mt-4 text-center">
                    <a href="{{ route('purchases.index') }}?supplier={{ $supplier->id }}"
                       class="text-primary-600 hover:text-primary-700 font-medium">
                        View all orders →
                    </a>
                </div>
            @endif
        </x-ui.card>
    </div>
@endsection
