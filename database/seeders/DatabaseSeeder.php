<?php

namespace Database\Seeders;

use App\Models\TransactionPayment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks for clean truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables
        DB::table('coupon_usages')->truncate();
        DB::table('coupons')->truncate();
        DB::table('variation_location_details')->truncate();
        DB::table('variations')->truncate();
        DB::table('product_variations')->truncate();
        DB::table('products')->truncate();
        DB::table('brands')->truncate();
        DB::table('categories')->truncate();
        DB::table('business_locations')->truncate();
        DB::table('business')->truncate();
        DB::table('users')->truncate();
        DB::table('currencies')->truncate();
        DB::table('transaction_sell_lines')->truncate();
        DB::table('purchase_lines')->truncate();
        DB::table('transaction_payments')->truncate();
        DB::table('transactions')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->call([
            PermissionsTableSeeder::class,
            CurrenciesTableSeeder::class,
        ]);

        $currencyId = DB::table('currencies')->where('code', 'USD')->value('id') ?? 2;

        // 2. Insert Default User (Owner)
        $userId = DB::table('users')->insertGetId([
            'first_name' => 'Waes',
            'last_name' => 'Ahmed',
            'username' => 'waes',
            'email' => 'waes@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Insert Business
        $businessId = DB::table('business')->insertGetId([
            'id' => 1,
            'name' => 'StoreMint E-Commerce',
            'currency_id' => $currencyId,
            'start_date' => '2026-01-01',
            'owner_id' => $userId,
            'time_zone' => 'Asia/Dhaka',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Link user to business
        DB::table('users')->where('id', $userId)->update(['business_id' => $businessId]);

        // 4. Insert Business Location
        $locationId = DB::table('business_locations')->insertGetId([
            'business_id' => $businessId,
            'location_id' => 'LOC001',
            'name' => 'Main E-Commerce Warehouse',
            'country' => 'United States',
            'state' => 'New York',
            'city' => 'New York',
            'zip_code' => '10001',
            'invoice_scheme_id' => 1,
            'invoice_layout_id' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4b. Seed Customer Users and Contacts
        $customersData = [
            [
                'first_name' => 'Sarah',
                'last_name' => 'Connor',
                'email' => 'sarah@example.com',
                'username' => 'sarah',
                'mobile' => '+1 (555) 019-2831',
            ],
            [
                'first_name' => 'Bruce',
                'last_name' => 'Wayne',
                'email' => 'bruce@example.com',
                'username' => 'bruce',
                'mobile' => '+1 (555) 911-3829',
            ],
            [
                'first_name' => 'Clark',
                'last_name' => 'Kent',
                'email' => 'clark@example.com',
                'username' => 'clark',
                'mobile' => '+1 (555) 777-8822',
            ],
        ];

        $customerMap = [];

        foreach ($customersData as $c) {
            $cUserId = DB::table('users')->insertGetId([
                'first_name' => $c['first_name'],
                'last_name' => $c['last_name'],
                'username' => $c['username'],
                'email' => $c['email'],
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'business_id' => $businessId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create corresponding contact
            $contactId = DB::table('contacts')->insertGetId([
                'business_id' => $businessId,
                'type' => 'customer',
                'first_name' => $c['first_name'],
                'last_name' => $c['last_name'],
                'name' => $c['first_name'].' '.$c['last_name'],
                'email' => $c['email'],
                'mobile' => $c['mobile'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Link in user_contact_access
            DB::table('user_contact_access')->insert([
                'user_id' => $cUserId,
                'contact_id' => $contactId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $customerMap[$c['first_name'].' '.$c['last_name']] = [
                'user_id' => $cUserId,
                'contact_id' => $contactId,
            ];
        }

        // 5. Insert Categories
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Tech & Gadgets',
            'business_id' => $businessId,
            'short_code' => 'TECH',
            'created_by' => $userId,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5b. Insert Brands
        $brandsDataList = [
            ['name' => 'Apex', 'business_id' => $businessId, 'created_by' => $userId, 'slug' => 'apex', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aero', 'business_id' => $businessId, 'created_by' => $userId, 'slug' => 'aero', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sleek', 'business_id' => $businessId, 'created_by' => $userId, 'slug' => 'sleek', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ergo', 'business_id' => $businessId, 'created_by' => $userId, 'slug' => 'ergo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ember', 'business_id' => $businessId, 'created_by' => $userId, 'slug' => 'ember', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aura', 'business_id' => $businessId, 'created_by' => $userId, 'slug' => 'aura', 'created_at' => now(), 'updated_at' => now()],
        ];
        $insertedBrands = [];
        foreach ($brandsDataList as $b) {
            $insertedBrands[$b['name']] = DB::table('brands')->insertGetId($b);
        }

        // 6. Insert Products (matching storefront list)
        $productsData = [
            [
                'name' => 'Quantum Chronograph Watch',
                'slug' => 'quantum-chronograph-watch',
                'price' => 299.00,
                'compare_at_price' => 350.00,
                'stock' => 15,
                'short_description' => 'Precision engineered chronograph watch with sapphire dial.',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => true,
                'brand' => 'Apex',
            ],
            [
                'name' => 'AeroBuds Pro Wireless',
                'slug' => 'aerobuds-pro-wireless',
                'price' => 149.00,
                'compare_at_price' => 199.00,
                'stock' => 24,
                'short_description' => 'True wireless studio sound earbuds with Active Noise Cancelling.',
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => false,
                'brand' => 'Aero',
            ],
            [
                'name' => 'Minimalist Leather Backpack',
                'slug' => 'minimalist-leather-backpack',
                'price' => 89.00,
                'compare_at_price' => 120.00,
                'stock' => 8,
                'short_description' => 'Sleek top-grain calf leather laptop pack for clean utility.',
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=60',
                'is_featured' => false,
                'is_best_seller' => true,
                'brand' => 'Sleek',
            ],
            [
                'name' => 'Lumbar Comfort Office Chair',
                'slug' => 'lumbar-comfort-office-chair',
                'price' => 249.00,
                'compare_at_price' => 299.00,
                'stock' => 12,
                'short_description' => 'Ergonomic lumbar adaptive chair with high-breathability mesh.',
                'image' => 'https://images.unsplash.com/photo-1505843490538-5133c6c7d0e1?w=500&auto=format&fit=crop&q=60',
                'is_featured' => false,
                'is_best_seller' => false,
                'brand' => 'Ergo',
            ],
            [
                'name' => 'Ember Mug Smart Temperature',
                'slug' => 'ember-mug-smart-temperature',
                'price' => 129.00,
                'compare_at_price' => 149.00,
                'stock' => 0,
                'short_description' => 'App-controlled smart mug keeping your brew at the ideal temperature.',
                'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=500&auto=format&fit=crop&q=60',
                'is_featured' => false,
                'is_best_seller' => false,
                'brand' => 'Ember',
            ],
            [
                'name' => 'Aura Light Ring Lamp',
                'slug' => 'aura-light-ring-lamp',
                'price' => 59.00,
                'compare_at_price' => 79.00,
                'stock' => 30,
                'short_description' => 'Warm multi-intensity studio lighting for professional streams.',
                'image' => 'https://images.unsplash.com/photo-1507646227500-4d389b0012be?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => false,
                'brand' => 'Aura',
            ],
        ];

        // Create initial purchase transaction
        $initPurchaseId = DB::table('transactions')->insertGetId([
            'business_id' => $businessId,
            'location_id' => $locationId,
            'created_by' => $userId,
            'type' => 'purchase',
            'status' => 'received',
            'payment_status' => 'paid',
            'invoice_no' => 'INV-PUR-INIT',
            'ref_no' => 'PUR-INIT',
            'transaction_date' => now()->subDays(10),
            'total_before_tax' => 10000.00,
            'final_total' => 10000.00,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        foreach ($productsData as $index => $pData) {
            $sku = 'SKU-'.strtoupper(Str::random(6));
            $stockStatus = $pData['stock'] > 0 ? 'in_stock' : 'out_of_stock';

            // Insert Product
            $prodId = DB::table('products')->insertGetId([
                'name' => $pData['name'],
                'slug' => $pData['slug'],
                'business_id' => $businessId,
                'category_id' => $categoryId,
                'brand_id' => $insertedBrands[$pData['brand']] ?? null,
                'price' => $pData['price'],
                'compare_at_price' => $pData['compare_at_price'],
                'stock_status' => $stockStatus,
                'short_description' => $pData['short_description'],
                'image' => $pData['image'],
                'is_featured' => $pData['is_featured'],
                'is_best_seller' => $pData['is_best_seller'],
                'is_active' => true,
                'sku' => $sku,
                'type' => 'single',
                'enable_stock' => 1,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Product Variation Template
            $prodVarId = DB::table('product_variations')->insertGetId([
                'product_id' => $prodId,
                'name' => 'DUMMY_VAR',
                'is_dummy' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Variation
            $varId = DB::table('variations')->insertGetId([
                'name' => 'DUMMY_VAR',
                'product_id' => $prodId,
                'sub_sku' => $sku,
                'product_variation_id' => $prodVarId,
                'default_purchase_price' => $pData['price'] * 0.6,
                'dpp_inc_tax' => $pData['price'] * 0.6,
                'profit_percent' => 40.00,
                'default_sell_price' => $pData['price'],
                'sell_price_inc_tax' => $pData['price'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Seed stock at Main warehouse location
            DB::table('variation_location_details')->insert([
                'product_id' => $prodId,
                'product_variation_id' => $prodVarId,
                'variation_id' => $varId,
                'location_id' => $locationId,
                'qty_available' => $pData['stock'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert purchase line for initial stock
            DB::table('purchase_lines')->insert([
                'transaction_id' => $initPurchaseId,
                'product_id' => $prodId,
                'variation_id' => $varId,
                'quantity' => $pData['stock'],
                'purchase_price' => $pData['price'] * 0.6,
                'purchase_price_inc_tax' => $pData['price'] * 0.6,
                'item_tax' => 0.00,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ]);
        }

        // 7. Insert Coupons
        $coupon1 = DB::table('coupons')->insertGetId([
            'business_id' => $businessId,
            'code' => 'MINT50',
            'description' => '50% off storewide on orders over $50!',
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'max_discount_amount' => 100.00,
            'min_order_amount' => 50.00,
            'usage_limit' => 500,
            'usage_limit_per_user' => 1,
            'used_count' => 14,
            'starts_at' => now()->subDays(5),
            'expires_at' => now()->addDays(30),
            'status' => 'active',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $coupon2 = DB::table('coupons')->insertGetId([
            'business_id' => $businessId,
            'code' => 'WELCOME10',
            'description' => 'Flat $10 off on orders over $40!',
            'discount_type' => 'flat',
            'discount_value' => 10.00,
            'min_order_amount' => 40.00,
            'usage_limit' => 1000,
            'usage_limit_per_user' => 1,
            'used_count' => 45,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->addDays(60),
            'status' => 'active',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 8. Insert Demo Orders
        $orders = [
            [
                'ref_no' => 'ORD-100201',
                'invoice_no' => 'INV-2026-9041',
                'customer_name' => 'Sarah Connor',
                'email' => 'sarah.c@sky.net',
                'phone' => '+1 (555) 019-2831',
                'address' => '404 Resistance Rd, Los Angeles, CA',
                'items' => [
                    ['slug' => 'quantum-chronograph-watch', 'qty' => 1],
                    ['slug' => 'aerobuds-pro-wireless', 'qty' => 1],
                ],
                'discount' => 10.00,
                'coupon_id' => $coupon2,
                'coupon_discount' => 10.00,
                'shipping' => 15.00,
                'gateway' => 'stripe',
                'status' => 'completed',
                'payment_status' => 'paid',
                'date' => now()->subHours(2),
            ],
            [
                'ref_no' => 'ORD-100202',
                'invoice_no' => 'INV-2026-4859',
                'customer_name' => 'Bruce Wayne',
                'email' => 'bruce@waynecorp.com',
                'phone' => '+1 (555) 911-3829',
                'address' => 'Wayne Manor, Gotham City',
                'items' => [
                    ['slug' => 'lumbar-comfort-office-chair', 'qty' => 2],
                ],
                'discount' => 100.00, // percentage 50% capped
                'coupon_id' => $coupon1,
                'coupon_discount' => 100.00,
                'shipping' => 25.00,
                'gateway' => 'sslcommerz',
                'status' => 'completed',
                'payment_status' => 'paid',
                'date' => now()->subDays(1),
            ],
            [
                'ref_no' => 'ORD-100203',
                'invoice_no' => 'INV-2026-1182',
                'customer_name' => 'Clark Kent',
                'email' => 'clark.k@dailyplanet.com',
                'phone' => '+1 (555) 777-8822',
                'address' => '345 Metro Heights, Metropolis',
                'items' => [
                    ['slug' => 'minimalist-leather-backpack', 'qty' => 1],
                ],
                'discount' => 0.00,
                'coupon_id' => null,
                'coupon_discount' => 0.00,
                'shipping' => 0.00,
                'gateway' => 'cod',
                'status' => 'ordered',
                'payment_status' => 'pending',
                'date' => now()->subDays(2),
            ],
        ];

        foreach ($orders as $order) {
            $subtotal = 0.00;
            $itemsToLink = [];

            foreach ($order['items'] as $itemSpec) {
                // Fetch product information
                $prod = DB::table('products')->where('slug', $itemSpec['slug'])->first();
                if ($prod) {
                    $itemTotal = $prod->price * $itemSpec['qty'];
                    $subtotal += $itemTotal;

                    // Fetch variation info
                    $var = DB::table('variations')->where('product_id', $prod->id)->first();

                    $itemsToLink[] = [
                        'product_id' => $prod->id,
                        'variation_id' => $var->id,
                        'qty' => $itemSpec['qty'],
                        'price' => $prod->price,
                    ];
                }
            }

            $grandTotal = $subtotal - $order['coupon_discount'] + $order['shipping'];

            $custInfo = $customerMap[$order['customer_name']] ?? null;
            $contactIdForTrans = $custInfo ? $custInfo['contact_id'] : null;
            $userIdForTrans = $custInfo ? $custInfo['user_id'] : $userId;

            // Insert Transaction
            $transId = DB::table('transactions')->insertGetId([
                'business_id' => $businessId,
                'location_id' => $locationId,
                'contact_id' => $contactIdForTrans,
                'created_by' => $userIdForTrans,
                'type' => 'sales_order',
                'status' => $order['status'],
                'payment_status' => $order['payment_status'],
                'invoice_no' => $order['invoice_no'],
                'ref_no' => $order['ref_no'],
                'transaction_date' => $order['date'],
                'total_before_tax' => $subtotal,
                'discount_type' => $order['coupon_id'] ? 'fixed' : null,
                'discount_amount' => $order['coupon_discount'],
                'coupon_id' => $order['coupon_id'],
                'shipping_charges' => $order['shipping'],
                'shipping_address' => $order['address'],
                'final_total' => $grandTotal,
                'created_at' => $order['date'],
                'updated_at' => $order['date'],
            ]);

            // Seed transaction payment if order is paid
            if ($order['payment_status'] === 'paid') {
                $paymentType = TransactionPayment::determinePaymentType($grandTotal);

                DB::table('transaction_payments')->insert([
                    'transaction_id' => $transId,
                    'business_id' => $businessId,
                    'amount' => $grandTotal,
                    'method' => 'online',
                    'payment_type' => $paymentType,
                    'gateway' => $order['gateway'],
                    'paid_on' => $order['date'],
                    'created_by' => $userIdForTrans,
                    'status' => 'success',
                    'created_at' => $order['date'],
                    'updated_at' => $order['date'],
                ]);
            }

            // Insert Sell Lines & Coupon Usage if applicable
            foreach ($itemsToLink as $linkItem) {
                DB::table('transaction_sell_lines')->insert([
                    'transaction_id' => $transId,
                    'product_id' => $linkItem['product_id'],
                    'variation_id' => $linkItem['variation_id'],
                    'quantity' => $linkItem['qty'],
                    'unit_price' => $linkItem['price'],
                    'unit_price_before_discount' => $linkItem['price'],
                    'unit_price_inc_tax' => $linkItem['price'],
                    'item_tax' => 0.00,
                    'created_at' => $order['date'],
                    'updated_at' => $order['date'],
                ]);

                DB::table('variation_location_details')
                    ->where('product_id', $linkItem['product_id'])
                    ->where('variation_id', $linkItem['variation_id'])
                    ->decrement('qty_available', $linkItem['qty']);
            }

            if ($order['coupon_id']) {
                DB::table('coupon_usages')->insert([
                    'coupon_id' => $order['coupon_id'],
                    'user_id' => $userIdForTrans,
                    'transaction_id' => $transId,
                    'discount_applied' => $order['coupon_discount'],
                    'created_at' => $order['date'],
                ]);
            }

            \App\Models\Transaction::checkAndGenerateSell($transId);
        }
    }
}
