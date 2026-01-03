<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'storage_location_id',
        'name',
        'barcode',
        'generic_name',
        'brand',
        'price',
        'stock',
        'unit',
        'description',
        'image',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : 'Uncategorized';
    }

    public function getCategoryColorAttribute()
    {
        return $this->category ? $this->category->color : '#6b7280';
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function activeBatches(): HasMany
    {
        return $this->hasMany(ProductBatch::class)
            ->where('expiry_date', '>=', now())
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date');
    }

    // Accessor - Use this anywhere: $product->active_batches_count
    public function getActiveBatchesCountAttribute()
    {
        if (!$this->relationLoaded('activeBatches')) {
            return $this->activeBatches()->count();
        }

        return $this->activeBatches->count();
    }

    // Accessor for total quantity in active batches
    public function getActiveBatchesStockAttribute()
    {
        if (!$this->relationLoaded('activeBatches')) {
            return $this->activeBatches()->sum('quantity');
        }

        return $this->activeBatches->sum('quantity');
    }

    // Accessor for expiring soon batches (within 30 days)
    public function getExpiringSoonBatchesCountAttribute()
    {
        return $this->batches()
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('quantity', '>', 0)
            ->count();
    }

    public function scopeWithActiveBatches($query)
    {
        return $query->where('batches', function ($q) {
            return $q->where('expiry_date', '>=', now());
        });
    }

    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class);
    }

    public function getStorageLocationLabelAttribute(): string
    {
        if (! $this->storageLocation) {
            return '—';
        }

        $parts = [
            $this->storageLocation->bucket_code,
            $this->storageLocation->shelf_code,
            $this->storageLocation->slot_code,
        ];

        // Remove null / empty parts (e.g. no slot)
        $code = implode('-', array_filter($parts));

        // Append label if exists
//        if ($this->storageLocation->label) {
//            $code .= ' (' . $this->storageLocation->label . ')';
//        }

        return $code;
    }

}
