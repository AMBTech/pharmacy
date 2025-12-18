@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Purchase Return #{{ $purchaseReturn->return_number }}</h1>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $purchaseReturn->status_color }}">
                        <i class="lni {{ $purchaseReturn->status_icon }} mr-1"></i>
                        {{ ucfirst($purchaseReturn->status) }}
                    </span>
                    <span class="text-gray-600">
                        Created {{ $purchaseReturn->created_at->format('M d, Y') }}
                    </span>
                </div>
            </div>

            @if(session('error'))
                <x-ui.alert type="error" message="{{ session('error') }}" />
            @endif

            @if(session('success'))
                <x-ui.alert type="success" message="{{ session('success') }}" />
            @endif

            <div class="flex items-center space-x-3">
                <a href="{{ route('purchases.returns.index') }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Returns
                </a>

                @if($purchaseReturn->status === 'pending')
                    <button onclick="printReturn()"
                            class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                        <i class="lni lni-printer mr-2"></i>
                        Print
                    </button>
                    <a href="{{ route('purchases.returns.edit', $purchaseReturn) }}"
                       class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center">
                        <i class="lni lni-pencil mr-2"></i>
                        Edit Return
                    </a>
                @elseif($purchaseReturn->status === 'approved')
                    <form action="{{ route('purchases.returns.complete', $purchaseReturn) }}" method="POST">
                        @csrf
                        <button class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center">
                            <i class="lni lni-checkmark-circle mr-2"></i>
                            Mark as Completed
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Return Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Return Items -->
                <x-ui.card title="Return Items">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Product</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Batch No.</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Quantity</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Unit Cost</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Total</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Reason</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($purchaseReturn->items as $item)
                                <tr class="border-b border-gray-100">
                                    <td class="py-4 px-4">
                                        <div class="font-medium">{{ $item->purchaseOrderItem->product->name }}</div>
                                        <div class="text-sm text-gray-500">
                                            SKU: {{ $item->purchaseOrderItem->product->barcode ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-gray-600">
                                        {{ $item->purchaseOrderItem->batch_number ?? 'N/A' }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="font-medium">{{ format_number($item->quantity, 0) }}</span>
                                        <span class="text-sm text-gray-500">
                                            (Received: {{ $item->purchaseOrderItem->received_quantity }})
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-gray-600">
                                        {{ format_currency($item->unit_cost) }}
                                    </td>
                                    <td class="py-4 px-4 font-semibold">
                                        {{ format_currency($item->total_cost) }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                                {{ $item->reason_type === 'damaged' ? 'bg-red-100 text-red-800' :
                                                   ($item->reason_type === 'defective' ? 'bg-orange-100 text-orange-800' :
                                                   ($item->reason_type === 'expired' ? 'bg-yellow-100 text-yellow-800' :
                                                   'bg-gray-100 text-gray-800')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $item->reason_type)) }}
                                            </span>
                                        </div>
                                        @if($item->reason)
                                            <div class="text-sm text-gray-600 mt-1">{{ $item->reason }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

                <!-- Notes -->
                @if($purchaseReturn->notes || $purchaseReturn->reason)
                    <x-ui.card title="Notes & Reason">
                        @if($purchaseReturn->reason)
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Reason for Return</h4>
                                <p class="text-gray-900">{{ $purchaseReturn->reason }}</p>
                            </div>
                        @endif

                        @if($purchaseReturn->notes)
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Additional Notes</h4>
                                <p class="text-gray-900">{{ $purchaseReturn->notes }}</p>
                            </div>
                        @endif
                    </x-ui.card>
            @endif

            <!-- Audit Trail -->
                <x-ui.card title="Activity Log">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="lni lni-add-files text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">Return Created</p>
                                <p class="text-sm text-gray-600">
                                    by {{ $purchaseReturn->creator->name ?? 'System' }}
                                    on {{ $purchaseReturn->created_at->format('M d, Y \a\t h:i A') }}
                                </p>
                            </div>
                        </div>

                        @if($purchaseReturn->approved_at)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="lni lni-checkmark-circle text-green-600"></i>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">Return Approved</p>
                                    <p class="text-sm text-gray-600">
                                        @if($purchaseReturn->approved_by)
                                            by {{ $purchaseReturn->approver->name ?? 'Approver' }}
                                        @endif
                                        on {{ \Carbon\Carbon::parse($purchaseReturn->approved_at)->format('M d, Y \a\t h:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($purchaseReturn->status === 'rejected')
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="lni lni-close text-red-600"></i>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">Return Rejected</p>
                                    <p class="text-sm text-gray-600">
                                        on {{ $purchaseReturn->updated_at->format('M d, Y \a\t h:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($purchaseReturn->completed_at)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <i class="lni lni-checkmark text-purple-600"></i>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">Return Completed</p>
                                    <p class="text-sm text-gray-600">
                                        on {{ \Carbon\Carbon::parse($purchaseReturn->completed_at)->format('M d, Y \a\t h:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>

            <!-- Right Column - Summary & Actions -->
            <div class="space-y-6">
                <!-- Return Summary -->
                <x-ui.card title="Return Summary">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">{{ format_currency($purchaseReturn->subtotal) }}</span>
                        </div>

                        @if($purchaseReturn->restocking_fee > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Restocking Fee ({{ $purchaseReturn->restocking_fee }}%)</span>
                                <span class="text-red-600">-{{ format_currency($purchaseReturn->subtotal * ($purchaseReturn->restocking_fee / 100)) }}</span>
                            </div>
                        @endif

                        @if($purchaseReturn->shipping_cost > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping Cost</span>
                                <span class="text-red-600">-{{ format_currency($purchaseReturn->shipping_cost) }}</span>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between font-semibold text-lg">
                                <span>Total Refund</span>
                                <span class="text-green-600">{{ format_currency($purchaseReturn->total) }}</span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Return Information -->
                <x-ui.card title="Return Information">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Return Type</label>
                            <div class="font-medium text-gray-900">
                                {{ ucfirst(str_replace('_', ' ', $purchaseReturn->return_type)) }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Return Date</label>
                            <div class="font-medium text-gray-900">
                                {{ $purchaseReturn->return_date->format('M d, Y') }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                            <div class="font-medium text-primary-600">{{ $purchaseReturn->return_number }}</div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Purchase Order Information -->
                <x-ui.card title="Purchase Order">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PO Number</label>
                            <a href="{{ route('purchases.show', $purchaseReturn->purchaseOrder) }}"
                               class="font-medium text-primary-600 hover:text-primary-700">
                                {{ $purchaseReturn->purchaseOrder->po_number }}
                            </a>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                            <div class="font-medium text-gray-900">
                                {{ $purchaseReturn->purchaseOrder->supplier->name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Order Date</label>
                            <div class="text-gray-900">
                                {{ $purchaseReturn->purchaseOrder->order_date->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Status Actions -->
                @if(auth()->user()->can('approve', $purchaseReturn) && $purchaseReturn->status === 'pending')
                    <x-ui.card title="Actions">
                        <div class="space-y-3">
                            <form action="{{ route('purchases.returns.approve', $purchaseReturn) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit"
                                        class="w-full bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center justify-center">
                                    <i class="lni lni-checkmark-circle mr-2"></i>
                                    Approve Return
                                </button>
                            </form>

                            <form action="{{ route('purchases.returns.reject', $purchaseReturn) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit"
                                        class="w-full bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition-colors flex items-center justify-center">
                                    <i class="lni lni-close mr-2"></i>
                                    Reject Return
                                </button>
                            </form>
                        </div>
                    </x-ui.card>
                @endif

            <!-- Danger Zone -->
                @if($purchaseReturn->status === 'pending' && auth()->user()->can('delete', $purchaseReturn))
                    <x-ui.card title="Danger Zone" class="border-red-200">
                        <form action="{{ route('purchases.returns.destroy', $purchaseReturn) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this return? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition-colors flex items-center justify-center">
                                <i class="lni lni-trash-can mr-2"></i>
                                Delete Return
                            </button>
                        </form>
                    </x-ui.card>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function printReturn() {
            // Create a print-friendly version
            const printContent = `
                <html>
                <head>
                    <title>Return {{ $purchaseReturn->return_number }}</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .section { margin-bottom: 20px; }
                        .section-title { font-weight: bold; border-bottom: 2px solid #000; margin-bottom: 10px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f5f5f5; }
                        .text-right { text-align: right; }
                        .summary { margin-top: 30px; }
                        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Purchase Return #{{ $purchaseReturn->return_number }}</h1>
                        <p>Date: {{ $purchaseReturn->return_date->format('M d, Y') }}</p>
                        <p>Status: {{ ucfirst($purchaseReturn->status) }}</p>
                    </div>

                    <div class="section">
                        <div class="section-title">Supplier Information</div>
                        <p><strong>Supplier:</strong> {{ $purchaseReturn->purchaseOrder->supplier->name }}</p>
                        <p><strong>Purchase Order:</strong> {{ $purchaseReturn->purchaseOrder->po_number }}</p>
                    </div>

                    <div class="section">
                        <div class="section-title">Return Items</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost</th>
                                    <th>Total</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseReturn->items as $item)
            <tr>
                <td>{{ $item->purchaseOrderItem->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ format_currency($item->unit_cost) }}</td>
                                    <td>{{ format_currency($item->total_cost) }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $item->reason_type)) }}</td>
                                </tr>
                                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section summary">
        <div class="section-title">Return Summary</div>
        <table style="border: none;">
            <tr>
                <td style="border: none;"><strong>Subtotal:</strong></td>
                <td style="border: none; text-align: right;">{{ format_currency($purchaseReturn->subtotal) }}</td>
                            </tr>
                            @if($purchaseReturn->restocking_fee > 0)
            <tr>
                <td style="border: none;"><strong>Restocking Fee ({{ $purchaseReturn->restocking_fee }}%):</strong></td>
                                <td style="border: none; text-align: right;">-{{ format_currency($purchaseReturn->subtotal * ($purchaseReturn->restocking_fee / 100)) }}</td>
                            </tr>
                            @endif
            @if($purchaseReturn->shipping_cost > 0)
            <tr>
                <td style="border: none;"><strong>Shipping Cost:</strong></td>
                <td style="border: none; text-align: right;">-{{ format_currency($purchaseReturn->shipping_cost) }}</td>
                            </tr>
                            @endif
            <tr>
                <td style="border: none;"><strong>Total Refund:</strong></td>
                <td style="border: none; text-align: right;"><strong>{{ format_currency($purchaseReturn->total) }}</strong></td>
                            </tr>
                        </table>
                    </div>

                    <div class="section">
                        <div class="section-title">Notes</div>
                        <p>{{ $purchaseReturn->reason ?: 'No additional notes.' }}</p>
                    </div>

                    <div class="footer">
                        <p>Generated on {{ now()->format('M d, Y \a\t h:i A') }}</p>
                        <p>Return #{{ $purchaseReturn->return_number }}</p>
                    </div>
                </body>
                </html>
            `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }

        // Keyboard shortcut for printing (Ctrl + P)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                printReturn();
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Additional styles for better readability */
        .text-green-600 {
            color: #059669;
        }
        .text-red-600 {
            color: #dc2626;
        }
        .text-blue-600 {
            color: #2563eb;
        }
        .text-orange-600 {
            color: #ea580c;
        }
        .text-yellow-600 {
            color: #ca8a04;
        }
        .text-purple-600 {
            color: #7c3aed;
        }
    </style>
@endpush
