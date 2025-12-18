<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PurchaseReturn;

class PurchaseReturnPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('purchase_returns.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->hasPermission('purchase_returns.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('purchase_returns.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->hasPermission('purchase_returns.edit')
            && $purchaseReturn->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->hasPermission('purchase_returns.delete')
            && $purchaseReturn->status === 'pending';
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->hasPermission('purchase_returns.approve')
            && $purchaseReturn->status === 'pending';
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->hasPermission('purchase_returns.approve')
            && $purchaseReturn->status === 'pending';
    }

    /**
     * Determine whether the user can complete the model.
     */
    public function complete(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->hasPermission('purchase_returns.complete')
            && $purchaseReturn->status === 'approved';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->hasPermission('purchase_returns.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->hasPermission('purchase_returns.force_delete');
    }
}
