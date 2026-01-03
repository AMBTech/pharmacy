<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{

    protected $fillable = [
        'bucket_code',
        'shelf_code',
        'slot_code',
        'label',
        'description',
        'is_active'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
