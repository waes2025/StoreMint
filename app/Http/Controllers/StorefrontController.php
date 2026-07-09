<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Coupon;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Display the storefront welcome homepage.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Welcome', $this->getStorefrontData());
    }

    /**
     * Display the storefront shop page.
     */
    public function shop(Request $request): Response
    {
        return Inertia::render('Shop', $this->getStorefrontData());
    }

    /**
     * Get storefront data shared across public pages.
     */
    private function getStorefrontData(): array
    {
        $products = Product::with(['category', 'brand'])
            ->where('is_active', 1)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => (float) $product->price,
                    'compare_at_price' => $product->compare_at_price ? (float) $product->compare_at_price : null,
                    'stock_status' => $product->stock_status,
                    'stock' => $product->stock_status === 'in_stock' ? 10 : 0, // Fallback stock qty
                    'image' => $product->image,
                    'short_description' => $product->short_description,
                    'description' => $product->description,
                    'is_featured' => (bool) $product->is_featured,
                    'is_best_seller' => (bool) $product->is_best_seller,
                    'category' => $product->category ? $product->category->name : 'Electronics',
                    'brand' => $product->brand ? $product->brand->name : null,
                ];
            });

        $categories = Category::where('is_active', 1)
            ->pluck('name')
            ->toArray();

        // Ensure "All" is not saved in DB but is returned for frontend tabs
        array_unshift($categories, 'All');

        $brands = \App\Models\Brand::pluck('name')->toArray();
        array_unshift($brands, 'All');

        $coupons = Coupon::where('status', 'active')
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
                ];
            });

        return [
            'dbProducts' => $products,
            'dbCategories' => $categories,
            'dbBrands' => $brands,
            'dbCoupons' => $coupons,
        ];
    }
}
