<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return Inertia::render('Welcome', $this->getStorefrontData());
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

        return Inertia::render('Shop', $this->getStorefrontData());
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
                ];
            });

        $systemSetting = \Illuminate\Support\Facades\DB::table('system')->where('key', 'payment_gateways')->first();
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

        $announcementSetting = \Illuminate\Support\Facades\DB::table('system')->where('key', 'announcement_bar')->first();
        $announcement = null;
        if ($announcementSetting) {
            $announcement = json_decode($announcementSetting->value, true);
        }

        if (!$announcement) {
            $announcement = [
                'enabled' => true,
                'text' => '✨ GRAND OPENING OFFER: USE COUPON {coupon} FOR 50% OFF ALL PRODUCTS!',
                'coupon' => 'MINT50',
                'bg_color' => '#059669',
                'text_color' => '#ffffff',
            ];
        }

        // Hero slides (admin-managed) stored in `system` table under key 'hero_slides'
        $heroSetting = \Illuminate\Support\Facades\DB::table('system')->where('key', 'hero_slides')->first();
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
        if ('N;' === $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        $lastc = substr($data, -1);
        if (';' !== $lastc && '}' !== $lastc) {
            return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ('"' !== substr($data, -2, 1)) {
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

    /**
     * Place a storefront order and save it to the database.
     */
    public function placeOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $businessId = (int) ($request->input('business_id') ?: session('storefront_business_id') ?: config('ecommerce.business_id', 1));
        $locationId = (int) ($request->input('location_id') ?: session('storefront_location_id') ?: config('ecommerce.location_id', 1));

        $business = DB::table('business')->where('id', $businessId)->first();
        $ownerId = $business ? $business->owner_id : 1;

        $customer = $request->input('customer', []);
        $items    = $request->input('items', []);
        $subtotal = (float) $request->input('subtotal', 0);
        $discount = (float) $request->input('discount', 0);
        $couponCode = $request->input('couponCode');
        $shipping   = (float) $request->input('shipping', 0);
        $grandTotal = (float) $request->input('grandTotal', 0);

        $paymentMethod = $customer['paymentMethod'] ?? 'cod';
        $paymentStatus = $paymentMethod === 'cod' ? 'due' : 'paid';
        $orderStatus   = $paymentMethod === 'cod' ? 'ordered' : 'completed';

        $invNo = 'INV-2026-' . rand(1000, 9999);
        $refNo = 'ORD-' . rand(100000, 999999);

        $contactId = null;
        $userId    = $user ? $user->id : $ownerId;

        if ($user) {
            $contactId = DB::table('user_contact_access')
                ->where('user_id', $user->id)
                ->value('contact_id');
        }

        if (!$contactId) {
            $email = $customer['email'] ?? ($user ? $user->email : 'guest@storemint.com');

            // 1. Try to find an existing contact
            $existingContact = DB::table('contacts')
                ->where('business_id', $businessId)
                ->where('email', $email)
                ->first();

            if ($existingContact) {
                $contactId = $existingContact->id;

                // If a user already exists for this contact, use them as created_by
                if (!$user) {
                    $linkedUserId = DB::table('user_contact_access')
                        ->where('contact_id', $contactId)
                        ->value('user_id');
                    if ($linkedUserId) {
                        $userId = $linkedUserId;
                    }
                }
            } else {
                // 2. Brand-new guest – build name parts
                $contactName = $customer['name'] ?? ($user ? trim($user->first_name . ' ' . $user->last_name) : 'Guest Customer');
                $parts     = explode(' ', $contactName, 2);
                $firstName = $parts[0] ?? 'Guest';
                $lastName  = $parts[1] ?? 'Customer';

                // 2a. Create the contact record
                $contactId = DB::table('contacts')->insertGetId([
                    'business_id' => $businessId,
                    'type'        => 'customer',
                    'first_name'  => $firstName,
                    'last_name'   => $lastName,
                    'name'        => $contactName,
                    'email'       => $email,
                    'mobile'      => $customer['phone'] ?? null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                // 2b. Create a customer User account if this is a guest checkout
                if (!$user) {
                    // Derive a unique username from the email local-part
                    $baseUsername = strtolower(preg_replace('/[^a-z0-9]/i', '', explode('@', $email)[0]));
                    $username     = $baseUsername;
                    $suffix       = 1;
                    while (DB::table('users')->where('username', $username)->exists()) {
                        $username = $baseUsername . $suffix++;
                    }

                    // Resolve the store team id (first non-personal team)
                    $storeTeamId = DB::table('teams')
                        ->where('is_personal', false)
                        ->orderBy('id')
                        ->value('id');

                    $newUserId = DB::table('users')->insertGetId([
                        'first_name'      => $firstName,
                        'last_name'       => $lastName,
                        'username'        => $username,
                        'email'           => $email,
                        'password'        => \Illuminate\Support\Facades\Hash::make('password'),
                        'user_type'       => 'customer',
                        'business_id'     => $businessId,
                        'current_team_id' => $storeTeamId,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    // Add to store team as member
                    if ($storeTeamId) {
                        DB::table('team_members')->insertOrIgnore([
                            'team_id'    => $storeTeamId,
                            'user_id'    => $newUserId,
                            'role'       => 'member',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $userId = $newUserId;
                }
            }

            // 3. Link contact <-> user in user_contact_access
            $ownerUserId = $user ? $user->id : $userId;
            DB::table('user_contact_access')->insertOrIgnore([
                'user_id'    => $ownerUserId,
                'contact_id' => $contactId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $couponId = null;
        if ($couponCode) {
            $couponId = DB::table('coupons')
                ->where('business_id', $businessId)
                ->where('code', $couponCode)
                ->value('id');
        }
        
        $transId = DB::table('transactions')->insertGetId([
            'business_id' => $businessId,
            'location_id' => $locationId,
            'contact_id' => $contactId,
            'created_by' => $userId,
            'type' => 'sales_order',
            'status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'invoice_no' => $invNo,
            'ref_no' => $refNo,
            'transaction_date' => now(),
            'total_before_tax' => $subtotal,
            'discount_type' => $couponId ? 'fixed' : null,
            'discount_amount' => $discount,
            'coupon_id' => $couponId,
            'shipping_charges' => $shipping,
            'shipping_address' => trim(($customer['address'] ?? '') . ', ' . ($customer['city'] ?? '') . ', ' . ($customer['zip'] ?? '')),
            'final_total' => $grandTotal,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if ($paymentStatus === 'paid') {
            $paymentType = \App\Models\TransactionPayment::determinePaymentType($grandTotal);
            DB::table('transaction_payments')->insert([
                'transaction_id' => $transId,
                'business_id' => $businessId,
                'amount' => $grandTotal,
                'method' => 'online',
                'payment_type' => $paymentType,
                'gateway' => $paymentMethod,
                'paid_on' => now(),
                'created_by' => $userId,
                'status' => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? $item['product']['id'] ?? null;
            $qty = $item['quantity'] ?? $item['qty'] ?? 1;
            $price = $item['price'] ?? $item['product']['price'] ?? 0;
            
            if ($productId) {
                $variationId = DB::table('variations')->where('product_id', $productId)->value('id');
                
                if ($variationId) {
                    DB::table('transaction_sell_lines')->insert([
                        'transaction_id' => $transId,
                        'product_id' => $productId,
                        'variation_id' => $variationId,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'unit_price_before_discount' => $price,
                        'unit_price_inc_tax' => $price,
                        'item_tax' => 0.00,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    DB::table('variation_location_details')
                        ->where('product_id', $productId)
                        ->where('variation_id', $variationId)
                        ->where('location_id', $locationId)
                        ->decrement('qty_available', $qty);
                }
            }
        }
        
        if ($couponId) {
            DB::table('coupon_usages')->insert([
                'coupon_id' => $couponId,
                'user_id' => $userId,
                'transaction_id' => $transId,
                'discount_applied' => $discount,
                'created_at' => now(),
            ]);
            
            // Increment coupon usage count
            DB::table('coupons')->where('id', $couponId)->increment('used_count');
        }
        
        \App\Models\Transaction::checkAndGenerateSell($transId);
        
        return response()->json([
            'success' => true,
            'invoice' => [
                'invoiceNo' => $invNo,
                'orderNo' => $refNo,
                'date' => now()->format('F d, Y'),
                'paymentMethod' => $paymentMethod === 'stripe' ? 'Stripe (Credit Card)' : 
                                   ($paymentMethod === 'sslcommerz' ? 'SSLCommerz (Local Card/Mobile Banking)' : 'Cash on Delivery (COD)'),
                'paymentStatus' => $paymentStatus === 'paid' ? 'Paid' : 'Pending',
                'customer' => $customer,
                'items' => array_map(function ($item) {
                    return [
                        'name' => $item['product']['name'] ?? 'Product',
                        'price' => $item['product']['price'] ?? 0,
                        'quantity' => $item['quantity'] ?? 1,
                        'total' => ($item['product']['price'] ?? 0) * ($item['quantity'] ?? 1),
                    ];
                }, $items),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'couponCode' => $couponCode,
                'shipping' => $shipping,
                'grandTotal' => $grandTotal,
            ]
        ]);
    }
}
