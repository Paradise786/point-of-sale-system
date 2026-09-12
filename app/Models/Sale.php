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
        'due_amount',
        'change_amount',
        'payment_method',
        'payment_status',
        'note',
        'description',
        'extra_field_one',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
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

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        return $this->customer ? $this->customer->name : 'Walk-in Customer';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Paid',
            'partially_paid' => 'Partially Paid',
            'unpaid' => 'Unpaid',
            'return' => 'Return',
            default => ucfirst(str_replace('_', ' ', $this->payment_status ?? 'paid')),
        };
    }

    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'partially_paid' => 'bg-amber-50 text-amber-700 border-amber-200',
            'unpaid' => 'bg-rose-50 text-rose-700 border-rose-200',
            'return' => 'bg-purple-50 text-purple-700 border-purple-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public static function computePaymentStatus(float $paid, float $total, bool $hasReturn = false): string
    {
        if ($hasReturn) {
            return 'return';
        }
        if ($paid <= 0) {
            return 'unpaid';
        }
        if ($paid < $total) {
            return 'partially_paid';
        }

        return 'paid';
    }
}
