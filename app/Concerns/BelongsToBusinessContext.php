<?php

namespace App\Concerns;

use App\Models\Business;

/**
 * Trait for models that belong to a specific business and location
 */
trait BelongsToBusinessContext
{
    /**
     * Scope to get records for the current business
     */
    public function scopeForCurrentBusiness($query)
    {
        $businessId = currentBusinessId();

        if ($businessId) {
            return $query->where('business_id', $businessId);
        }

        return $query;
    }

    /**
     * Scope to get records for the current location
     */
    public function scopeForCurrentLocation($query)
    {
        $locationId = currentLocationId();

        if ($locationId) {
            return $query->where('location_id', $locationId);
        }

        return $query;
    }

    /**
     * Scope to get records for current business and location
     */
    public function scopeForCurrentContext($query)
    {
        return $query
            ->forCurrentBusiness()
            ->forCurrentLocation();
    }

    /**
     * Boot the trait - automatically set business and location on create
     */
    public static function bootBelongsToBusinessContext()
    {
        static::creating(function ($model) {
            if (empty($model->business_id)) {
                $model->business_id = currentBusinessId();
            }

            if (empty($model->location_id) && $model->hasColumn('location_id')) {
                $model->location_id = currentLocationId();
            }
        });
    }

    /**
     * Check if model has a location_id column
     */
    protected function hasColumn($column)
    {
        return in_array($column, $this->getConnection()
            ->getSchemaBuilder()
            ->getColumns($this->getTable()));
    }
}
