<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        auth()->user()->hasPermission('transactions.view');

        // Build filters array
        $filters = [
            'type' => $request->type,
            'payment_method' => $request->payment_method,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'search' => $request->search,
            'user_id' => $request->user_id,
        ];

        // Handle amount range filter
        if ($request->amount_range) {
            [$min, $max] = $this->parseAmountRange($request->amount_range);
            if ($min !== null) $filters['min_amount'] = $min;
            if ($max !== null) $filters['max_amount'] = $max;
        }

        // Get transactions with filters
        $query = Transaction::search($filters);
//        dd($query->get());

        // If user doesn't have permission to view all, show only their transactions
        if (!Auth::user()->hasPermission('transactions.view_all')) {
            $query->where('user_id', Auth::id());
        }

        $transactions = $query->paginate(20)->withQueryString();

        // Calculate summary statistics
        $summaryQuery = Transaction::query()->with('related');
        $summaryQuery = $summaryQuery->auth();


        // Apply same filters for summary
        if ($request->filled('start_date')) {
            $summaryQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $summaryQuery->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('type')) {
            $summaryQuery->where('transaction_type', $request->type);
        }
        if ($request->filled('payment_method')) {
            $summaryQuery->where('payment_method', $request->payment_method);
        }
        if ($request->filled('user_id')) {
            $summaryQuery->where('user_id', $request->user_id);
        }

        // Calculate totals
        $totalInflow = (float) $summaryQuery->clone()
            ->whereIn('transaction_type', ['sale', 'payment'])
            ->sum('amount');

        $totalOutflow = (float) $summaryQuery->clone()
            ->whereIn('transaction_type', ['refund', 'expense'])
            ->sum('amount');

        $netTotal = $totalInflow - $totalOutflow;

        // Get stats for cards
        $stats = [
            'sales' => (float) $summaryQuery->clone()->where('transaction_type', 'sale')->sum('amount'),
            'refunds' => (float) $summaryQuery->clone()->where('transaction_type', 'refund')->sum('amount'),
            'expenses' => (float) $summaryQuery->clone()->where('transaction_type', 'expense')->sum('amount'),
            'net' => $netTotal,
        ];

        // Get users for filter (only if user has permission)
        $users = Auth::user()->hasPermission('transactions.view')
            ? User::active()->orderBy('name')->get()
            : collect();

        $currency_symbol = get_currency_symbol();

        return view('transactions.index', [
            'transactions' => $transactions,
            'stats' => $stats,
            'totalInflow' => $totalInflow,
            'totalOutflow' => $totalOutflow,
            'netTotal' => $netTotal,
            'users' => $users,
            'currency_symbol' => $currency_symbol,
        ]);
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        auth()->user()->hasPermission('transactions.create');

        return view('transactions.create', [
            'transactionTypes' => [
                'expense' => 'Expense',
                'payment' => 'Payment',
            ],
            'paymentMethods' => [
                'cash' => 'Cash',
                'card' => 'Card',
                'bank' => 'Bank Transfer',
                'online' => 'Online Payment',
                'wallet' => 'Digital Wallet',
            ],
        ]);
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request)
    {
        auth()->user()->hasPermission('transactions.create');

        $validated = $request->validate([
            'transaction_type' => 'required|in:expense,payment',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank,online,wallet',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'transaction_type' => $validated['transaction_type'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'user_id' => Auth::id(),
            ]);

            // Log activity
            /*activity()
                ->performedOn($transaction)
                ->causedBy(Auth::user())
                ->withProperties(['amount' => $validated['amount']])
                ->log('created manual transaction');*/

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create transaction: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction)
    {
        auth()->user()->hasPermission('transactions.view');

        $currency_symbol = get_currency_symbol();

        return view('transactions.show', [
            'transaction' => $transaction->load(['user', 'related']),
            'currency_symbol' => $currency_symbol
        ]);
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction)
    {
        auth()->user()->hasPermission('transactions.update');

        // Only allow editing of manual transactions (not linked to sales/refunds)
        if ($transaction->related_id && $transaction->related_type) {
            return redirect()->route('transactions.show', $transaction)
                ->with('error', 'This transaction cannot be edited as it is linked to a sale or refund.');
        }

        return view('transactions.edit', [
            'transaction' => $transaction,
            'transactionTypes' => [
                'expense' => 'Expense',
                'payment' => 'Payment',
            ],
            'paymentMethods' => [
                'cash' => 'Cash',
                'card' => 'Card',
                'bank' => 'Bank Transfer',
                'online' => 'Online Payment',
                'wallet' => 'Digital Wallet',
            ],
        ]);
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        auth()->user()->hasPermission('transactions.update');

        // Prevent editing of linked transactions
        if ($transaction->related_id && $transaction->related_type) {
            return back()->with('error', 'Cannot edit transaction linked to sale/refund.');
        }

        $validated = $request->validate([
            'transaction_type' => 'required|in:expense,payment',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank,online,wallet',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $oldAmount = $transaction->amount;

            $transaction->update([
                'transaction_type' => $validated['transaction_type'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Log activity
            /*activity()
                ->performedOn($transaction)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_amount' => $oldAmount,
                    'new_amount' => $validated['amount'],
                    'old_type' => $transaction->getOriginal('transaction_type'),
                    'new_type' => $validated['transaction_type'],
                ])
                ->log('updated transaction');*/

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update transaction: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(Transaction $transaction)
    {
        auth()->user()->hasPermission('transactions.delete');

        // Prevent deletion of linked transactions
        if ($transaction->related_id && $transaction->related_type) {
            return back()->with('error', 'Cannot delete transaction linked to sale/refund.');
        }

        try {
            DB::beginTransaction();

            $amount = $transaction->amount;
            $type = $transaction->transaction_type;

            $transaction->delete();

            // Log activity
            /*activity()
                ->causedBy(Auth::user())
                ->withProperties(['amount' => $amount, 'type' => $type])
                ->log('deleted transaction');*/

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete transaction: ' . $e->getMessage());
        }
    }

    /**
     * Export transactions to Excel.
     */
    public function export(Request $request)
    {
        auth()->user()->hasPermission('transactions.export');

        $exportType = $request->input('export_type', 'excel');

        // Build filters (same as index)
        $filters = [
            'type' => $request->type,
            'payment_method' => $request->payment_method,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'search' => $request->search,
            'user_id' => $request->user_id,
        ];

        $query = Transaction::search($filters)
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        // If user doesn't have permission to view all, show only their transactions
        if (!Auth::user()->hasPermission('transactions.view_all')) {
            $query->where('user_id', Auth::id());
        }

        $transactions = $query->get();

        return match ($exportType) {
            'excel' => Excel::download(new \App\Exports\TransactionExport($transactions, $filters), 'transactions-report-' . now()->format('Y-m-d') . '.xlsx'),
            'csv' => Excel::download(new \App\Exports\TransactionExport($transactions, $filters), 'transactions-report-' . now()->format('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV),
            default => back()->with('error', 'Invalid export type'),
        };
    }

    /**
     * Get transaction statistics for dashboard.
     */
    public function statistics(Request $request)
    {
        auth()->user()->hasPermission('transactions.view');

        $transactions = Transaction::getDashboardStats();
        $period = $request->get('period', 'today');

        switch ($period) {
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->get('start_date');
                $endDate = $request->get('end_date');
                break;
            default: // today
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
        }

        $stats = [
            'sales' => Transaction::getSalesTotal($startDate, $endDate),
            'refunds' => Transaction::getRefundsTotal($startDate, $endDate),
            'expenses' => Transaction::getExpensesTotal($startDate, $endDate),
            'payments' => Transaction::getPaymentsTotal($startDate, $endDate),
            'net_cash_flow' => Transaction::getNetCashFlow($startDate, $endDate),
        ];

        // Get payment method breakdown
        $paymentBreakdown = [
            'cash' => Transaction::sales()->cash()->whereBetween('created_at', [$startDate, $endDate])->sum('amount'),
            'card' => Transaction::sales()->card()->whereBetween('created_at', [$startDate, $endDate])->sum('amount'),
            'bank' => Transaction::sales()->bank()->whereBetween('created_at', [$startDate, $endDate])->sum('amount'),
            'online' => Transaction::sales()->where('payment_method', 'online')->whereBetween('created_at', [$startDate, $endDate])->sum('amount'),
        ];

        return view('transactions.statistics',
            compact(
                'stats',
                'paymentBreakdown',
                'period',
                'startDate',
                'endDate',
                'transactions'
            ));

        /*return response()->json([
            'stats' => $stats,
            'payment_breakdown' => $paymentBreakdown,
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);*/
    }

    /**
     * Parse amount range string (e.g., "0-100", "100-500", "1000+")
     */
    private function parseAmountRange($range)
    {
        if (str_contains($range, '+')) {
            $min = (float) str_replace('+', '', $range);
            return [$min, null];
        }

        if (str_contains($range, '-')) {
            [$min, $max] = explode('-', $range);
            return [(float) $min, (float) $max];
        }

        return [null, null];
    }
}
