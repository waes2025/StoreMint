<?php

namespace Modules\Cart\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];

    /**
     * Get the cart that contains this item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product associated with this cart item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
