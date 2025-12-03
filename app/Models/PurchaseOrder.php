<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'user_id',
        'order_date',
        'expected_delivery_date',
        'delivery_date',
        'status',
        'subtotal',
        'tax',
        'shipping_cost',
        'total',
        'discount',
        'notes',
        'payment_terms'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'payment_terms' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->po_number) {
                $model->po_number = 'PO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'ordered' => 'bg-blue-100 text-blue-800',
            'received' => 'bg-green-100 text-green-800',
            'partial' => 'bg-yellow-100 text-yellow-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'draft' => 'lni-draft',
            'ordered' => 'lni-shopping-basket',
            'received' => 'lni-checkmark-circle',
            'partial' => 'lni-package',
            'cancelled' => 'lni-close',
            default => 'lni-draft'
        };
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum('total_cost');
        $tax = $subtotal * ($this->tax / 100);
        $total = $subtotal + $tax + $this->shipping_cost - $this->discount;

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total
        ]);
    }

    public function updateReceivedStatus(): void
    {
        $totalItems = $this->items->count();
        $fullyReceivedItems = $this->items->filter(function ($item) {
            return $item->received_quantity >= $item->quantity;
        })->count();
        
        $partiallyReceivedItems = $this->items->filter(function ($item) {
            return $item->received_quantity > 0 && $item->received_quantity < $item->quantity;
        })->count();

        if ($fullyReceivedItems === $totalItems) {
            $status = 'received';
        } elseif ($fullyReceivedItems > 0 || $partiallyReceivedItems > 0) {
            $status = 'partial';
        } else {
            $status = 'ordered';
        }

        $this->update(['status' => $status]);
    }
}
