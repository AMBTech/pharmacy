@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Refunds</h1>
                <p class="text-gray-600 mt-1">Manage supplier refunds</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $refunds->total() }}</p>
                <p class="text-sm text-gray-600">Total Refunds</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-yellow-600">
                    {{ \App\Models\Refund::where('status', 'pending')->count() }}
                </p>
                <p class="text-sm text-gray-600">Pending</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-blue-600">
                    {{ \App\Models\Refund::where('status', 'processing')->count() }}
                </p>
                <p class="text-sm text-gray-600">Processing</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-green-600">
                    {{ \App\Models\Refund::where('status', 'completed')->count() }}
                </p>
                <p class="text-sm text-gray-600">Completed</p>
            </x-ui.card>
        </div>

        <!-- Refunds Table -->
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Refund No</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Supplier</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Return No</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Amount</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Method</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Date</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($refunds as $refund)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4">
                                <a href="{{ route('refunds.show', $refund) }}"
                                   class="font-medium text-primary-600 hover:text-primary-700">
                                    {{ $refund->refund_number }}
                                </a>
                            </td>
                            <td class="py-4 px-4">
                                {{ $refund->supplier->name }}
                            </td>
                            <td class="py-4 px-4">
                                @if($refund->purchaseReturn)
                                    <a href="{{ route('purchases.returns.show', $refund->purchaseReturn) }}"
                                       class="text-primary-600 hover:text-primary-700">
                                        {{ $refund->purchaseReturn->return_number }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="py-4 px-4 font-semibold">
                                {{ format_currency($refund->amount) }}
                            </td>
                            <td class="py-4 px-4">
                                {{ $refund->method_label }}
                            </td>
                            <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $refund->status_color }}">
                                        <i class="lni {{ $refund->status_icon }} mr-1"></i>
                                        {{ ucfirst($refund->status) }}
                                    </span>
                            </td>
                            <td class="py-4 px-4 text-gray-600">
                                {{ $refund->refund_date->format('M d, Y') }}
                            </td>
                            <td class="py-4 px-4">
                                <a href="{{ route('refunds.show', $refund) }}"
                                   class="text-gray-600 hover:text-gray-700 p-2 rounded hover:bg-gray-100">
                                    <i class="lni lni-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if($refunds->hasPages())
                <div class="px-4 py-4 border-t border-gray-200">
                    {{ $refunds->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
@endsection
