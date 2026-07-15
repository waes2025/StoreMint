<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variation extends Model
{
    use SoftDeletes;

    protected $with = ['details'];

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
        'is_best_seller',
        'slug',
    ];

    protected $appends = [
        'short_description',
        'description',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function details()
    {
        return $this->hasMany(ProductDetail::class);
    }

    public function getShortDescriptionAttribute()
    {
        // Check if relation is loaded, otherwise query or load it
        $detail = $this->details->firstWhere('key', 'short_description');

        return $detail ? $detail->value : null;
    }

    public function getDescriptionAttribute()
    {
        // Check if relation is loaded, otherwise query or load it
        $detail = $this->details->firstWhere('key', 'description');

        return $detail ? $detail->value : null;
    }
}
