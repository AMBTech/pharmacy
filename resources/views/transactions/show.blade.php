{{--
@extends('layouts.app')

@section('content')
    <div class="space-y-6 max-w-5xl mx-auto">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Transaction Details</h1>
                <p class="text-gray-600 mt-1">Transaction #{{ $transaction->related->invoice_number ?? $transaction->related->return_number ?? 'N/A' }}</p>
            </div>
            <div class="flex items-center space-x-4">
                <x-ui.button variant="success" icon="lni lni-arrow-left" href="{{ route('transactions.index') }}">
                    Back to Transactions
                </x-ui.button>
            </div>
        </div>

        <x-ui.card class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Type</span>
                        <span>{!! $transaction->type_badge !!}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Amount</span>
                        <span class="text-lg font-semibold {{ $transaction->amount_class }}">{{ $transaction->signed_amount }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Payment Method</span>
                        <span>{!! $transaction->payment_method_badge !!}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Created At</span>
                        <span class="text-sm font-medium text-gray-900">{{ $transaction->created_at->format('M j, Y h:i A') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Recorded By</span>
                        <span class="text-sm font-medium text-gray-900">{{ optional($transaction->user)->name ?? 'System' }}</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Notes</p>
                        <p class="text-sm text-gray-900">{{ $transaction->notes ?: '—' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Related Record</p>
                        @if($transaction->related)
                            <p class="text-sm text-gray-900">
                                {{ class_basename($transaction->related_type) }} #{{ $transaction->related_id }}
                            </p>
                        @else
                            <p class="text-sm text-gray-900">None</p>
                        @endif
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
@endsection
--}}


@extends('layouts.app')

@section('content')
    <div class="space-y-6 max-w-6xl mx-auto">
        <!-- Page Header -->
        <div class="bg-white rounded-2xl p-6 shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)]">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center">
                            @if($transaction->transaction_type === 'sale')
                                <i class="lni lni-shopping-basket text-primary-600 text-xl"></i>
                            @elseif($transaction->transaction_type === 'refund')
                                <i class="lni lni-reload text-red-600 text-xl"></i>
                            @elseif($transaction->transaction_type === 'expense')
                                <i class="lni lni-money-protection text-orange-600 text-xl"></i>
                            @elseif($transaction->transaction_type === 'payment')
                                <i class="lni lni-credit-cards text-blue-600 text-xl"></i>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Transaction Details</h1>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-sm text-gray-600">ID: {{ $transaction->id }}</span>
                                <span class="text-gray-400">•</span>
                                <span class="text-sm text-gray-600">{{ $transaction->created_at->format('F j, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    @if($transaction->related)
                        <div class="mt-3 inline-flex items-center px-3 py-1.5 rounded-full bg-white shadow-xs border">
                            <span class="text-xs text-gray-600 mr-2">Linked to:</span>
                            <span class="text-sm font-medium text-primary-700">
                                {{ class_basename($transaction->related_type) }} #{{ $transaction->related->invoice_number ?? $transaction->related->return_number ?? $transaction->related_id }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-ui.button variant="outline" icon="lni lni-arrow-left" href="{{ route('transactions.index') }}">
                        Back to List
                    </x-ui.button>

                    {{--@hasPermission('transactions.edit')
                    @if(!$transaction->related_id)
                        <x-ui.button variant="warning" icon="lni lni-pencil-alt" href="{{ route('transactions.edit', $transaction) }}">
                            Edit
                        </x-ui.button>
                    @endif
                    @endhasPermission--}}
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Transaction Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Transaction Card -->
                <x-ui.card class="overflow-hidden">
                    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Transaction Information</h2>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Info -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                        Transaction Type
                                    </label>
                                    <div class="flex items-center gap-2">
                                        {!! $transaction->type_badge !!}
                                        <span class="text-sm text-gray-700 ml-2">{{ $transaction->type_label }}</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                        Amount
                                    </label>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-3xl font-bold {{ $transaction->amount_class }}">
                                            {{ $transaction->signed_amount }}
                                        </span>
                                        <span class="text-sm text-gray-500">({{ $currency_symbol }} {{ number_format(abs($transaction->amount), 2) }})</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                        Payment Method
                                    </label>
                                    <div class="flex items-center gap-2">
                                        {!! $transaction->payment_method_badge !!}
                                        <span class="text-sm text-gray-700 ml-2">{{ $transaction->payment_method_label }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Timestamps & User -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                        Transaction Date & Time
                                    </label>
                                    <div class="flex items-center gap-2 text-gray-900">
                                        <i class="lni lni-calendar text-gray-400"></i>
                                        <span>{{ $transaction->created_at->format('F j, Y') }}</span>
                                        <i class="lni lni-timer text-gray-400 ml-3"></i>
                                        <span>{{ $transaction->created_at->format('h:i A') }}</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                        Recorded By
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center">
                                            <i class="lni lni-user text-primary-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ optional($transaction->user)->name ?? 'System' }}</p>
                                            <p class="text-xs text-gray-500">{{ optional($transaction->user)->email ?? 'Automated System' }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if($transaction->updated_at != $transaction->created_at)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                            Last Updated
                                        </label>
                                        <p class="text-sm text-gray-900">
                                            {{ $transaction->updated_at->diffForHumans() }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Notes & Additional Information -->
                <x-ui.card>
                    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Additional Information</h2>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Notes Section -->
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                                    Notes
                                </label>
                                @if($transaction->notes)
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                        <p class="text-gray-700 whitespace-pre-line">{{ $transaction->notes }}</p>
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-400">
                                        <i class="lni lni-clipboard text-3xl mb-2 block"></i>
                                        <p>No notes provided</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Related Information -->
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                                    Related Information
                                </label>
                                @if($transaction->related)
                                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-white border flex items-center justify-center flex-shrink-0">
                                                @if($transaction->transaction_type === 'sale')
                                                    <i class="lni lni-shopping-basket text-green-600"></i>
                                                @elseif($transaction->transaction_type === 'refund')
                                                    <i class="lni lni-reload text-red-600"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">
                                                    {{ class_basename($transaction->related_type) }}
                                                    @if($transaction->related_type === 'sale' && isset($transaction->related->invoice_number))
                                                        #{{ $transaction->related->invoice_number }}
                                                    @elseif($transaction->related_type === 'order_return' && isset($transaction->related->return_number))
                                                        #{{ $transaction->related->return_number }}
                                                    @else
                                                        #{{ $transaction->related_id }}
                                                    @endif
                                                </p>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    {{ $transaction->related_type === 'sale' ? 'Sale' : 'Return' }}
                                                </p>
                                                @if($transaction->related_type === 'sale')
                                                    <div class="mt-2">
                                                        <a href="{{ route('sales.show', $transaction->related) }}"
                                                           class="inline-flex items-center text-sm text-primary-600 hover:text-primary-800 font-medium">
                                                            <i class="lni lni-external-link mr-1"></i>
                                                            View Sale Details
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-400">
                                        <i class="lni lni-link text-3xl mb-2 block"></i>
                                        <p>No related records</p>
                                        <p class="text-xs mt-1">This is a standalone transaction</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <!-- Right Column: Quick Actions & Statistics -->
            <div class="space-y-6">
                <!-- Quick Stats Card -->
                <x-ui.card>
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h2 class="text-lg font-semibold text-gray-900">Transaction Overview</h2>
                    </div>

                    <div class="p-4 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Status</span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                Completed
                            </span>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                                Impact on Cash Flow
                            </p>
                            <div class="space-y-2">
                                @if(in_array($transaction->transaction_type, ['sale', 'payment']))
                                    <div class="flex items-center justify-between text-green-600">
                                        <span class="text-sm">Cash Inflow</span>
                                        <span class="font-semibold">+{{ $currency_symbol }} {{ number_format($transaction->amount, 2) }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between text-red-600">
                                        <span class="text-sm">Cash Outflow</span>
                                        <span class="font-semibold">-{{ $currency_symbol }} {{ number_format($transaction->amount, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                                Daily Comparison
                            </p>
                            @php
                                $dailyAvg = \App\Models\Transaction::whereDate('created_at', $transaction->created_at)
                                    ->where('id', '!=', $transaction->id)
                                    ->where('transaction_type', $transaction->transaction_type)
                                    ->avg('amount');
                            @endphp
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Daily Average</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $currency_symbol }} {{ number_format($dailyAvg ?? 0, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Quick Actions -->
                <x-ui.card>
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                    </div>

                    <div class="p-4 space-y-3">
                        @if($transaction->transaction_type === 'sale' && $transaction->related)
                            <a href="{{ route('sales.print', $transaction->related) }}" target="_blank"
                               class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors group">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                        <i class="lni lni-printer text-blue-600"></i>
                                    </div>
                                    <span class="font-medium text-gray-900">Print Receipt</span>
                                </div>
                                <i class="lni lni-arrow-right text-gray-400 group-hover:text-gray-600"></i>
                            </a>
                        @else
                            <span class="text-sm text-gray-500">No actions</span>
                        @endif

                        @if($transaction->transaction_type === 'sale' && $transaction->related)
                            @php
                                $sale = $transaction->related;
                                $hasAnyAvailable = $sale->items->contains(function($it) {
                                    $soldQty = (int) $it->quantity;
                                    $refundedQty = (int) ($it->refunded_quantity ?? 0);
                                    $pendingQty = (int) ($it->pending_return_qty ?? 0);
                                    return ($soldQty - $refundedQty - $pendingQty) > 0;
                                });
                            @endphp
                            @if(!$sale->isFullyRefunded() || $hasAnyAvailable)
                                <a href="{{ route('returns.create', $sale) }}"
                                   class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-red-50 transition-colors group">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center mr-3">
                                            <i class="lni lni-reload text-red-600"></i>
                                        </div>
                                        <span class="font-medium text-gray-900">Create Return</span>
                                    </div>
                                    <i class="lni lni-arrow-right text-gray-400 group-hover:text-red-600"></i>
                                </a>
                            @endif
                        @endif

                        @hasPermission('transactions.edit')
                        @if(!$transaction->related_id)
                            <a href="{{ route('transactions.edit', $transaction) }}"
                               class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-yellow-50 transition-colors group">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center mr-3">
                                        <i class="lni lni-pencil-alt text-yellow-600"></i>
                                    </div>
                                    <span class="font-medium text-gray-900">Edit Transaction</span>
                                </div>
                                <i class="lni lni-arrow-right text-gray-400 group-hover:text-yellow-600"></i>
                            </a>
                        @endif
                        @endhasPermission

                        @hasPermission('transactions.delete')
                        @if(!$transaction->related_id)
                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-red-50 transition-colors group text-left">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center mr-3">
                                            <i class="lni lni-trash-can text-red-600"></i>
                                        </div>
                                        <span class="font-medium text-gray-900">Delete Transaction</span>
                                    </div>
                                    <i class="lni lni-arrow-right text-gray-400 group-hover:text-red-600"></i>
                                </button>
                            </form>
                        @endif
                        @endhasPermission
                    </div>
                </x-ui.card>

                <!-- Related Transactions -->
                @php
                    $relatedTransactions = \App\Models\Transaction::where('user_id', $transaction->user_id)
                        ->whereDate('created_at', $transaction->created_at)
                        ->where('id', '!=', $transaction->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp

                @if($relatedTransactions->count() > 0)
                    <x-ui.card>
                        <div class="border-b border-gray-200 px-4 py-3">
                            <h2 class="text-lg font-semibold text-gray-900">Related Transactions</h2>
                            <p class="text-xs text-gray-500 mt-1">Same day, same cashier</p>
                        </div>

                        <div class="p-4 space-y-3">
                            @foreach($relatedTransactions as $related)
                                <a href="{{ route('transactions.show', $related) }}"
                                   class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors group">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-medium {{ $related->amount_class }}">
                                            {{ $related->signed_amount }}
                                        </span>
                                            <span class="text-xs px-2 py-0.5 rounded-full {{
                                            $related->transaction_type === 'sale' ? 'bg-green-100 text-green-800' :
                                            ($related->transaction_type === 'refund' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')
                                        }}">
                                            {{ $related->type_label }}
                                        </span>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            {{ $related->created_at->format('h:i A') }}
                                            • {{ Str::limit($related->notes, 30) ?: 'No description' }}
                                        </p>
                                    </div>
                                    <i class="lni lni-arrow-right text-gray-400 group-hover:text-gray-600"></i>
                                </a>
                            @endforeach

                            <div class="pt-3 border-t border-gray-100">
                                <a href="{{ route('transactions.index', [
                                'user_id' => $transaction->user_id,
                                'start_date' => $transaction->created_at->format('Y-m-d'),
                                'end_date' => $transaction->created_at->format('Y-m-d')
                            ]) }}"
                                   class="text-sm text-primary-600 hover:text-primary-800 font-medium flex items-center justify-center">
                                    View all transactions from this day
                                    <i class="lni lni-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </x-ui.card>
                @endif
            </div>
        </div>

        <!-- Audit Trail Section -->
        {{--@if(app()->has('activity'))
            @php
                $activities = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\Transaction::class)
                    ->where('subject_id', $transaction->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            @endphp

            @if($activities->count() > 0)
                <x-ui.card>
                    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Activity Log</h2>
                        <p class="text-sm text-gray-500 mt-1">History of changes to this transaction</p>
                    </div>

                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($activities as $activity)
                                <div class="flex items-start gap-4 pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <i class="lni lni-history text-gray-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="font-medium text-gray-900">
                                                {{ $activity->description }}
                                            </p>
                                            <span class="text-xs text-gray-500">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        @if($activity->causer)
                                            <p class="text-sm text-gray-600 mt-1">
                                                By {{ $activity->causer->name }}
                                            </p>
                                        @endif
                                        @if($activity->properties && count($activity->properties) > 0)
                                            <div class="mt-2 text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                                <pre class="whitespace-pre-wrap">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            @endif
        @endif--}}
    </div>
@endsection

@push('styles')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .max-w-6xl {
                max-width: 100% !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Copy transaction ID to clipboard
        function copyTransactionId() {
            const id = '{{ $transaction->id }}';
            navigator.clipboard.writeText(id).then(() => {
                alert('Transaction ID copied to clipboard!');
            });
        }

        // Print optimized receipt
        function printReceipt() {
            const printContent = document.getElementById('printable-receipt');
            if (printContent) {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Transaction Receipt #{{ $transaction->id }}</title>
                        <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; }
                            .receipt-header { text-align: center; margin-bottom: 30px; }
                            .receipt-details { margin-bottom: 20px; }
                            .receipt-footer { margin-top: 30px; text-align: center; color: #666; }
                        </style>
                    </head>
                    <body>
                        ${printContent.innerHTML}
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.print();
            } else {
                window.print();
            }
        }
    </script>
@endpush
