<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessLocation extends Model
{
    protected $table = 'business_locations';

    protected $fillable = [
        'business_id',
        'location_id',
        'name',
        'landmark',
        'country',
        'state',
        'city',
        'zip_code',
        'invoice_scheme_id',
        'location_name',
        'default_payment_status',
        'purchase_in_progress_payment_status',
    ];

    /**
     * Get the business this location belongs to
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /**
     * Get all transactions for this location
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'location_id');
    }

    /**
     * Get full address string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->landmark,
            $this->city,
            $this->state,
            $this->country,
            $this->zip_code,
        ]);

        return implode(', ', $parts);
    }
}
