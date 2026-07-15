<?php

namespace App\Models;

use App\Concerns\BelongsToBusinessContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use BelongsToBusinessContext, SoftDeletes;

    protected $fillable = [
        'name',
        'business_id',
        'category_id',
        'image',
        'is_featured',
        'is_allow_ecom',
        'sku',
        'type',
        'enable_stock',
        'created_by',
    ];

    public function variations()
    {
        return $this->hasMany(Variation::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Calculate current stock dynamically from purchase_lines and transaction_sell_lines.
     */
    public function currentStock(?int $locationId = null): int
    {
        $purchasedQuery = DB::table('purchase_lines')
            ->join('transactions', 'purchase_lines.transaction_id', '=', 'transactions.id')
            ->where('purchase_lines.product_id', $this->id)
            ->whereNotIn('transactions.status', ['cancelled', 'draft', 'quotation']);

        if ($locationId) {
            $purchasedQuery->where('transactions.location_id', $locationId);
        }

        $purchased = (float) $purchasedQuery->sum('purchase_lines.quantity');

        $soldQuery = DB::table('transaction_sell_lines')
            ->join('transactions', 'transaction_sell_lines.transaction_id', '=', 'transactions.id')
            ->where('transaction_sell_lines.product_id', $this->id)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('transactions.type', 'sell')
                        ->whereNotIn('transactions.status', ['cancelled', 'draft', 'quotation']);
                })->orWhere(function ($q) {
                    $q->whereIn('transactions.type', ['sales_order', 'sale_order'])
                        ->whereNotIn('transactions.status', ['cancelled', 'draft', 'quotation', 'completed']);
                });
            });

        if ($locationId) {
            $soldQuery->where('transactions.location_id', $locationId);
        }

        $sold = (float) $soldQuery->sum('transaction_sell_lines.quantity');

        return (int) ($purchased - $sold);
    }

    /**
     * Determine dynamic stock status.
     */
    public function currentStockStatus(?int $locationId = null): string
    {
        return $this->currentStock($locationId) > 0 ? 'in_stock' : 'out_of_stock';
    }
}
