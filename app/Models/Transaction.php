<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_type',
        'related_id',
        'related_type',
        'amount',
        'payment_method',
        'notes',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeAuth($query)
    {
        $user = auth()->user();
        if ($user && !$user->hasPermission('view_all_transactions')) {
            return $query->where('user_id', $user->id);
        }
        return $query;
    }

    /**
     * Get the user who created the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model (polymorphic).
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for sales transactions.
     */
    public function scopeSales($query)
    {
        return $query->where('transaction_type', 'sale');
    }

    /**
     * Scope for refund transactions.
     */
    public function scopeRefunds($query)
    {
        return $query->where('transaction_type', 'refund');
    }

    /**
     * Scope for expense transactions.
     */
    public function scopeExpenses($query)
    {
        return $query->where('transaction_type', 'expense');
    }

    /**
     * Scope for payment transactions.
     */
    public function scopePayments($query)
    {
        return $query->where('transaction_type', 'payment');
    }

    /**
     * Scope for cash transactions.
     */
    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    /**
     * Scope for card transactions.
     */
    public function scopeCard($query)
    {
        return $query->where('payment_method', 'card');
    }

    /**
     * Scope for bank transactions.
     */
    public function scopeBank($query)
    {
        return $query->where('payment_method', 'bank');
    }

    /**
     * Scope for today's transactions.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for this week's transactions.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope for this month's transactions.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    /**
     * Get the transaction type as a human-readable label.
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'sale' => 'Sale',
            'refund' => 'Refund',
            'return_rejection' => 'Return Rejection',
            'expense' => 'Expense',
            'payment' => 'Payment',
        ];

        return $labels[$this->transaction_type] ?? ucfirst($this->transaction_type);
    }

    /**
     * Get the payment method as a human-readable label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        $labels = [
            'cash' => 'Cash',
            'card' => 'Card',
            'bank' => 'Bank Transfer',
            'online' => 'Online Payment',
            'wallet' => 'Digital Wallet',
        ];

        return $labels[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    /**
     * Get the amount with sign (positive/negative).
     */
    public function getSignedAmountAttribute(): string
    {
        $currency_symbol = get_currency_symbol();
        $sign = '';
        $amount = number_format(abs($this->amount), 2);

        if (in_array($this->transaction_type, ['sale', 'payment'])) {
            $sign = '+';
        } elseif (in_array($this->transaction_type, ['refund', 'expense'])) {
            $sign = '-';
        }

        return $sign . $currency_symbol . $amount;
    }

    /**
     * Get the CSS class for amount display.
     */
    public function getAmountClassAttribute(): string
    {
        if (in_array($this->transaction_type, ['sale', 'payment'])) {
            return 'text-green-600';
        } elseif (in_array($this->transaction_type, ['refund', 'expense'])) {
            return 'text-red-600';
        }

        return 'text-gray-600';
    }

    /**
     * Get the badge HTML for transaction type.
     */
    public function getTypeBadgeAttribute(): string
    {
        $badges = [
            'sale' => 'bg-green-100 text-green-800',
            'refund' => 'bg-red-100 text-red-800',
            'return_rejection' => 'bg-gray-100 text-gray-800',
            'expense' => 'bg-orange-100 text-orange-800',
            'payment' => 'bg-blue-100 text-blue-800',
        ];

        $class = $badges[$this->transaction_type] ?? 'bg-gray-100 text-gray-800';

        return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $class . '">' .
            $this->type_label . '</span>';
    }

    /**
     * Get the badge HTML for payment method.
     */
    public function getPaymentMethodBadgeAttribute(): string
    {
        $badges = [
            'cash' => 'bg-yellow-100 text-yellow-800',
            'card' => 'bg-purple-100 text-purple-800',
            'bank' => 'bg-indigo-100 text-indigo-800',
            'online' => 'bg-teal-100 text-teal-800',
        ];

        $class = $badges[$this->payment_method] ?? 'bg-gray-100 text-gray-800';

        return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $class . '">' .
            $this->payment_method_label . '</span>';
    }

    /**
     * Create a sale transaction.
     */
    public static function createSale(array $data): Transaction
    {
        $data['transaction_type'] = 'sale';
        return self::create($data);
    }

    /**
     * Create a refund transaction.
     */
    public static function createRefund(array $data): Transaction
    {
        $data['transaction_type'] = 'refund';
        return self::create($data);
    }

    /**
     * Create an expense transaction.
     */
    public static function createExpense(array $data): Transaction
    {
        $data['transaction_type'] = 'expense';
        return self::create($data);
    }

    /**
     * Create a payment transaction.
     */
    public static function createPayment(array $data): Transaction
    {
        $data['transaction_type'] = 'payment';
        return self::create($data);
    }

    /**
     * Get total sales amount for a period.
     */
    public static function getSalesTotal($startDate = null, $endDate = null): float
    {
        $query = self::sales();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->sum('amount');
    }

    /**
     * Get total refunds amount for a period.
     */
    public static function getRefundsTotal($startDate = null, $endDate = null): float
    {
        $query = self::refunds();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->sum('amount');
    }

    /**
     * Get total expenses amount for a period.
     */
    public static function getExpensesTotal($startDate = null, $endDate = null): float
    {
        $query = self::expenses();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->sum('amount');
    }

    /**
     * Get total payments amount for a period.
     */
    public static function getPaymentsTotal($startDate = null, $endDate = null): float
    {
        $query = self::payments();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->sum('amount');
    }

    /**
     * Get net cash flow for a period.
     */
    public static function getNetCashFlow($startDate = null, $endDate = null): float
    {
        $inflow = self::getSalesTotal($startDate, $endDate) +
            self::getPaymentsTotal($startDate, $endDate);

        $outflow = self::getRefundsTotal($startDate, $endDate) +
            self::getExpensesTotal($startDate, $endDate);

        return $inflow - $outflow;
    }

    /**
     * Get daily cash summary.
     */
    public static function getDailySummary($date = null): array
    {
        $date = $date ?? today();

        return [
            'date' => $date->format('Y-m-d'),
            'total_sales' => self::sales()->whereDate('created_at', $date)->sum('amount'),
            'total_refunds' => self::refunds()->whereDate('created_at', $date)->sum('amount'),
            'total_expenses' => self::expenses()->whereDate('created_at', $date)->sum('amount'),
            'total_payments' => self::payments()->whereDate('created_at', $date)->sum('amount'),
            'cash_sales' => self::sales()->cash()->whereDate('created_at', $date)->sum('amount'),
            'card_sales' => self::sales()->card()->whereDate('created_at', $date)->sum('amount'),
            'bank_sales' => self::sales()->bank()->whereDate('created_at', $date)->sum('amount'),
        ];
    }

    /**
     * Get monthly summary.
     */
    public static function getMonthlySummary($year = null, $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();

        return [
            'month' => $startDate->format('F Y'),
            'total_sales' => self::getSalesTotal($startDate, $endDate),
            'total_refunds' => self::getRefundsTotal($startDate, $endDate),
            'total_expenses' => self::getExpensesTotal($startDate, $endDate),
            'total_payments' => self::getPaymentsTotal($startDate, $endDate),
            'net_cash_flow' => self::getNetCashFlow($startDate, $endDate),
        ];
    }

    /**
     * Get transaction statistics for dashboard.
     */
    public static function getDashboardStats(): array
    {
        return [
            'today' => [
                'sales' => self::sales()->today()->sum('amount'),
                'refunds' => self::refunds()->today()->sum('amount'),
                'expenses' => self::expenses()->today()->sum('amount'),
                'net' => self::sales()->today()->sum('amount') -
                    self::refunds()->today()->sum('amount') -
                    self::expenses()->today()->sum('amount'),
            ],
            'this_week' => [
                'sales' => self::sales()->thisWeek()->sum('amount'),
                'refunds' => self::refunds()->thisWeek()->sum('amount'),
                'expenses' => self::expenses()->thisWeek()->sum('amount'),
                'net' => self::sales()->thisWeek()->sum('amount') -
                    self::refunds()->thisWeek()->sum('amount') -
                    self::expenses()->thisWeek()->sum('amount'),
            ],
            'this_month' => [
                'sales' => self::sales()->thisMonth()->sum('amount'),
                'refunds' => self::refunds()->thisMonth()->sum('amount'),
                'expenses' => self::expenses()->thisMonth()->sum('amount'),
                'net' => self::sales()->thisMonth()->sum('amount') -
                    self::refunds()->thisMonth()->sum('amount') -
                    self::expenses()->thisMonth()->sum('amount'),
            ],
        ];
    }

    /**
     * Search transactions with filters.
     */
    public static function search(array $filters = [])
    {
        $query = self::query();

        if (!empty($filters['type'])) {
            $query->where('transaction_type', $filters['type']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('notes', 'LIKE', "%{$search}%")
                    ->orWhere('transaction_type', 'LIKE', "%{$search}%")
                    ->orWhere('payment_method', 'LIKE', "%{$search}%");
            });
        }

        // Default ordering
        $query->orderBy('created_at', 'desc');

        return $query;
    }

    /**
     * Get the related sale if transaction is for a sale.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'related_id')
            ->where('related_type', Sale::class)
            ->where('transaction_type', Sale::class);
    }

    /**
     * Get the related refund if transaction is for a refund.
     */
    public function refund()
    {
        if ($this->transaction_type === 'refund' && $this->related_type === ReturnOrder::class) {
            return $this->related;
        }
        return null;
    }

    /**
     * Get the related order return if transaction is for a refund.
     */
    public function orderReturn()
    {
        return $this->belongsTo(ReturnOrder::class, 'related_id')
            ->where('related_type', 'order_return')
            ->where('transaction_type', 'refund');
    }

    /**
     * Link this transaction to a sale.
     */
    public function linkToSale($saleId): self
    {
        $this->update([
            'related_id' => $saleId,
            'related_type' => Sale::class,
        ]);

        return $this;
    }

    /**
     * Link this transaction to an order return.
     */
    public function linkToOrderReturn($returnId): self
    {
        $this->update([
            'related_id' => $returnId,
            'related_type' => ReturnOrder::class,
        ]);

        return $this;
    }

    /**
     * Link this transaction to a user.
     */
    public function linkToUser($userId): self
    {
        $this->update(['user_id' => $userId]);
        return $this;
    }
}
