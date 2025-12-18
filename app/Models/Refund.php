<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'refund_number',
        'purchase_return_id',
        'purchase_order_id',
        'supplier_id',
        'amount',
        'refund_date',
        'method',
        'reference',
        'status',
        'bank_name',
        'account_name',
        'account_number',
        'routing_number',
        'swift_code',
        'check_number',
        'check_date',
        'check_due_date',
        'credit_balance',
        'credit_expiry_date',
        'notes',
        'failure_reason',
        'created_by',
        'approved_by',
        'processed_by',
        'approved_at',
        'processed_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_date' => 'date',
        'check_date' => 'date',
        'check_due_date' => 'date',
        'credit_balance' => 'decimal:2',
        'credit_expiry_date' => 'date',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->refund_number) {
                $model->refund_number = 'REF-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }

            // Ensure unique refund number
            while (self::where('refund_number', $model->refund_number)->exists()) {
                $model->refund_number = 'REF-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'pending' => 'lni lni-hourglass',
            'processing' => 'lni lni-spinner-arrow',
            'completed' => 'lni lni-checkmark-circle',
            'failed' => 'lni lni-close-circle',
            'cancelled' => 'lni lni-ban',
            default => 'lni lni-hourglass',
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return match($this->method) {
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'check' => 'Check',
            'credit_note' => 'Credit Note',
            'store_credit' => 'Store Credit',
            'other' => 'Other',
            default => ucfirst($this->method),
        };
    }

    public function getPaymentDetailsAttribute(): array
    {
        $details = [];

        switch ($this->method) {
            case 'bank_transfer':
                $details = [
                    'Bank Name' => $this->bank_name,
                    'Account Name' => $this->account_name,
                    'Account Number' => $this->account_number,
                    'Reference' => $this->reference,
                ];
                break;

            case 'check':
                $details = [
                    'Check Number' => $this->check_number,
                    'Check Date' => $this->check_date?->format('M d, Y'),
                    'Due Date' => $this->check_due_date?->format('M d, Y'),
                ];
                break;

            case 'store_credit':
                $details = [
                    'Credit Balance' => format_currency($this->credit_balance),
                    'Expiry Date' => $this->credit_expiry_date?->format('M d, Y'),
                ];
                break;
        }

        return array_filter($details);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'pending';
    }
}
