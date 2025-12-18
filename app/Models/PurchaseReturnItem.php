<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_return_id',
        'purchase_order_item_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'reason_type',
        'reason'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2'
    ];

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->total_cost = $model->quantity * $model->unit_cost;
        });

        static::saved(function ($model) {
            // Update returned quantity on purchase order item
            $purchaseOrderItem = $model->purchaseOrderItem;
            $totalReturned = PurchaseReturnItem::where('purchase_order_item_id', $purchaseOrderItem->id)
                ->sum('quantity');
            $purchaseOrderItem->update(['returned_quantity' => $totalReturned]);

            // Update purchase return totals
            $model->purchaseReturn->calculateTotals();
        });

        static::deleted(function ($model) {
            // Update returned quantity on purchase order item
            $purchaseOrderItem = $model->purchaseOrderItem;
            $totalReturned = PurchaseReturnItem::where('purchase_order_item_id', $purchaseOrderItem->id)
                ->sum('quantity');
            $purchaseOrderItem->update(['returned_quantity' => $totalReturned]);

            // Update purchase return totals
            $model->purchaseReturn->calculateTotals();
        });
    }

    public function getReasonFormattedAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->reason_type));
    }

}
