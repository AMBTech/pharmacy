<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_number',
        'notes',
        'is_active',
        'total_purchases'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_purchases' => 'decimal:2'
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function getTotalOrdersAttribute()
    {
        return $this->purchaseOrders()->count();
    }

    public function getPendingOrdersAttribute()
    {
        return $this->purchaseOrders()
            ->whereIn('status', ['ordered', 'partial'])
            ->count();
    }
}
