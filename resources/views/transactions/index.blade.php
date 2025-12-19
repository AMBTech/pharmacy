@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Transactions</h1>
                <p class="text-gray-600 mt-1">View and manage all financial transactions</p>
            </div>
            <div class="flex items-center space-x-4">
                <x-ui.button variant="success" icon="lni lni-download" :href="route('transactions.export', request()->query())" unescaped>
                    Export
                </x-ui.button>
                <x-ui.button variant="primary" icon="lni lni-plus" href="{{ route('transactions.create') }}">
                    Add Transaction
                </x-ui.button>
            </div>
        </div>

        <!-- Transaction Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card class="border-l-4 border-l-green-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="lni lni-revenue text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Sales</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $currency_symbol }} {{ number_format($stats['sales'], 2) }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="border-l-4 border-l-red-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="lni lni-close text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Refunds</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $currency_symbol }} {{ number_format($stats['refunds'], 2) }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="border-l-4 border-l-blue-500">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="lni lni-credit-cards text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Net Flow</p>
                        <p class="text-2xl font-bold text-gray-900 {{ $stats['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $currency_symbol }} {{ number_format($stats['net'], 2) }}
                        </p>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Filters -->
        <x-ui.card title="Filters" padding="p-6">
            <form action="{{ route('transactions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Transaction Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Types</option>
                        <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>Sale</option>
                        <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Refund</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Payment</option>
                    </select>
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method" id="payment_method"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank</option>
                        <option value="online" {{ request('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                    </select>
                </div>

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

                <!-- Amount Range -->
                <div>
                    <label for="amount_range" class="block text-sm font-medium text-gray-700 mb-1">Amount Range</label>
                    <select name="amount_range" id="amount_range"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Amounts</option>
                        <option value="0-100" {{ request('amount_range') == '0-100' ? 'selected' : '' }}>{{$currency_symbol}}0 - {{$currency_symbol}}100</option>
                        <option value="100-500" {{ request('amount_range') == '100-500' ? 'selected' : '' }}>{{$currency_symbol}}100 - {{$currency_symbol}}500</option>
                        <option value="500-1000" {{ request('amount_range') == '500-1000' ? 'selected' : '' }}>{{$currency_symbol}}500 - {{$currency_symbol}}1,000</option>
                        <option value="1000+" {{ request('amount_range') == '1000+' ? 'selected' : '' }}>{{$currency_symbol}}1,000+</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="md:col-span-3">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search notes, type, payment method..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <!-- User Filter (if admin) -->
                @hasPermission('transactions.view')
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Cashier</label>
                    <select name="user_id" id="user_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Cashiers</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endhasPermission

                <!-- Filter Actions -->
                <div class="md:col-span-5 flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('transactions.index') }}"
                       class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                        Clear
                    </a>
                    <button type="submit"
                            class="bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                        Apply Filters
                    </button>
                </div>
            </form>
        </x-ui.card>

        <!-- Transactions Table -->
        <x-ui.card title="Transactions" padding="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date & Time
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment Method
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cashier
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ format_date_time($transaction->created_at) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {!! $transaction->type_badge !!}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
{{--                                    {{dd($transaction)}}--}}
                                    @if($transaction->related_type === 'sale' && $transaction->sale)
                                        Sale: {{ $transaction->sale->invoice_number ?? 'N/A' }}
                                    @elseif($transaction->related_type === 'order_return' && $transaction->orderReturn)
                                        Refund: {{ $transaction->orderReturn->return_number ?? 'N/A' }}
                                    @else
                                        {{ $transaction->notes ?? 'No description' }}
                                    @endif
                                </div>
                                @if($transaction->notes && $transaction->transaction_type !== 'sale' && $transaction->transaction_type !== 'refund')
                                    <div class="text-sm text-gray-500 mt-1">{{ $transaction->notes }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="{{ $transaction->amount_class }} font-bold">
                                    {{ $transaction->signed_amount }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {!! $transaction->payment_method_badge !!}
{{--                                {{dd($transaction)}}--}}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-2">
                                        <i class="lni lni-user text-gray-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ optional($transaction->user)->name ?? 'System' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('transactions.show', $transaction) }}"
                                       class="text-blue-600 hover:text-blue-900 flex items-center p-2 hover:bg-gray-100 rounded-md"
                                       title="View Details">
                                        <i class="lni lni-eye"></i>
                                    </a>
                                    @if($transaction->transaction_type === 'sale' && $transaction->related)
                                        <a href="{{ route('sales.show', $transaction->related) }}"
                                           class="text-green-600 hover:text-green-900 flex items-center p-2 hover:bg-gray-100 rounded-md"
                                           title="View Sale">
                                            <i class="lni lni-shopping-basket"></i>
                                        </a>
                                    @endif
                                    @if($transaction->transaction_type === 'refund' && $transaction->related)
                                        <a href="{{ route('returns.show', $transaction->related) }}"
{{--                                        <a href="{{ route('returns.show', $transaction->related_id) }}"--}}
                                           class="text-red-600 hover:text-red-900 flex items-center p-2 hover:bg-gray-100 rounded-md"
                                           title="View Refund">
                                            <i class="lni lni-reload"></i>
                                        </a>
                                    @endif
                                    @hasPermission('transactions.edit')
                                    <a href="{{ route('transactions.edit', $transaction) }}"
                                       class="text-yellow-600 hover:text-yellow-900 flex items-center p-2 hover:bg-gray-100 rounded-md"
                                       title="Edit">
                                        <i class="lni lni-pencil-alt"></i>
                                    </a>
                                    @endhasPermission
                                    @hasPermission('transactions.delete')
                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-900 flex items-center p-2 hover:bg-gray-100 rounded-md"
                                                title="Delete">
                                            <i class="lni lni-trash-can"></i>
                                        </button>
                                    </form>
                                    @endhasPermission
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="lni lni-package text-4xl text-gray-300 mb-2 block"></i>
                                No transactions found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if($transactions->hasPages())
                    <div class="bg-white px-6 py-4 border-t border-gray-200">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

            <!-- Summary Row -->
            {{--@if($transactions->count() > 0)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg border-t">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions
                        </div>
                        <div class="flex space-x-6">
                            <div class="text-right">
                                <div class="text-sm text-gray-600">Total Inflow</div>
                                <div class="text-lg font-bold text-green-600">
                                    {{ $currency_symbol }} {{ number_format($totalInflow, 2) }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600">Total Outflow</div>
                                <div class="text-lg font-bold text-red-600">
                                    {{ $currency_symbol }} {{ number_format($totalOutflow, 2) }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600">Net Total</div>
                                <div class="text-lg font-bold {{ $netTotal >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $currency_symbol }} {{ number_format($netTotal, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif--}}
        </x-ui.card>


    </div>
@endsection

@push('scripts')
    <script>
        // Quick filter buttons
        document.addEventListener('DOMContentLoaded', function() {
            // Set today's date range
            const todayBtn = document.getElementById('filterToday');
            if (todayBtn) {
                todayBtn.addEventListener('click', function() {
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('start_date').value = today;
                    document.getElementById('end_date').value = today;
                    this.closest('form').submit();
                });
            }

            // Set this month's date range
            const monthBtn = document.getElementById('filterThisMonth');
            if (monthBtn) {
                monthBtn.addEventListener('click', function() {
                    const now = new Date();
                    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
                    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
                    document.getElementById('start_date').value = firstDay;
                    document.getElementById('end_date').value = lastDay;
                    this.closest('form').submit();
                });
            }
        });
    </script>
@endpush
