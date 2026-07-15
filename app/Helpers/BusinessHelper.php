<?php

/**
 * Global helper functions for business and location selection
 *
 * These functions provide convenient access to the BusinessContextService
 * for managing business and location context throughout the application.
 */

use App\Models\Business;
use App\Models\BusinessLocation;
use App\Services\BusinessContextService;
use Illuminate\Database\Eloquent\Collection;

if (! function_exists('currentBusiness')) {
    /**
     * Get the currently selected business
     */
    function currentBusiness(): ?Business
    {
        return BusinessContextService::getCurrentBusiness();
    }
}

if (! function_exists('currentBusinessId')) {
    /**
     * Get the currently selected business ID
     */
    function currentBusinessId(): ?int
    {
        return BusinessContextService::getCurrentBusinessId();
    }
}

if (! function_exists('setCurrentBusiness')) {
    /**
     * Set the current business in session
     */
    function setCurrentBusiness(Business|int $business): Business
    {
        return BusinessContextService::setCurrentBusiness($business);
    }
}

if (! function_exists('currentLocation')) {
    /**
     * Get the currently selected business location
     */
    function currentLocation(): ?BusinessLocation
    {
        return BusinessContextService::getCurrentLocation();
    }
}

if (! function_exists('currentLocationId')) {
    /**
     * Get the currently selected business location ID
     */
    function currentLocationId(): ?int
    {
        return BusinessContextService::getCurrentLocationId();
    }
}

if (! function_exists('setCurrentLocation')) {
    /**
     * Set the current business location in session
     * Must belong to the currently selected business
     */
    function setCurrentLocation(BusinessLocation|int $location): BusinessLocation
    {
        return BusinessContextService::setCurrentLocation($location);
    }
}

if (! function_exists('availableBusinesses')) {
    /**
     * Get all businesses for the current user
     *
     * @return Collection
     */
    function availableBusinesses()
    {
        return BusinessContextService::getAvailableBusinesses();
    }
}

if (! function_exists('availableLocations')) {
    /**
     * Get all locations for the currently selected business
     *
     * @return Collection
     */
    function availableLocations()
    {
        return BusinessContextService::getAvailableLocations();
    }
}

if (! function_exists('hasCurrentBusiness')) {
    /**
     * Check if business is currently selected
     */
    function hasCurrentBusiness(): bool
    {
        return BusinessContextService::hasCurrentBusiness();
    }
}

if (! function_exists('hasCurrentLocation')) {
    /**
     * Check if location is currently selected
     */
    function hasCurrentLocation(): bool
    {
        return BusinessContextService::hasCurrentLocation();
    }
}

if (! function_exists('clearBusinessContext')) {
    /**
     * Clear all business context (business and location)
     */
    function clearBusinessContext(): void
    {
        BusinessContextService::clearContext();
    }
}

if (! function_exists('businessContext')) {
    /**
     * Get full business context data for use in views/API responses
     */
    function businessContext(): array
    {
        return BusinessContextService::getContextData();
    }
}
