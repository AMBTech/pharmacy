<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'received_quantity',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'notes',
        'returned_quantity'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date'
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getPendingQuantityAttribute()
    {
        return $this->quantity - $this->received_quantity;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->total_cost = $model->quantity * $model->unit_cost;
        });

        static::saved(function ($model) {
            $model->purchaseOrder->calculateTotals();
        });

        static::deleted(function ($model) {
            $model->purchaseOrder->calculateTotals();
        });
    }

    public function getAvailableForReturnAttribute()
    {
        return $this->received_quantity - $this->returned_quantity;
    }
}
