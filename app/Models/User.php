<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Backward-compatible accessor for single role reference.
     */
    public function getRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(['super-admin', 'Super Admin']);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        // 1. Super Admin is always granted all permissions by default!
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Aliases support
        if ($permissionSlug === 'pos.terminal' || $permissionSlug === 'pos.access') {
            return $this->hasAnyPermission(['pos.access', 'pos.terminal', 'pos.checkout']);
        }

        if ($permissionSlug === 'sales.return' || $permissionSlug === 'sale_returns.view') {
            return $this->hasAnyPermission(['sales.return', 'sale_returns.view']);
        }

        if ($permissionSlug === 'purchases.return' || $permissionSlug === 'purchase_returns.view') {
            return $this->hasAnyPermission(['purchases.return', 'purchase_returns.view']);
        }

        if ($permissionSlug === 'ledgers.view') {
            return $this->hasAnyPermission(['ledgers.view', 'ledgers.customer', 'ledgers.vendor']);
        }

        try {
            return $this->hasPermissionTo($permissionSlug);
        } catch (\Throwable) {
            return false;
        }
    }
}
