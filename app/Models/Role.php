<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->slug === 'super-admin' || Str::slug($this->name) === 'super-admin') {
            return true;
        }

        return $this->permissions->contains('slug', $permissionSlug);
    }

    public static function createWithSlug(string $name, ?string $description = null, bool $isSystem = false): self
    {
        return self::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $description,
            'is_system' => $isSystem,
        ]);
    }
}
