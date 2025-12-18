@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Sales History</h1>
                <p class="text-gray-600 mt-1">View and manage all sales transactions</p>
            </div>
            <div class="flex items-center space-x-4">
                <x-ui.button variant="success" icon="lni lni-download" :href="route('sales.export.excel', request()->query())" unescaped>
                    Export
                </x-ui.button>
                <x-ui.button variant="success" icon="lni lni-printer" href="{{ route('pos.index') }}">
                    New Sale
                </x-ui.button>
            </div>
        </div>

        <!-- Sales Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card class="border-l-4 border-l-primary-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mr-4">
                        <x-ui.icon name="bar-chart-dollar"></x-ui.icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $currency_symbol }} {{ number_format($todaySales, 2) }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="border-l-4 border-l-success-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="lni lni-stats-up text-success-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">This Month</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $currency_symbol }} {{ number_format($monthSales, 2) }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="border-l-4 border-l-warning-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="lni lni-layers text-warning-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Transactions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalSales }}</p>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Filters -->
        <x-ui.card title="Filters" padding="p-6">
            <form action="{{ route('sales.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Date Range -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment
                        Method</label>
                    <select name="payment_method" id="payment_method"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="digital" {{ request('payment_method') == 'digital' ? 'selected' : '' }}>Digital
                        </option>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Invoice, Customer..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <!-- Filter Actions -->
                <div class="md:col-span-4 flex justify-between items-center pt-4 border-t border-gray-200">
                    @hasPermission('reports.view')
                        <a href="{{ route('reports.sales-by-cashier') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="lni lni-bar-chart mr-2"></i>
                            Sales by Cashier Report
                        </a>
                    @endhasPermission
                    <div class="flex space-x-3 ml-auto">
                        <a href="{{ route('sales.index') }}"
                           class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            Clear
                        </a>
                        <button type="submit"
                                class="bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        <!-- Sales Table -->
        <x-ui.card title="Sales" padding="p-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Invoice #
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date & Time
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Amount
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Refunded Amount
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Payment
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cashier
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sales as $sale)

                            @php
                                $hasAnyAvailable = $sale->items->contains(function($it) {
                                    $soldQty = (int) $it->quantity;
                                    $refundedQty = (int) ($it->refunded_quantity ?? 0);
                                    $pendingQty = (int) ($it->pending_return_qty ?? 0);
                                    return ($soldQty - $refundedQty - $pendingQty) > 0;
                                });
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{$sale->invoice_number ?? "N/A"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{format_date_time($sale->created_at) ?? "N/A"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="lni lni-user text-gray-400"></i>
                                        </div>
                                        <div>
                                            <div
                                                class="text-sm font-medium text-gray-900">{{ $sale->customer_name ?? "Walk-in Customer" }}</div>
                                            <div
                                                class="text-sm text-gray-500">{{ $sale->customer_phone ?? "Phone: N/A" }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex flex-col">
                                    <span>{{count($sale->items) ?? "0"}}</span>
                                    <span class="text-xs text-gray-400"><strong>Quantity:</strong> {{$sale->items->sum('quantity')}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{format_currency($sale->total_amount) ?? "0"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex flex-col">
                                    @if ($sale->refunded_amount > 0)
                                        <span class="text-red-500">{{format_currency($sale->refunded_amount)}}</span>
                                        <span class="text-gray-500 text-xs"><strong>Approved:</strong> {{$sale->items->sum("refunded_quantity")}}</span>
                                    @elseif ($sale->pending_refunded_amount > 0)
                                        <span class="text-warning-500">{{format_currency($sale->pending_refunded_amount)}}</span>
                                        <span class="text-gray-500 text-xs"><strong>Pending:</strong> {{$sale->items->sum("pending_return_qty")}}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{$sale->payment_method ?? "Cash"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span>{{optional($sale->cashier)->name ?? "Unknown"}}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex mt-4 gap-2">
                                    <a href="{{ route('sales.show', $sale) }}"
                                       class="text-green-600 hover:text-green-900 flex items-center text-sm font-bold p-2 hover:bg-gray-200 rounded-md">
                                        <i class="lni lni-eye"></i>
                                    </a>
                                    <a href="{{ route('sales.print', $sale) }}" target="_blank"
                                       class="text-green-600 hover:text-green-900 flex items-center text-sm font-bold p-2 hover:bg-gray-200 rounded-md">
                                        <i class="lni lni-printer"></i>
                                    </a>
                                    @if(!$sale->isFullyRefunded() || $hasAnyAvailable)
                                        <button onclick="openRefundModal({{$sale->id}})"
                                                class="text-red-600 hover:text-red-900 flex items-center text-sm font-bold p-2 hover:bg-gray-200 rounded-md">
                                            <i class="lni lni-reload"></i>
                                        </button>
                                    @endif
                                </td>

                                <!-- Create refund Modal -->
                                <div id="createRefundModal-{{ $sale->id }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                                    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
                                        <div class="flex justify-between items-center mb-4">
                                            <h3 class="text-xl font-semibold text-gray-900">Create Return Order</h3>
                                            <button type="button" onclick="closeRefundModal({{ $sale->id }})" class="text-gray-400 hover:text-gray-600">
                                                <i class="lni lni-close text-2xl"></i>
                                            </button>
                                        </div>

                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <p class="text-gray-600 mt-1">Select quantities to refund for each item</p>
                                                <p class="text-sm text-gray-600">Invoice: {{ $sale->invoice_number }}</p>
                                            </div>
                                        </div>

                                        <form id="refundForm-{{ $sale->id }}" class="mt-4 space-y-4" method="POST" action="{{ route('returns.store', $sale) }}">
                                            @csrf

                                            <div class="mb-4">
                                                <label class="block text-sm font-medium text-gray-700">Refund Method</label>
                                                <select name="refund_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                    <option value="cash">Cash</option>
                                                    <option value="card">Card</option>
                                                    <option value="bank">Bank Transfer</option>
                                                </select>
                                            </div>

                                            {{-- Per-item quantity selectors + inline subtotal --}}
                                            <div class="space-y-3">
                                                @foreach($sale->items as $item)
                                                    @php
                                                        // server-side calculations
                                                        $soldQty = (int) $item->quantity;
                                                        $refundedQty = (int) ($item->refunded_quantity ?? 0);
                                                        $pendingQty = (int) ($item->pending_return_qty ?? 0);
                                                        $available = max(0, $soldQty - $refundedQty - $pendingQty);

                                                        $unitPrice = $soldQty > 0 ? round($item->total_price / $soldQty, 2) : 0;
                                                    @endphp

                                                    <div class="p-3 border rounded return-row" data-sale-item-id="{{ $item->id }}">
                                                        <div class="flex justify-between items-center gap-4">
                                                            <div class="min-w-0">
                                                                <div class="font-medium text-gray-800">{{ optional($item->product)->name ?? $item->product_name }}</div>
                                                                <div class="text-xs text-gray-500">
                                                                    Sold: {{ $soldQty }}
                                                                    • Refunded: {{ $refundedQty }}
                                                                    • Pending: {{ $pendingQty }}
                                                                    • <strong>Available: {{ $available }}</strong>
                                                                    • Unit: {{ format_currency($unitPrice) }}
                                                                </div>
                                                            </div>

                                                            <div class="flex items-end space-x-3">
                                                                <div class="w-28">
                                                                    <label class="text-xs text-gray-600 block mb-1">Qty</label>
                                                                    <input
                                                                        type="number"
                                                                        name="items[{{ $loop->index }}][quantity]"
                                                                        class="return-qty w-full px-2 py-1 border rounded text-right"
                                                                        min="0"
                                                                        max="{{ $available }}"
                                                                        step="1"
                                                                        value="{{ $available > 0 ? 0 : 0 }}"
                                                                        data-unit-price="{{ $unitPrice }}"
                                                                        data-max="{{ $available }}"
                                                                        aria-label="Return quantity for {{ $item->product_name }}"
                                                                        {{ $available <= 0 ? 'disabled' : '' }}
                                                                    >
                                                                    <input type="hidden" name="items[{{ $loop->index }}][sale_item_id]" value="{{ $item->id }}">
                                                                </div>

                                                                {{-- inline subtotal --}}
                                                                <div class="text-right">
                                                                    <div class="text-xs text-gray-500">Subtotal</div>
                                                                    <div id="lineSubtotal-{{ $sale->id }}-{{ $loop->index }}" class="font-medium text-gray-800">0.00</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- Reason --}}
                                            <div class="my-4">
                                                <label class="block text-sm font-medium text-gray-700">Reason</label>
                                                <textarea name="reason" id="reason-{{ $sale->id }}" rows="3"
                                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                                          placeholder="Optional reason for refund"></textarea>
                                            </div>

                                            {{-- Visible refund total --}}
                                            <div class="mt-2 text-right">
                                                <div class="text-sm text-gray-600">Refund Amount</div>
                                                <div id="refundTotalDisplay-{{ $sale->id }}" class="text-2xl font-bold text-gray-900">0.00</div>
                                            </div>

                                            {{-- Hidden authoritative amount (server will recompute) --}}
                                            <input type="hidden" id="refundAmount-{{ $sale->id }}" name="refund_amount" value="0.00">

                                            <div class="flex justify-end space-x-3 mt-4">
                                                <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 cursor-pointer" onclick="closeRefundModal({{ $sale->id }})">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 cursor-pointer">
                                                    Confirm Refund
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="lni lni-package text-4xl text-gray-300 mb-2 block"></i>
                                    No sales found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($sales->hasPages())
                <div class="bg-white px-6 py-4 border-t border-gray-200">
                    {{ $sales->links() }}
                </div>
            @endif
        </x-ui.card>



@endsection

@push('scripts')
    <script>
        function openRefundModal(sale_id) {
            document.getElementById(`createRefundModal-${sale_id}`).classList.remove('hidden');
        }

        function closeRefundModal(sale_id) {
            document.getElementById(`createRefundModal-${sale_id}`).classList.add('hidden');
        }

    //    Refund modal script
        (function () {
            // formatting helper
            const moneyFormatter = new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // open modal for saleId and attach listeners
            window.openRefundModal = function (saleId) {
                const modal = document.getElementById('createRefundModal-' + saleId);
                if (!modal) return;
                modal.classList.remove('hidden');

                // attach listeners to qty inputs inside this modal (idempotent)
                const qtyInputs = modal.querySelectorAll('.return-qty');
                qtyInputs.forEach((input, idx) => {
                    if (!input.__refundListenerAttached) {
                        input.addEventListener('input', () => recalcModalRefund(saleId));
                        input.addEventListener('change', () => recalcModalRefund(saleId));
                        input.__refundListenerAttached = true;
                    }
                });

                // initial calculation
                recalcModalRefund(saleId);
            };

            window.closeRefundModal = function (saleId) {
                const modal = document.getElementById('createRefundModal-' + saleId);
                if (!modal) return;
                modal.classList.add('hidden');

                // reset quantities & subtotals to 0 when closed
                modal.querySelectorAll('.return-qty').forEach(i => { i.value = 0; });
                recalcModalRefund(saleId);
            };

            // recalc total inside the modal with given saleId
            function recalcModalRefund(saleId) {
                const modal = document.getElementById('createRefundModal-' + saleId);
                if (!modal) return;

                let total = 0;
                modal.querySelectorAll('.return-row').forEach((row, rowIndex) => {
                    const qtyInput = row.querySelector('.return-qty');
                    if (!qtyInput) return;

                    let qty = parseInt(qtyInput.value || '0', 10);
                    if (isNaN(qty) || qty < 0) qty = 0;

                    const max = parseInt(qtyInput.dataset.max || '0', 10);
                    if (qty > max) {
                        qty = max;
                        qtyInput.value = max;
                    }

                    const unit = parseFloat(qtyInput.dataset.unitPrice || '0') || 0;
                    const lineTotal = Math.round((qty * unit + Number.EPSILON) * 100) / 100;
                    total += lineTotal;

                    // update per-line subtotal display (element id: lineSubtotal-{saleId}-{index})
                    const lineSubtotalEl = document.getElementById('lineSubtotal-' + saleId + '-' + rowIndex);
                    if (lineSubtotalEl) {
                        lineSubtotalEl.textContent = moneyFormatter.format(lineTotal);
                    }
                });

                // rounding
                total = Math.round((total + Number.EPSILON) * 100) / 100;

                // update visible and hidden fields
                const display = document.getElementById('refundTotalDisplay-' + saleId);
                const hidden = document.getElementById('refundAmount-' + saleId);
                if (display) display.textContent = moneyFormatter.format(total);
                if (hidden) hidden.value = total.toFixed(2);
            }

            // prevent submitting when refund amount is zero (for forms with id refundForm-{saleId})
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!form || !form.id) return;
                const match = form.id.match(/^refundForm-(\d+)$/);
                if (!match) return;

                const saleId = match[1];
                const hidden = document.getElementById('refundAmount-' + saleId);
                const total = hidden ? parseFloat(hidden.value || '0') : 0;
                if (total <= 0) {
                    e.preventDefault();
                    alert('Please select quantity for at least one item to refund.');
                    return false;
                }
            }, true);
        })();
    //    Refund modal script

    </script>
@endpush
