<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_code',
        'base_unit_id',
        'operator',
        'conversion_factor',
    ];

    /**
     * The parent base unit (if any).
     */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * Units derived from this base unit.
     */
    public function subUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    /**
     * Products using this unit.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Convert given quantity in this unit to base unit quantity.
     */
    public function toBaseQuantity(float $quantity): float
    {
        if (! $this->base_unit_id) {
            return $quantity;
        }

        if ($this->operator === '*') {
            return $quantity * (float) $this->conversion_factor;
        }

        return $quantity / (float) $this->conversion_factor;
    }
}
