<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessLocation;
use Illuminate\Support\Facades\Session;

/**
 * Global service for managing business and business location selection context
 */
class BusinessContextService
{
    const CURRENT_BUSINESS_KEY = 'current_business_id';
    const CURRENT_LOCATION_KEY = 'current_business_location_id';

    /**
     * Get the currently selected business
     */
    public static function getCurrentBusiness(): ?Business
    {
        $businessId = Session::get(self::CURRENT_BUSINESS_KEY);

        if (!$businessId) {
            return null;
        }

        return Business::find($businessId);
    }

    /**
     * Get the currently selected business ID
     */
    public static function getCurrentBusinessId(): ?int
    {
        return Session::get(self::CURRENT_BUSINESS_KEY);
    }

    /**
     * Set the current business in session
     */
    public static function setCurrentBusiness(Business|int $business): Business
    {
        $businessId = $business instanceof Business ? $business->id : $business;
        $business = Business::findOrFail($businessId);

        Session::put(self::CURRENT_BUSINESS_KEY, $business->id);

        // Clear location if set, as it must belong to new business
        Session::forget(self::CURRENT_LOCATION_KEY);

        return $business;
    }

    /**
     * Get the currently selected business location
     */
    public static function getCurrentLocation(): ?BusinessLocation
    {
        $locationId = Session::get(self::CURRENT_LOCATION_KEY);

        if (!$locationId) {
            return null;
        }

        return BusinessLocation::find($locationId);
    }

    /**
     * Get the currently selected business location ID
     */
    public static function getCurrentLocationId(): ?int
    {
        return Session::get(self::CURRENT_LOCATION_KEY);
    }

    /**
     * Set the current business location in session
     * Must belong to the currently selected business
     */
    public static function setCurrentLocation(BusinessLocation|int $location): BusinessLocation
    {
        $locationId = $location instanceof BusinessLocation ? $location->id : $location;
        $location = BusinessLocation::findOrFail($locationId);

        $currentBusinessId = self::getCurrentBusinessId();

        if ($currentBusinessId && $location->business_id !== $currentBusinessId) {
            throw new \Exception("Location does not belong to current business");
        }

        Session::put(self::CURRENT_LOCATION_KEY, $location->id);

        return $location;
    }

    /**
     * Get all businesses for the current user
     */
    public static function getAvailableBusinesses(): \Illuminate\Database\Eloquent\Collection
    {
        $user = auth()->user();

        if (!$user) {
            return collect();
        }

        // If user is owner of businesses, return those
        $businesses = Business::where('owner_id', $user->id)->get();

        if ($businesses->isNotEmpty()) {
            return $businesses;
        }

        // Otherwise return empty collection
        return collect();
    }

    /**
     * Get all locations for the currently selected business
     */
    public static function getAvailableLocations(): \Illuminate\Database\Eloquent\Collection
    {
        $businessId = self::getCurrentBusinessId();

        if (!$businessId) {
            return collect();
        }

        return BusinessLocation::where('business_id', $businessId)->get();
    }

    /**
     * Check if business is currently selected
     */
    public static function hasCurrentBusiness(): bool
    {
        return Session::has(self::CURRENT_BUSINESS_KEY);
    }

    /**
     * Check if location is currently selected
     */
    public static function hasCurrentLocation(): bool
    {
        return Session::has(self::CURRENT_LOCATION_KEY);
    }

    /**
     * Clear all business context
     */
    public static function clearContext(): void
    {
        Session::forget([self::CURRENT_BUSINESS_KEY, self::CURRENT_LOCATION_KEY]);
    }

    /**
     * Get context data for use in views/API responses
     */
    public static function getContextData(): array
    {
        return [
            'business' => self::getCurrentBusiness(),
            'business_id' => self::getCurrentBusinessId(),
            'location' => self::getCurrentLocation(),
            'location_id' => self::getCurrentLocationId(),
            'available_businesses' => self::getAvailableBusinesses(),
            'available_locations' => self::getAvailableLocations(),
        ];
    }
}
