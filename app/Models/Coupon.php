<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_order_amount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'status',
        'created_by',
    ];
}
