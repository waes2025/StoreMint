<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Modules\Cart\Models\Coupon;
use App\Models\Product;
use Modules\Cart\Models\Transaction;
use Modules\Cart\Models\TransactionPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CheckoutController extends Controller
{
    /**
     * Place a storefront order and save it to the database.
     */
    public function placeOrder(Request $request): JsonResponse
    {
        $user = $request->user();

        $businessId = (int) ($request->input('business_id') ?: session('storefront_business_id') ?: config('ecommerce.business_id', 1));
        $locationId = (int) ($request->input('location_id') ?: session('storefront_location_id') ?: config('ecommerce.location_id', 1));

        $business = DB::table('business')->where('id', $businessId)->first();
        $ownerId = $business ? $business->owner_id : 1;

        $customer = $request->input('customer', []);
        $items = $request->input('items', []);
        $subtotal = (float) $request->input('subtotal', 0);
        $discount = (float) $request->input('discount', 0);
        $couponCode = $request->input('couponCode');
        $shipping = (float) $request->input('shipping', 0);
        $grandTotal = (float) $request->input('grandTotal', 0);

        $enabledModules = $business ? json_decode($business->enabled_modules ?? '[]', true) : [];
        if (! in_array('Shipment', $enabledModules)) {
            $shipping = 0.0;
            $grandTotal = max(0.0, $subtotal - $discount);
        }

        $paymentMethod = $customer['paymentMethod'] ?? 'cod';

        if (in_array($paymentMethod, ['stripe', 'sslcommerz'])) {
            if (! in_array('Gateway', $enabledModules)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Online payment gateways are currently disabled.',
                ], 400);
            }
        }

        $paymentStatus = $paymentMethod === 'cod' ? 'due' : 'paid';
        $orderStatus = $paymentMethod === 'cod' ? 'ordered' : 'completed';

        $invNo = 'INV-2026-'.rand(1000, 9999);
        $refNo = 'ORD-'.rand(100000, 999999);

        $contactId = null;
        $userId = $user ? $user->id : $ownerId;

        if ($user) {
            $contactId = DB::table('user_contact_access')
                ->where('user_id', $user->id)
                ->value('contact_id');
        }

        if (! $contactId) {
            $email = $customer['email'] ?? ($user ? $user->email : 'guest@storemint.com');

            // 1. Try to find an existing contact
            $existingContact = DB::table('contacts')
                ->where('business_id', $businessId)
                ->where('email', $email)
                ->first();

            if ($existingContact) {
                $contactId = $existingContact->id;

                // If a user already exists for this contact, use them as created_by
                if (! $user) {
                    $linkedUserId = DB::table('user_contact_access')
                        ->where('contact_id', $contactId)
                        ->value('user_id');
                    if ($linkedUserId) {
                        $userId = $linkedUserId;
                    }
                }
            } else {
                // 2. Brand-new guest – build name parts
                $contactName = $customer['name'] ?? ($user ? trim($user->first_name.' '.$user->last_name) : 'Guest Customer');
                $parts = explode(' ', $contactName, 2);
                $firstName = $parts[0] ?? 'Guest';
                $lastName = $parts[1] ?? 'Customer';

                // 2a. Create the contact record
                $contactId = DB::table('contacts')->insertGetId([
                    'business_id' => $businessId,
                    'type' => 'customer',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => $contactName,
                    'email' => $email,
                    'mobile' => $customer['phone'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2b. Create a customer User account if this is a guest checkout
                if (! $user) {
                    // Derive a unique username from the email local-part
                    $baseUsername = strtolower(preg_replace('/[^a-z0-9]/i', '', explode('@', $email)[0]));
                    $username = $baseUsername;
                    $suffix = 1;
                    while (DB::table('users')->where('username', $username)->exists()) {
                        $username = $baseUsername.$suffix++;
                    }

                    // Resolve the store team id (first non-personal team)
                    $storeTeamId = DB::table('teams')
                        ->where('is_personal', false)
                        ->orderBy('id')
                        ->value('id');

                    $newUserId = DB::table('users')->insertGetId([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'username' => $username,
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'user_type' => 'customer',
                        'business_id' => $businessId,
                        'current_team_id' => $storeTeamId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Add to store team as member
                    if ($storeTeamId) {
                        DB::table('team_members')->insertOrIgnore([
                            'team_id' => $storeTeamId,
                            'user_id' => $newUserId,
                            'role' => 'member',
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
                'user_id' => $ownerUserId,
                'contact_id' => $contactId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $couponId = null;
        if ($couponCode) {
            $coupon = DB::table('coupons')
                ->where('business_id', $businessId)
                ->where('code', $couponCode)
                ->first();

            if (! $coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'The coupon code is invalid.',
                ]);
            }

            if ($coupon->status !== 'active' || ($coupon->expires_at && \Carbon\Carbon::parse($coupon->expires_at)->isPast())) {
                return response()->json([
                    'success' => false,
                    'message' => 'The coupon code has expired.',
                ]);
            }

            if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'This coupon usage limit has been reached.',
                ]);
            }

            if ($coupon->usage_limit_per_user !== null) {
                $userUsages = DB::table('coupon_usages')
                    ->where('coupon_id', $coupon->id)
                    ->where('user_id', $userId)
                    ->count();

                if ($userUsages >= $coupon->usage_limit_per_user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have reached the usage limit per customer for this coupon.',
                    ]);
                }
            }

            $couponId = $coupon->id;
        }

        $transId = DB::table('transactions')->insertGetId([
            'business_id' => $businessId,
            'location_id' => $locationId,
            'contact_id' => $contactId,
            'created_by' => $userId,
            'type' => 'sales_order',
            'status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'shipping_status' => 'ordered',
            'invoice_no' => $invNo,
            'ref_no' => $refNo,
            'transaction_date' => now(),
            'total_before_tax' => $subtotal,
            'discount_type' => $couponId ? 'fixed' : null,
            'discount_amount' => $discount,
            'coupon_id' => $couponId,
            'shipping_charges' => $shipping,
            'shipping_address' => trim(($customer['address'] ?? '').', '.($customer['city'] ?? '').', '.($customer['zip'] ?? '')),
            'final_total' => $grandTotal,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($paymentStatus === 'paid') {
            $paymentType = TransactionPayment::determinePaymentType($grandTotal);
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

        Transaction::checkAndGenerateSell($transId);

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
            ],
        ]);
    }
}
