<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HeldSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'hold_id',
        'cashier_id',
        'customer_name',
        'customer_phone',
        'cart_data',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'held_at',
        'expires_at'
    ];

    protected $casts = [
        'cart_data' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'held_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($heldSale) {
            if (empty($heldSale->hold_id)) {
                $heldSale->hold_id = 'HOLD-' . Str::upper(Str::random(8));
            }
            if (empty($heldSale->held_at)) {
                $heldSale->held_at = now();
            }
            if (empty($heldSale->expires_at)) {
                $heldSale->expires_at = now()->addHours(24); // Expire after 24 hours
            }
        });
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    // Check if hold is expired
    public function getIsExpiredAttribute()
    {
        return $this->expires_at->isPast();
    }

    // Check if hold is still valid
    public function getIsValidAttribute()
    {
        return !$this->is_expired;
    }

    // Scope for valid (non-expired) holds
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    // Scope for expired holds
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
