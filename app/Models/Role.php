<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->slug === 'super-admin' || Str::slug($this->name) === 'super-admin') {
            return true;
        }

        return $this->hasPermissionTo($permissionSlug);
    }

    public static function createWithSlug(string $name, ?string $description = null, bool $isSystem = false): self
    {
        return self::create([
            'name' => $name,
            'guard_name' => 'web',
            'slug' => Str::slug($name),
            'description' => $description,
            'is_system' => $isSystem,
        ]);
    }
}
