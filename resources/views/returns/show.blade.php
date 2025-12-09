@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- Page header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Return Receipt</h1>
                <p class="text-sm text-gray-500 mt-1">Return details and refund summary</p>
            </div>

            <div class="flex flex-row gap-3">
                <div class="flex items-center space-x-3">
                    <x-ui.button variant="secondary" icon="lni lni-arrow-left" href="{{ route('returns.index') }}">
                        Back to Returns
                    </x-ui.button>
                </div>
                @if($returnOrder->status === 'pending')
                    <div class="flex items-center space-x-3">
                        <div class="flex space-x-3">
                            <!-- Approve Form -->
                            <form action="{{ route('returns.approve', $returnOrder) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Approve this return?')"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <i class="lni lni-checkmark mr-1"></i> Approve Return
                                </button>
                            </form>

                            <!-- Reject Form with Modal Trigger -->
                            <button onclick="openRejectModal()"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                <i class="lni lni-cross-circle mr-1"></i> Reject Return
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Main card --}}
        <x-ui.card class="p-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                {{-- Left: meta --}}
                <div class="flex-1 min-w-0">
                    <p class="text-lg font-semibold text-gray-900" aria-label="Return number">{{ $returnOrder->return_number }}</p>
                    <p class="text-sm text-gray-600 mt-1">Sale: <span class="font-medium">{{ $returnOrder->sale->invoice_number ?? 'N/A' }}</span></p>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-600">
                        <div>
                            <div class="text-xs text-gray-500">Generated At</div>
                            <div class="font-medium">{{ optional($returnOrder->created_at)->format('F d, Y h:i A') ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Cashier</div>
                            <div class="font-medium">{{ optional($returnOrder->cashier)->name ?? 'Unknown' }}</div>
                        </div>

                        {{-- optional customer info if available --}}
                        @if($returnOrder->sale && ($returnOrder->sale->customer_name || $returnOrder->sale->customer_phone))
                            <div class="sm:col-span-2">
                                <div class="text-xs text-gray-500">Customer</div>
                                <div class="font-medium">
                                    {{ $returnOrder->sale->customer_name ?? 'Walk-in Customer' }}
                                    @if($returnOrder->sale->customer_phone)
                                        • <span class="text-gray-500">{{ $returnOrder->sale->customer_phone }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right: summary box --}}
                <div class="w-full md:w-64">
                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-right">
                        <div class="text-sm text-gray-500">Refund Amount</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">
                            {{ format_currency($returnOrder->refund_amount ?? 0) }}
                        </div>
                        @if($returnOrder->payment_method)
                            <div class="mt-2 text-xs text-gray-500">Refund via: {{ ucfirst($returnOrder->payment_method) }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Items table --}}
            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-sm table-auto">
                    <thead class="bg-white">
                    <tr class="text-left text-gray-700">
                        <th class="py-3 px-3 border-b">Product</th>
                        <th class="py-3 px-3 border-b text-center w-24">Qty</th>
                        <th class="py-3 px-3 border-b text-right w-40">Unit Price</th>
                        <th class="py-3 px-3 border-b text-right w-40">Total</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white">
                    @forelse($returnOrder->items as $item)
                        <tr class="border-b last:border-b-0">
                            <td class="py-3 px-3 align-top">
                                <div class="font-medium text-gray-800">
                                    {{ optional($item->product)->name ?? ($item->description ?? 'Product (deleted)') }}
                                </div>
                                @if(optional($item->product)->sku)
                                    <div class="text-xs text-gray-500 mt-1">SKU: {{ $item->product->sku }}</div>
                                @endif
                            </td>

                            <td class="py-3 px-3 text-center align-top">{{ (int) $item->quantity }}</td>

                            <td class="py-3 px-3 text-right align-top">
                                {{ format_currency($item->unit_price ?? ($item->total_price / max(1, $item->quantity))) }}
                            </td>

                            <td class="py-3 px-3 text-right align-top font-medium">
                                {{ format_currency($item->total_price ?? 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 px-3 text-center text-gray-500">
                                No items found for this return.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer totals --}}
            <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-end gap-4">
                <div class="w-full md:w-1/3 text-right">
                    <div class="flex justify-between text-sm text-gray-600">
                        <div>Subtotal</div>
                        <div class="font-medium">{{ format_currency($returnOrder->sub_total ?? $returnOrder->items->sum('total_price')) }}</div>
                    </div>

                    @if(!empty($returnOrder->tax_amount) || !empty($returnOrder->discount_amount))
                        <div class="flex justify-between text-sm text-gray-600 mt-2">
                            <div>Discount</div>
                            <div class="font-medium">- {{ format_currency($returnOrder->discount_amount ?? 0) }}</div>
                        </div>

                        <div class="flex justify-between text-sm text-gray-600 mt-1">
                            <div>Tax</div>
                            <div class="font-medium">{{ format_currency($returnOrder->tax_amount ?? 0) }}</div>
                        </div>
                    @endif

                    <div class="flex justify-between text-lg font-bold text-gray-900 mt-3">
                        <div>Total Refund</div>
                        <div>{{ format_currency($returnOrder->refund_amount ?? 0) }}</div>
                    </div>
                </div>
            </div>

        </x-ui.card>
    </div>

    @if($returnOrder->status === 'pending')
        <!-- Reject Modal for show page -->
        <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
            <div class="relative top-20 mx-auto p-5 w-96 bg-white rounded-lg shadow-xl">
                <h3 class="text-lg font-semibold mb-4">Reject Return #{{ $returnOrder->return_number }}</h3>

                <form action="{{ route('returns.reject', $returnOrder) }}" method="POST">
                    @csrf
                    @method('POST')

                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Customer: <strong>{{ optional($returnOrder->customer)->name }}</strong></p>
                        <p class="text-sm text-gray-600 mb-2">Amount: <strong>{{$currency_symbol}}{{ number_format($returnOrder->total_refund_amount, 2) }}</strong></p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Rejection Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea name="rejection_reason"
                                  rows="4"
                                  required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                  placeholder="Why are you rejecting this return?"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Staff Notes (Optional)</label>
                        <textarea name="staff_notes"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                  placeholder="Internal notes..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRejectModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                onclick="return confirm('Are you sure you want to reject this return?')"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openRejectModal() {
                document.getElementById('rejectModal').classList.remove('hidden');
            }

            function closeRejectModal() {
                document.getElementById('rejectModal').classList.add('hidden');
            }
        </script>
    @endif

    {{-- Small print styles so the print looks tidy --}}
    @push('styles')
        <style>
            @media print {
                body { color-adjust: exact; -webkit-print-color-adjust: exact; }
                .no-print { display: none !important; }
                .x-ui-card { box-shadow: none !important; border: none !important; }
            }
        </style>
    @endpush

@endsection
