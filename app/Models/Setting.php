<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'business_id',
        'key',
        'value',
    ];

    /**
     * Get the business that owns this setting
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
