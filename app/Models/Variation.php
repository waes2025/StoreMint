<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'product_id',
        'sub_sku',
        'product_variation_id',
        'woocommerce_variation_id',
        'variation_value_id',
        'default_purchase_price',
        'dpp_inc_tax',
        'profit_percent',
        'default_sell_price',
        'sell_price_inc_tax',
        'pcs_per_box',
        'combo_variations',
        'compare_at_price',
        'short_description',
        'is_best_seller',
        'description',
        'slug',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
