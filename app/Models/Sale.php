<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'cashier_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->invoice_number)) {
                $sale->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $lastInvoice = static::where('invoice_number', 'like', "INV-{$date}-%")->latest()->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return "INV-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function returns()
    {
        return $this->hasMany(\App\Models\ReturnOrder::class, 'sale_id');
    }

    public function getRefundedAmountAttribute()
    {
        // sum of approved return amounts for this sale
        return $this->returns()->where('status','approved')->sum('refund_amount');
    }

    public function getPendingRefundedAmountAttribute()
    {
        // sum of approved return amounts for this sale
        return $this->returns()->where('status','pending')->sum('refund_amount');
    }

    public function getNetAmountAttribute()
    {
        return round($this->total_amount - $this->refunded_amount, 2);
    }

    public function getRefundStatusAttribute()
    {
        // compute based on refunded qty vs sold qty
        $totalSold = $this->items->sum('quantity');
        $totalRefunded = $this->items->sum('refunded_quantity');
        if ($totalRefunded == 0) return 'none';
        if ($totalRefunded < $totalSold) return 'partial';
        return 'full';
    }

    public function isFullyRefunded(): bool
    {
        return $this->refund_status === 'full';
    }
}
