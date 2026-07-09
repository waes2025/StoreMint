<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'business_id',
        'location_id',
        'contact_id',
        'created_by',
        'type',
        'status',
        'payment_status',
        'invoice_no',
        'transaction_date',
        'total_before_tax',
        'discount_amount',
        'coupon_id',
        'shipping_charges',
        'final_total',
        'shipping_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payments()
    {
        return $this->hasMany(TransactionPayment::class, 'transaction_id');
    }
}
