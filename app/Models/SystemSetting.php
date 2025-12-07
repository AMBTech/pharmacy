<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_rate',
        'currency',
        'low_stock_threshold',
//        'company_name',
//        'company_address',
//        'company_phone',
//        'license_number',
//        'company_email'
    ];

    protected $guarded = [
        'company_name',
        'company_address',
        'company_phone',
        'license_number',
        'company_email'
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'low_stock_threshold' => 'integer'
    ];

    public static function getSettings()
    {
        return self::firstOrCreate([], [
            'tax_rate' => 0.00,
            'currency' => 'PKR',
            'low_stock_threshold' => 10
        ]);
    }
}
