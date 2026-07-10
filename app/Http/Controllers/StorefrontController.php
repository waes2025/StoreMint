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
                $qty = $product->currentStock();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => (float) $product->price,
                    'compare_at_price' => $product->compare_at_price ? (float) $product->compare_at_price : null,
                    'stock_status' => $qty > 0 ? 'in_stock' : 'out_of_stock',
                    'stock' => $qty,
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

        $brands = Brand::pluck('name')->toArray();
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

        return [
            'dbProducts' => $products,
            'dbCategories' => $categories,
            'dbBrands' => $brands,
            'dbCoupons' => $coupons,
            'gateways' => $gateways,
            'announcement' => $announcement,
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
        
        $customer = $request->input('customer', []);
        $items = $request->input('items', []);
        $subtotal = (float) $request->input('subtotal', 0);
        $discount = (float) $request->input('discount', 0);
        $couponCode = $request->input('couponCode');
        $shipping = (float) $request->input('shipping', 0);
        $grandTotal = (float) $request->input('grandTotal', 0);
        
        $paymentMethod = $customer['paymentMethod'] ?? 'cod';
        $paymentStatus = $paymentMethod === 'cod' ? 'due' : 'paid';
        $orderStatus = $paymentMethod === 'cod' ? 'ordered' : 'completed';
        
        $invNo = 'INV-2026-' . rand(1000, 9999);
        $refNo = 'ORD-' . rand(100000, 999999);
        
        $contactId = null;
        $userId = $user ? $user->id : 1; 
        
        if ($user) {
            $contactId = DB::table('user_contact_access')
                ->where('user_id', $user->id)
                ->value('contact_id');
        }
        
        if (!$contactId) {
            $email = $customer['email'] ?? ($user ? $user->email : 'guest@storemint.com');
            $contact = DB::table('contacts')
                ->where('email', $email)
                ->first();
                
            if ($contact) {
                $contactId = $contact->id;
            } else {
                $contactName = $customer['name'] ?? ($user ? trim($user->first_name . ' ' . $user->last_name) : 'Guest Customer');
                $parts = explode(' ', $contactName, 2);
                $firstName = $parts[0] ?? 'Guest';
                $lastName = $parts[1] ?? 'Customer';
                
                $contactId = DB::table('contacts')->insertGetId([
                    'business_id' => 1,
                    'type' => 'customer',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => $contactName,
                    'email' => $email,
                    'mobile' => $customer['phone'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            if ($user) {
                DB::table('user_contact_access')->insertOrIgnore([
                    'user_id' => $user->id,
                    'contact_id' => $contactId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        $couponId = null;
        if ($couponCode) {
            $couponId = DB::table('coupons')->where('code', $couponCode)->value('id');
        }
        
        $transId = DB::table('transactions')->insertGetId([
            'business_id' => 1,
            'location_id' => 1,
            'contact_id' => $contactId,
            'created_by' => $userId,
            'type' => 'sell',
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
                'business_id' => 1,
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
                        ->decrement('qty_available', $qty);
                        
                    // Retrieve dynamic stock using the product model instance
                    $prodModel = Product::find($productId);
                    $totalStock = $prodModel ? $prodModel->currentStock() : 0;
                        
                    if ($totalStock <= 0) {
                        DB::table('products')
                            ->where('id', $productId)
                            ->update(['stock_status' => 'out_of_stock']);
                    }
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
