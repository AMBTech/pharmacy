@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Suppliers</h1>
                <p class="text-gray-600 mt-1">Manage your product suppliers</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('purchases.index') }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Orders
                </a>
                <a href="{{ route('purchases.suppliers.create') }}"
                   class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center">
                    <i class="lni lni-plus mr-2"></i>
                    Add Supplier
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $suppliers->total() }}</p>
                <p class="text-sm text-gray-600">Total Suppliers</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-green-600">
                    {{ $suppliers->where('is_active', true)->count() }}
                </p>
                <p class="text-sm text-gray-600">Active</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-blue-600">
                    {{ \App\Models\Supplier::has('purchaseOrders')->count() }}
                </p>
                <p class="text-sm text-gray-600">With Orders</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-purple-600">
                    {{$currency}} {{ number_format(\App\Models\Supplier::sum('total_purchases'), 2) }}
                </p>
                <p class="text-sm text-gray-600">Total Purchases</p>
            </x-ui.card>
        </div>

        <!-- Suppliers Table -->
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Supplier</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Contact</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Total Orders</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Total Purchases</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($suppliers as $supplier)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4">
                                <a href="{{ route('purchases.suppliers.show', $supplier) }}"
                                   class="font-medium text-primary-600 hover:text-primary-700">
                                    {{ $supplier->name }}
                                </a>
                                @if($supplier->contact_person)
                                    <p class="text-sm text-gray-600">{{ $supplier->contact_person }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($supplier->email)
                                    <p class="text-sm text-gray-600">{{ $supplier->email }}</p>
                                @endif
                                @if($supplier->phone)
                                    <p class="text-sm text-gray-600">{{ $supplier->phone }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $supplier->total_orders }}
                            </span>
                            </td>
                            <td class="py-4 px-4 font-semibold text-gray-900">
                                {{$currency}} {{ number_format($supplier->total_purchases, 2) }}
                            </td>
                            <td class="py-4 px-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('purchases.suppliers.show', $supplier) }}"
                                       class="text-gray-600 hover:text-gray-700 p-2 rounded hover:bg-gray-100"
                                       title="View">
                                        <i class="lni lni-eye"></i>
                                    </a>
                                    <a href="{{ route('purchases.suppliers.edit', $supplier) }}"
                                       class="text-gray-600 hover:text-gray-700 p-2 rounded hover:bg-gray-100"
                                       title="Edit">
                                        <i class="lni lni-pencil"></i>
                                    </a>
                                    <a href="{{ route('purchases.create') }}?supplier={{ $supplier->id }}"
                                       class="text-primary-600 hover:text-primary-700 p-2 rounded hover:bg-primary-50"
                                       title="New Order">
                                        <i class="lni lni-shopping-basket"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($suppliers->hasPages())
                <div class="px-4 py-4 border-t border-gray-200">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
@endsection
