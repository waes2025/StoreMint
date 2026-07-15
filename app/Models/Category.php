<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'business_id',
        'short_code',
        'parent_id',
        'created_by',
        'category_type',
        'description',
        'slug',
        'image',
        'sort_order',
        'is_allow_ecom',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
