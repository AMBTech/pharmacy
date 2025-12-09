<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'return_number',
        'customer_name',
        'subtotal',
        'tax_amount',
        'refund_amount',
        'refund_method',
        'reason',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function items()
    {
        return $this->hasMany(ReturnOrderItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($return) {
            if (empty($return->return_number)) {
                $return->return_number = static::generateReturnNumber();
            }
        });
    }

    /**
     * Generate a sequential return number grouped by day.
     */
    public static function generateReturnNumber(): string
    {
        $date = now()->format('Ymd');
        $lastReturn = static::where('return_number', 'like', "RET-{$date}-%")
            ->latest()
            ->first();

        $sequence = $lastReturn ? (int) substr($lastReturn->return_number, -4) + 1 : 1;

        return 'RET-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApprove($query)
    {
        return $query->where('status', 'approve');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-gray-100 text-gray-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'pending' => 'lni-clock',
            'approved' => 'lni-tick',
            'rejected' => 'lni-times',
            'cancelled' => 'lni-block',
            default => 'lni-block'
        };
    }
}
