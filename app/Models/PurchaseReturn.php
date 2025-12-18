<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'purchase_order_id',
        'return_date',
        'return_type',
        'status',
        'reason',
        'subtotal',
        'restocking_fee',
        'shipping_cost',
        'total',
        'notes'
    ];

    protected $casts = [
        'return_date' => 'date',
        'subtotal' => 'decimal:2',
        'restocking_fee' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->return_number) {
                $model->return_number = 'RET-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getReturnTypeFormattedAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->return_type));
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'rejected' => 'bg-red-100 text-red-800',
            'completed' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'pending' => 'lni-hourglass',
            'approved' => 'lni-checkmark-circle',
            'rejected' => 'lni-close',
            'completed' => 'lni-checkmark',
            default => 'lni-hourglass'
        };
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum('total_cost');
        $feeAmount = $subtotal * ($this->restocking_fee / 100);
        $total = $subtotal - $feeAmount - $this->shipping_cost;

        $this->update([
            'subtotal' => $subtotal,
            'total' => $total
        ]);
    }

    // Add this method to calculate totals on the fly
    public static function calculateReturnTotals($items, $restockingFee, $shippingCost)
    {
        $subtotal = collect($items)->sum(function($item) {
            return ($item['quantity'] ?? 0) * ($item['unit_cost'] ?? 0);
        });

        $feeAmount = $subtotal * ($restockingFee / 100);
        $total = $subtotal - $feeAmount - $shippingCost;

        return [
            'subtotal' => $subtotal,
            'total' => max($total, 0), // Ensure total is not negative
        ];
    }

// Add this scope for filtering
    public function scopeWithReturnableItems($query)
    {
        return $query->whereHas('purchaseOrder.items', function($q) {
            $q->where('received_quantity', '>', DB::raw('COALESCE(returned_quantity, 0)'));
        });
    }
}
