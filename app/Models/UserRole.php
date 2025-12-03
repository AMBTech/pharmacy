<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_system'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function hasPermission($permission): bool
    {
        // If permissions is null or empty, return false
        if (empty($this->permissions)) {
            return false;
        }

        // Check for wildcard permission
        if (in_array('*', $this->permissions)) {
            return true;
        }

        // Check if the specific permission exists
        return in_array($permission, $this->permissions);
    }
}
