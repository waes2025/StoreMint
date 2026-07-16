<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Modules\Cart\Models\Coupon;
use App\Models\Product;
use Modules\Cart\Models\Transaction;
use Modules\Cart\Models\TransactionPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    /**
     * Display the storefront welcome homepage.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user() && $request->user()->isAdmin()) {
            return redirect()->route('customer.dashboard');
        }

        if ($request->has('business_id')) {
            session(['storefront_business_id' => (int) $request->input('business_id')]);
        }
        if ($request->has('location_id')) {
            session(['storefront_location_id' => (int) $request->input('location_id')]);
        }

        return Inertia::render('Shop::Welcome', $this->getStorefrontData());
    }

    /**
     * Display the storefront shop page.
     */
    public function shop(Request $request): Response|RedirectResponse
    {
        if ($request->user() && $request->user()->isAdmin()) {
            return redirect()->route('customer.dashboard');
        }

        if ($request->has('business_id')) {
            session(['storefront_business_id' => (int) $request->input('business_id')]);
        }
        if ($request->has('location_id')) {
            session(['storefront_location_id' => (int) $request->input('location_id')]);
        }

        return Inertia::render('Shop::Shop', $this->getStorefrontData());
    }

    /**
     * Get storefront data shared across public pages.
     */
    private function getStorefrontData(): array
    {
        $businessId = (int) (request()->input('business_id') ?: session('storefront_business_id') ?: config('ecommerce.business_id', 1));
        $locationId = (int) (request()->input('location_id') ?: session('storefront_location_id') ?: config('ecommerce.location_id', 1));

        $products = Product::with(['category', 'brand', 'variations'])
            ->where('business_id', $businessId)
            ->where('is_allow_ecom', 1)
            ->get()
            ->map(function ($product) use ($locationId) {
                $qty = $product->currentStock($locationId);
                $variation = $product->variations->first();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $variation ? $variation->slug : null,
                    'price' => $variation ? (float) $variation->default_sell_price : 0.0,
                    'compare_at_price' => ($variation && $variation->compare_at_price) ? (float) $variation->compare_at_price : null,
                    'stock_status' => $qty > 0 ? 'in_stock' : 'out_of_stock',
                    'stock' => $qty,
                    'image' => $product->image,
                    'short_description' => $variation ? $variation->short_description : null,
                    'description' => $variation ? $variation->description : null,
                    'is_featured' => (bool) $product->is_featured,
                    'is_best_seller' => $variation ? (bool) $variation->is_best_seller : false,
                    'category' => $product->category ? $product->category->name : 'Electronics',
                    'brand' => $product->brand ? $product->brand->name : null,
                ];
            });

        $categories = Category::where('business_id', $businessId)
            ->where('is_allow_ecom', 1)
            ->pluck('name')
            ->toArray();

        // Ensure "All" is not saved in DB but is returned for frontend tabs
        array_unshift($categories, 'All');

        $brands = Brand::where('business_id', $businessId)
            ->pluck('name')
            ->toArray();
        array_unshift($brands, 'All');

        $coupons = Coupon::where('business_id', $businessId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get()
            ->map(function ($coupon) {
                return [
                    'code' => $coupon->code,
                    'description' => $coupon->description,
                    'discountType' => $coupon->discount_type, // Map flat/percentage
                    'discountValue' => (float) $coupon->discount_value,
                    'minOrderAmount' => (float) $coupon->min_order_amount,
                    'maxDiscountAmount' => $coupon->max_discount_amount ? (float) $coupon->max_discount_amount : null,
                    'usageLimitPerUser' => $coupon->usage_limit_per_user,
                ];
            });

        $systemSetting = DB::table('settings')
            ->where('business_id', $businessId)
            ->where('key', 'payment_gateways')
            ->first();
        $savedGateways = [];
        if ($systemSetting) {
            $val = $systemSetting->value;
            if ($this->isSerialized($val)) {
                $savedGateways = @unserialize($val);
            } else {
                $savedGateways = json_decode($val, true);
            }
        }

        $defaultGateways = [
            'stripe' => [
                'enabled' => false,
                'publishable_key' => '',
                'secret_key' => '',
            ],
            'sslcommerz' => [
                'enabled' => false,
                'store_id' => '',
                'store_password' => '',
                'merchant_id' => '',
                'mode' => 'live',
            ],
            'cod' => [
                'enabled' => true,
            ],
        ];

        $gateways = array_merge($defaultGateways, is_array($savedGateways) ? $savedGateways : []);

        $businessModel = \App\Models\Business::find($businessId);
        $enabledModules = $businessModel ? ($businessModel->enabled_modules ?? []) : [];
        if (! in_array('Gateway', $enabledModules)) {
            $gateways['stripe']['enabled'] = false;
            $gateways['sslcommerz']['enabled'] = false;
        }

        $announcementSetting = DB::table('settings')
            ->where('business_id', $businessId)
            ->where('key', 'announcement_bar')
            ->first();
        $announcement = null;
        if ($announcementSetting) {
            $announcement = json_decode($announcementSetting->value, true);
        }

        if (! $announcement) {
            $announcement = [
                'enabled' => false,
                'text' => '',
                'coupon' => '',
                'bg_color' => '#059669',
                'text_color' => '#ffffff',
            ];
        }

        // Hero slides (admin-managed) stored in `settings` table under key 'hero_slides'
        $heroSetting = DB::table('settings')
            ->where('business_id', $businessId)
            ->where('key', 'hero_slides')
            ->first();
        $heroSlides = [];
        if ($heroSetting) {
            $heroSlides = json_decode($heroSetting->value, true) ?: [];
        }

        return [
            'dbProducts' => $products,
            'dbCategories' => $categories,
            'dbBrands' => $brands,
            'dbCoupons' => $coupons,
            'gateways' => $gateways,
            'announcement' => $announcement,
            'heroSlides' => $heroSlides,
        ];
    }

    /**
     * Check if a value is serialized PHP data.
     */
    private function isSerialized($value): bool
    {
        if (! is_string($value)) {
            return false;
        }
        $data = trim($value);
        if ($data === 'N;') {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if ($data[1] !== ':') {
            return false;
        }
        $lastc = substr($data, -1);
        if ($lastc !== ';' && $lastc !== '}') {
            return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if (substr($data, -2, 1) !== '"') {
                    return false;
                }
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                return (bool) preg_match("/^{$token}:[0-9.E-]+;/s", $data);
        }

        return false;
    }
}
