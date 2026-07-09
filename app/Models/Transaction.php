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
        'payment_gateway',
        'invoice_no',
        'order_number',
        'transaction_date',
        'total_before_tax',
        'discount_amount',
        'coupon_id',
        'coupon_discount_amount',
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
}
