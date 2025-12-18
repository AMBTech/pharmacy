@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Purchase Returns</h1>
                <p class="text-gray-600 mt-1">Manage returned items to suppliers</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('purchases.index') }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Purchases
                </a>
                <a href="{{ route('purchases.returns.create') }}"
                   class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center">
                    <i class="lni lni-plus mr-2"></i>
                    New Return
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $purchaseReturns->count() }}</p>
                <p class="text-sm text-gray-600">Total Returns</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-yellow-600">
                    {{ \App\Models\PurchaseReturn::where('status', 'pending')->count() }}
                </p>
                <p class="text-sm text-gray-600">Pending</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-blue-600">
                    {{ \App\Models\PurchaseReturn::where('status', 'approved')->count() }}
                </p>
                <p class="text-sm text-gray-600">Approved</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-green-600">
                    {{ \App\Models\PurchaseReturn::where('status', 'completed')->count() }}
                </p>
                <p class="text-sm text-gray-600">Completed</p>
            </x-ui.card>
        </div>

        <!-- Purchase Returns Table -->
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Return No</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Purchase Order</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Supplier</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Return Date</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Total Amount</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Reason</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($purchaseReturns as $return)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4">
                                <a href="{{ route('purchases.returns.show', $return) }}"
                                   class="font-medium text-primary-600 hover:text-primary-700">
                                    {{ $return->return_number }}
                                </a>
                            </td>
                            <td class="py-4 px-4">
                                <a href="{{ route('purchases.show', $return->purchaseOrder) }}"
                                   class="text-primary-600 hover:text-primary-700">
                                    {{ $return->purchaseOrder->po_number }}
                                </a>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="lni lni-user text-gray-400"></i>
                                    </div>
                                    <span class="font-medium">{{ $return->purchaseOrder->supplier->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-gray-600">
                                {{ $return->return_date->format('M d, Y') }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $return->status_color }}">
                                    <i class="lni {{ $return->status_icon }} mr-1"></i>
                                    {{ ucfirst($return->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-semibold text-gray-900">
                                {{ format_currency($return->total) }}
                            </td>
                            <td class="py-4 px-4 text-gray-600">
                                {{ Str::limit($return->reason, 30) }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('purchases.returns.show', $return) }}"
                                       class="text-gray-600 hover:text-gray-700 p-2 rounded hover:bg-gray-100"
                                       title="View">
                                        <i class="lni lni-eye"></i>
                                    </a>

                                    @if($return->status === 'pending')
                                        <a href="{{ route('purchases.returns.edit', $return) }}"
                                           class="text-gray-600 hover:text-gray-700 p-2 rounded hover:bg-gray-100"
                                           title="Edit">
                                            <i class="lni lni-pencil"></i>
                                        </a>
                                    @endif

                                    @if($return->status === 'approved')
                                        <form action="{{ route('purchases.returns.complete', $return) }}" method="POST">
                                            @csrf
                                            <button
                                               class="text-green-600 hover:text-green-700 p-2 rounded hover:bg-green-50 cursor-pointer"
                                               title="Mark as Completed">
                                                <i class="lni lni-checkmark-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($purchaseReturns->hasPages())
                <div class="px-4 py-4 border-t border-gray-200">
                    {{ $purchaseReturns->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
@endsection
