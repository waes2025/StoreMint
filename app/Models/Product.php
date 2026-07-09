<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'business_id',
        'category_id',
        'price',
        'compare_at_price',
        'stock_status',
        'short_description',
        'description',
        'image',
        'is_featured',
        'is_best_seller',
        'is_active',
        'sku',
        'type',
        'enable_stock',
        'created_by',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
