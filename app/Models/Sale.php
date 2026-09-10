<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'sale_order_id',
        'invoice_number',
        'customer_id',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'note',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        return $this->customer ? $this->customer->name : 'Walk-in Customer';
    }
}
