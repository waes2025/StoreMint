<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ecommerce Storefront Context Configuration
    |--------------------------------------------------------------------------
    |
    | This file controls the active business_id and location_id context used
    | for the storefront ecommerce operations (loading products, categories,
    | brands, coupons, and placing orders).
    |
    */

    'business_id' => (int) env('ECOM_BUSINESS_ID', 1),
    'location_id' => (int) env('ECOM_LOCATION_ID', 1),
];
