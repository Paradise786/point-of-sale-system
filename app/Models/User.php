<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        if (! $this->role) {
            return false;
        }

        return $this->role->slug === 'super-admin'
            || Str::slug($this->role->name) === 'super-admin'
            || strcasecmp($this->role->name, 'Super Admin') === 0;
    }

    public function hasRole(string|array $roles): bool
    {
        if (! $this->role) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($this->role->slug, $roles, true) || in_array($this->role->name, $roles, true);
        }

        return $this->role->slug === $roles || $this->role->name === $roles;
    }

    public function hasPermission(string $permissionSlug): bool
    {
        // 1. Super Admin is always granted all permissions by default!
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->role) {
            return false;
        }

        // Check if role has the permission
        return $this->role->permissions->contains('slug', $permissionSlug);
    }
}
