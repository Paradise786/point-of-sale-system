<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'barcode',
        'sku',
        'category_id',
        'unit_id',
        'default_sale_unit_id',
        'default_purchase_unit_id',
        'purchase_price',
        'selling_price',
        'quantity',
        'alert_quantity',
        'description',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'integer',
        'alert_quantity' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function defaultSaleUnit()
    {
        return $this->belongsTo(Unit::class, 'default_sale_unit_id');
    }

    public function defaultPurchaseUnit()
    {
        return $this->belongsTo(Unit::class, 'default_purchase_unit_id');
    }

    public function secondaryUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * Get all available units (Base Unit + Secondary Units) with their conversion rates & prices.
     */
    public function getAvailableUnitsAttribute(): array
    {
        $units = [];

        if ($this->unit) {
            $units[] = [
                'unit_id' => $this->unit->id,
                'name' => $this->unit->name,
                'short_code' => $this->unit->short_code,
                'conversion_rate' => 1.0,
                'sale_price' => (float) $this->selling_price,
                'purchase_price' => (float) $this->purchase_price,
                'is_base' => true,
            ];
        }

        foreach ($this->secondaryUnits as $su) {
            if ($su->unit) {
                $rate = (float) ($su->conversion_rate ?? 1.0);
                $units[] = [
                    'unit_id' => $su->unit->id,
                    'name' => $su->unit->name,
                    'short_code' => $su->unit->short_code,
                    'conversion_rate' => $rate,
                    'sale_price' => round((float) $this->selling_price * $rate, 2),
                    'purchase_price' => round((float) $this->purchase_price * $rate, 2),
                    'is_base' => false,
                ];
            }
        }

        return $units;
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'alert_quantity');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->alert_quantity;
    }
}
