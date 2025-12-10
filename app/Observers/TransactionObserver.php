<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Sale;
use App\Models\ReturnOrder;
//use App\Models\Expense;
//use App\Models\Payment;

class TransactionObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public static function handleSaleCreated(Sale $sale): void
    {
        if ($sale->total > 0) {
            Transaction::create([
                'transaction_type' => 'sale',
                'related_id' => $sale->id,
                'related_type' => Sale::class,
                'amount' => $sale->total,
                'payment_method' => $sale->payment_method ?? 'cash',
                'notes' => 'Sale #' . $sale->order_number,
                'user_id' => $sale->user_id ?? auth()->id(),
            ]);
        }
    }

    /**
     * Handle the Sale "updated" event.
     */
    public static function handleSaleUpdated(Sale $sale): void
    {
        // If sale was cancelled, create refund transaction
        if ($sale->wasChanged('status') && $sale->status === 'cancelled') {
            self::handleRefundCreated($sale);
        }
    }

    /**
     * Handle the OrderReturn "created" event.
     */
    public static function handleReturnCreated(ReturnOrder $orderReturn): void
    {
        // Transaction will be created when refund is processed, not when return is created
    }

    /**
     * Handle the OrderReturn "updated" event.
     */
    public static function handleReturnUpdated(ReturnOrder $orderReturn): void
    {
        // Create refund transaction when return status changes to refunded
        if ($orderReturn->wasChanged('status') && $orderReturn->status === 'refunded') {
            Transaction::create([
                'transaction_type' => 'refund',
                'related_id' => $orderReturn->id,
                'related_type' => ReturnOrder::class,
                'amount' => $orderReturn->total_refund_amount,
                'payment_method' => $orderReturn->refund_method ?? 'cash',
                'notes' => 'Refund for Return #' . $orderReturn->return_number,
                'user_id' => $orderReturn->user_id ?? auth()->id(),
            ]);
        }
    }

    /**
     * Handle the Expense "created" event.
     */
//    public static function handleExpenseCreated(Expense $expense): void
//    {
//        Transaction::create([
//            'transaction_type' => 'expense',
//            'related_id' => $expense->id,
//            'related_type' => Expense::class,
//            'amount' => $expense->amount,
//            'payment_method' => $expense->payment_method ?? 'cash',
//            'notes' => $expense->description,
//            'user_id' => $expense->user_id ?? auth()->id(),
//        ]);
//    }

    /**
     * Handle the Payment "created" event.
     */
//    public static function handlePaymentCreated(Payment $payment): void
//    {
//        Transaction::create([
//            'transaction_type' => 'payment',
//            'related_id' => $payment->id,
//            'related_type' => Payment::class,
//            'amount' => $payment->amount,
//            'payment_method' => $payment->method ?? 'cash',
//            'notes' => $payment->description,
//            'user_id' => $payment->user_id ?? auth()->id(),
//        ]);
//    }

    /**
     * Create a manual sale transaction.
     */
    public static function createSaleTransaction($sale, $paymentMethod = 'cash'): Transaction
    {
        return Transaction::create([
            'transaction_type' => 'sale',
            'related_id' => $sale->id,
            'related_type' => get_class($sale),
            'amount' => $sale->total_amount,
            'payment_method' => $paymentMethod,
            'notes' => 'Sale #' . ($sale->order_number ?? $sale->id),
            'user_id' => $sale->user_id ?? auth()->id(),
        ]);
    }

    /**
     * Create a manual refund transaction.
     */
    public static function createRefundTransaction($orderReturn, $paymentMethod = 'cash'): Transaction
    {
        return Transaction::create([
            'transaction_type' => 'refund',
            'related_id' => $orderReturn->id,
            'related_type' => get_class($orderReturn),
            'amount' => $orderReturn->refund_amount,
            'payment_method' => $paymentMethod,
            'notes' => 'Refund for Return #' . $orderReturn->return_number,
            'user_id' => $orderReturn->user_id ?? auth()->id(),
        ]);
    }

    /**
     * Create a manual expense transaction.
     */
    public static function createExpenseTransaction($expense): Transaction
    {
        return Transaction::create([
            'transaction_type' => 'expense',
            'related_id' => $expense->id,
            'related_type' => get_class($expense),
            'amount' => $expense->amount,
            'payment_method' => $expense->payment_method ?? 'cash',
            'notes' => $expense->description,
            'user_id' => $expense->user_id ?? auth()->id(),
        ]);
    }

    /**
     * Create a manual payment transaction.
     */
    public static function createPaymentTransaction($payment): Transaction
    {
        return Transaction::create([
            'transaction_type' => 'payment',
            'related_id' => $payment->id,
            'related_type' => get_class($payment),
            'amount' => $payment->amount,
            'payment_method' => $payment->method ?? 'cash',
            'notes' => $payment->description,
            'user_id' => $payment->user_id ?? auth()->id(),
        ]);
    }

    /**
     * Create a direct transaction (manual entry).
     */
    public static function createDirectTransaction(array $data): Transaction
    {
        return Transaction::create(array_merge([
            'user_id' => auth()->id(),
        ], $data));
    }
}
