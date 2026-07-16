<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $table = 'business';

    protected $fillable = [
        'name',
        'currency_id',
        'start_date',
        'tax_number_1',
        'tax_label_1',
        'tax_number_2',
        'tax_label_2',
        'code_label_1',
        'code_label_2',
        'code_label_3',
        'owner_id',
        'invoice_scheme_id',
        'credit_limit_before_alert',
        'accounting_method',
        'default_profit_margin',
        'website',
        'is_active',
        'enable_bnpl',
        'print_receipt_on_invoice',
        'receipt_printer_type',
        'receipt_printer_ip',
        'enabled_modules',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_active' => 'boolean',
        'enable_bnpl' => 'boolean',
        'print_receipt_on_invoice' => 'boolean',
        'enabled_modules' => 'array',
    ];

    /**
     * Get the enabled modules, defaulting to Shop if null.
     */
    public function getEnabledModulesAttribute($value): array
    {
        if (is_null($value)) {
            return ['Shop'];
        }
        return json_decode($value, true) ?: [];
    }

    /**
     * Get the owner (User) of this business
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all locations for this business
     */
    public function locations(): HasMany
    {
        return $this->hasMany(BusinessLocation::class, 'business_id');
    }

    /**
     * Get all transactions for this business
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'business_id');
    }

    /**
     * Get all products for this business
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'business_id');
    }

    /**
     * Get all settings for this business
     */
    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'business_id');
    }
}
