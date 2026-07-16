<?php

namespace Database\Seeders;

use Modules\Cart\Models\Transaction;
use Modules\Cart\Models\TransactionPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed demo data on top of the base configuration.
     * Seeds: extra teams, staff, customers, products, coupons, and orders.
     */
    public function run(): void
    {
        // Run BaseDataSeeder to seed base configuration (owner, business, team, location)
        $this->call(BaseDataSeeder::class);

        $userId = DB::table('users')->where('username', 'waes')->value('id');
        $businessId = (int) config('ecommerce.business_id', 1);
        $storeTeamId = DB::table('teams')->where('slug', 'storemint-store')->value('id');
        $locationId = (int) config('ecommerce.location_id', 1);

        // Customize the business name for the demo environment
        DB::table('business')
            ->where('id', $businessId)
            ->update(['name' => 'StoreMint Demo']);

        // ── Extra Teams ───────────────────────────────────────────────────────────
        $techTeamId = DB::table('teams')->insertGetId([
            'name' => 'Tech Gadgets BD',
            'slug' => 'tech-gadgets-bd',
            'is_personal' => false,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $opsTeamId = DB::table('teams')->insertGetId([
            'name' => 'Operations',
            'slug' => 'operations',
            'is_personal' => false,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        // Add owner to extra teams
        DB::table('team_members')->insert([
            [
                'team_id' => $techTeamId,
                'user_id' => $userId,
                'role' => 'admin',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'team_id' => $opsTeamId,
                'user_id' => $userId,
                'role' => 'admin',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ]);

        // ── Demo Staff User ───────────────────────────────────────────────────────
        $staffUserId = DB::table('users')->insertGetId([
            'first_name' => 'Alex',
            'last_name' => 'Mercer',
            'username' => 'alex',
            'email' => 'alex@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'user',
            'business_id' => $businessId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('team_members')->insert([
            [
                'team_id' => $storeTeamId,
                'user_id' => $staffUserId,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $techTeamId,
                'user_id' => $staffUserId,
                'role' => 'editor',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
        ]);

        DB::table('users')
            ->where('id', $staffUserId)
            ->update(['current_team_id' => $storeTeamId]);

        // ── Demo Customers ────────────────────────────────────────────────────────
        $customersData = [
            [
                'first_name' => 'Rahim',
                'last_name' => 'Ali',
                'email' => 'rahim@example.com',
                'username' => 'rahim',
                'mobile' => '+8801711000001',
            ],
            [
                'first_name' => 'Abul',
                'last_name' => 'Kalam',
                'email' => 'kalam@example.com',
                'username' => 'kalam',
                'mobile' => '+8801819000002',
            ],
            [
                'first_name' => 'Karim',
                'last_name' => 'Uddin',
                'email' => 'karim@example.com',
                'username' => 'karim',
                'mobile' => '+8801911000003',
            ],
            [
                'first_name' => 'Tasnim',
                'last_name' => 'Jahan',
                'email' => 'tasnim@example.com',
                'username' => 'tasnim',
                'mobile' => '+8801515000004',
            ],
            [
                'first_name' => 'Nusrat',
                'last_name' => 'Jahan',
                'email' => 'nusrat@example.com',
                'username' => 'nusrat',
                'mobile' => '+8801616000005',
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
                'current_team_id' => $storeTeamId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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

            DB::table('user_contact_access')->insert([
                'user_id' => $cUserId,
                'contact_id' => $contactId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('team_members')->insert([
                'team_id' => $storeTeamId,
                'user_id' => $cUserId,
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $customerMap[$c['first_name'].' '.$c['last_name']] = [
                'user_id' => $cUserId,
                'contact_id' => $contactId,
            ];
        }

        // ── Categories ────────────────────────────────────────────────────────────
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Tech & Gadgets',
            'business_id' => $businessId,
            'short_code' => 'TECH',
            'created_by' => $userId,
            'is_allow_ecom' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fashionCategoryId = DB::table('categories')->insertGetId([
            'name' => 'Fashion & Lifestyle',
            'business_id' => $businessId,
            'short_code' => 'FASH',
            'created_by' => $userId,
            'is_allow_ecom' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $homeCategoryId = DB::table('categories')->insertGetId([
            'name' => 'Home & Living',
            'business_id' => $businessId,
            'short_code' => 'HOME',
            'created_by' => $userId,
            'is_allow_ecom' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Brands ────────────────────────────────────────────────────────────────
        $brandsDataList = [
            ['name' => 'Apex',    'slug' => 'apex'],
            ['name' => 'Aero',    'slug' => 'aero'],
            ['name' => 'Sleek',   'slug' => 'sleek'],
            ['name' => 'Ergo',    'slug' => 'ergo'],
            ['name' => 'Ember',   'slug' => 'ember'],
            ['name' => 'Aura',    'slug' => 'aura'],
            ['name' => 'Nimbus',  'slug' => 'nimbus'],
            ['name' => 'Velta',   'slug' => 'velta'],
            ['name' => 'Kova',    'slug' => 'kova'],
            ['name' => 'Lumix',   'slug' => 'lumix'],
        ];

        $insertedBrands = [];
        foreach ($brandsDataList as $b) {
            $insertedBrands[$b['name']] = DB::table('brands')->insertGetId([
                'name' => $b['name'],
                'slug' => $b['slug'],
                'business_id' => $businessId,
                'created_by' => $userId,
                'is_allow_ecom' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Products ──────────────────────────────────────────────────────────────
        $productsData = [
            // Tech & Gadgets
            [
                'name' => 'Quantum Chronograph Watch',
                'slug' => 'quantum-chronograph-watch',
                'price' => 25000.00,
                'compare_at_price' => 30000.00,
                'short_description' => 'Precision engineered chronograph watch with sapphire dial.',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => true,
                'brand' => 'Apex',
                'category' => 'tech',
                'type' => 'single',
            ],
            [
                'name' => 'AeroBuds Pro Wireless',
                'slug' => 'aerobuds-pro-wireless',
                'price' => 12000.00,
                'compare_at_price' => 15000.00,
                'short_description' => 'True wireless studio sound earbuds with Active Noise Cancelling.',
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => false,
                'brand' => 'Aero',
                'category' => 'tech',
                'type' => 'single',
            ],
            [
                'name' => 'Smart Fitness Band V4',
                'slug' => 'smart-fitness-band-v4',
                'price' => 4500.00,
                'compare_at_price' => 6000.00,
                'short_description' => 'Feature-rich smart band with heart rate monitoring and AMOLED display.',
                'image' => 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => true,
                'brand' => 'Apex',
                'category' => 'tech',
                'type' => 'variable',
                'variations' => [
                    [
                        'name' => 'Black',
                        'slug' => 'smart-fitness-band-v4-black',
                        'price' => 4500.00,
                        'compare_at_price' => 6000.00,
                    ],
                    [
                        'name' => 'Blue',
                        'slug' => 'smart-fitness-band-v4-blue',
                        'price' => 4500.00,
                        'compare_at_price' => 6000.00,
                    ],
                    [
                        'name' => 'Red',
                        'slug' => 'smart-fitness-band-v4-red',
                        'price' => 4700.00,
                        'compare_at_price' => 6200.00,
                    ],
                ]
            ],
            [
                'name' => 'Ember Mug Smart Temperature',
                'slug' => 'ember-mug-smart-temperature',
                'price' => 10500.00,
                'compare_at_price' => 12500.00,
                'short_description' => 'App-controlled smart mug keeping your brew at the ideal temperature.',
                'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=500&auto=format&fit=crop&q=60',
                'is_featured' => false,
                'is_best_seller' => false,
                'brand' => 'Ember',
                'category' => 'tech',
                'type' => 'single',
            ],
            [
                'name' => 'Aura Light Ring Lamp',
                'slug' => 'aura-light-ring-lamp',
                'price' => 4800.00,
                'compare_at_price' => 6000.00,
                'short_description' => 'Warm multi-intensity studio lighting for professional streams.',
                'image' => 'https://images.unsplash.com/photo-1507646227500-4d389b0012be?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => false,
                'brand' => 'Aura',
                'category' => 'tech',
                'type' => 'single',
            ],
            [
                'name' => 'Nimbus 4K Action Camera',
                'slug' => 'nimbus-4k-action-camera',
                'price' => 18500.00,
                'compare_at_price' => 22000.00,
                'short_description' => 'Rugged 4K waterproof action camera with 3-axis stabilization.',
                'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764bee?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => true,
                'brand' => 'Nimbus',
                'category' => 'tech',
                'type' => 'single',
            ],
            [
                'name' => 'Lumix Portable Power Bank 20K',
                'slug' => 'lumix-portable-power-bank-20k',
                'price' => 3800.00,
                'compare_at_price' => 4500.00,
                'short_description' => '20,000 mAh fast-charge power bank with dual USB-C ports.',
                'image' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=500&auto=format&fit=crop&q=60',
                'is_featured' => false,
                'is_best_seller' => true,
                'brand' => 'Lumix',
                'category' => 'tech',
                'type' => 'single',
            ],
            // Fashion & Lifestyle
            [
                'name' => 'Premium Cotton Polo T-Shirt',
                'slug' => 'premium-cotton-polo-t-shirt',
                'price' => 1200.00,
                'compare_at_price' => 1800.00,
                'short_description' => 'Comfortable premium cotton polo shirt for casual wear.',
                'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => true,
                'brand' => 'Apex',
                'category' => 'fashion',
                'type' => 'variable',
                'variations' => [
                    [
                        'name' => 'Red / M',
                        'slug' => 'premium-cotton-polo-t-shirt-red-m',
                        'price' => 1200.00,
                        'compare_at_price' => 1800.00,
                    ],
                    [
                        'name' => 'Red / L',
                        'slug' => 'premium-cotton-polo-t-shirt-red-l',
                        'price' => 1250.00,
                        'compare_at_price' => 1850.00,
                    ],
                    [
                        'name' => 'Black / M',
                        'slug' => 'premium-cotton-polo-t-shirt-black-m',
                        'price' => 1200.00,
                        'compare_at_price' => 1800.00,
                    ],
                ]
            ],
            [
                'name' => 'Minimalist Leather Backpack',
                'slug' => 'minimalist-leather-backpack',
                'price' => 7500.00,
                'compare_at_price' => 9500.00,
                'short_description' => 'Sleek top-grain calf leather laptop pack for clean utility.',
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=60',
                'is_featured' => false,
                'is_best_seller' => true,
                'brand' => 'Sleek',
                'category' => 'fashion',
                'type' => 'single',
            ],
            [
                'name' => 'Velta Premium Sunglasses',
                'slug' => 'velta-premium-sunglasses',
                'price' => 9800.00,
                'compare_at_price' => 12000.00,
                'short_description' => 'Polarized UV400 titanium frame sunglasses for all-day wear.',
                'image' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => false,
                'brand' => 'Velta',
                'category' => 'fashion',
                'type' => 'single',
            ],
            [
                'name' => 'Kova Urban Sneakers',
                'slug' => 'kova-urban-sneakers',
                'price' => 6500.00,
                'compare_at_price' => 8500.00,
                'short_description' => 'Lightweight breathable mesh sneakers for everyday street style.',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&auto=format&fit=crop&q=60',
                'is_featured' => true,
                'is_best_seller' => true,
                'brand' => 'Kova',
                'category' => 'fashion',
                'type' => 'single',
            ],
            // Home & Living
            [
                'name' => 'Lumbar Comfort Office Chair',
                'slug' => 'lumbar-comfort-office-chair',
                'price' => 22000.00,
                'compare_at_price' => 26000.00,
                'short_description' => 'Ergonomic lumbar adaptive chair with high-breathability mesh.',
                'image' => 'https://images.unsplash.com/photo-1505843490538-5133c6c7d0e1?w=500&auto=format&fit=crop&q=60',
                'is_featured' => false,
                'is_best_seller' => false,
                'brand' => 'Ergo',
                'category' => 'home',
                'type' => 'single',
            ],
            [
                'name' => 'Apex Aromatherapy Diffuser',
                'slug' => 'apex-aromatherapy-diffuser',
                'price' => 3500.00,
                'compare_at_price' => 4500.00,
                'short_description' => 'Ultrasonic 500ml mist diffuser with 7-color LED ambient lighting.',
                'image' => 'https://images.unsplash.com/photo-1608181831718-c9fbb5bfb95a?w=500&auto=format&fit=crop&q=60',
                'is_featured' => false,
                'is_best_seller' => true,
                'brand' => 'Apex',
                'category' => 'home',
                'type' => 'single',
            ],
            [
                'name' => 'Sleek Bamboo Desk Organizer',
                'slug' => 'sleek-bamboo-desk-organizer',
                'price' => 2800.00,
                'compare_at_price' => 3500.00,
                'short_description' => 'Eco-friendly modular bamboo desk tray for a clutter-free workspace.',
                'image' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&auto=format&fit=crop&q=60',
                'is_featured' => false,
                'is_best_seller' => false,
                'brand' => 'Sleek',
                'category' => 'home',
                'type' => 'single',
            ],
        ];

        // Map category keys to IDs
        $categoryMap = [
            'tech' => $categoryId,
            'fashion' => $fashionCategoryId,
            'home' => $homeCategoryId,
        ];

        // Create initial purchase transaction for stock
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
            'total_before_tax' => 1000000.00,
            'final_total' => 1000000.00,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        foreach ($productsData as $pData) {
            $sku = 'SKU-'.strtoupper(Str::random(6));
            $isVariable = isset($pData['type']) && $pData['type'] === 'variable';

            $prodId = DB::table('products')->insertGetId([
                'name' => $pData['name'],
                'business_id' => $businessId,
                'category_id' => $categoryMap[$pData['category']] ?? $categoryId,
                'brand_id' => $insertedBrands[$pData['brand']] ?? null,
                'image' => $pData['image'],
                'is_featured' => $pData['is_featured'],
                'is_allow_ecom' => true,
                'sku' => $sku,
                'type' => $isVariable ? 'variable' : 'single',
                'enable_stock' => 1,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($isVariable) {
                $prodVarId = DB::table('product_variations')->insertGetId([
                    'product_id' => $prodId,
                    'name' => 'Size-Color',
                    'is_dummy' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($pData['variations'] as $v) {
                    $varSku = $sku . '-' . strtoupper(Str::slug($v['name']));
                    $varId = DB::table('variations')->insertGetId([
                        'name' => $v['name'],
                        'product_id' => $prodId,
                        'sub_sku' => $varSku,
                        'product_variation_id' => $prodVarId,
                        'default_purchase_price' => $v['price'] * 0.6,
                        'dpp_inc_tax' => $v['price'] * 0.6,
                        'profit_percent' => 40.00,
                        'default_sell_price' => $v['price'],
                        'sell_price_inc_tax' => $v['price'],
                        'slug' => $v['slug'],
                        'compare_at_price' => $v['compare_at_price'],
                        'is_best_seller' => $pData['is_best_seller'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('product_details')->insert([
                        [
                            'product_id' => $prodId,
                            'variation_id' => $varId,
                            'key' => 'short_description',
                            'value' => $pData['short_description'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'product_id' => $prodId,
                            'variation_id' => $varId,
                            'key' => 'description',
                            'value' => $pData['short_description'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ]);

                    $stock = rand(15, 60);

                    DB::table('variation_location_details')->insert([
                        'product_id' => $prodId,
                        'product_variation_id' => $prodVarId,
                        'variation_id' => $varId,
                        'location_id' => $locationId,
                        'qty_available' => $stock,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('purchase_lines')->insert([
                        'transaction_id' => $initPurchaseId,
                        'product_id' => $prodId,
                        'variation_id' => $varId,
                        'quantity' => $stock,
                        'purchase_price' => $v['price'] * 0.6,
                        'purchase_price_inc_tax' => $v['price'] * 0.6,
                        'item_tax' => 0.00,
                        'created_at' => now()->subDays(10),
                        'updated_at' => now()->subDays(10),
                    ]);
                }
            } else {
                $prodVarId = DB::table('product_variations')->insertGetId([
                    'product_id' => $prodId,
                    'name' => 'DUMMY_VAR',
                    'is_dummy' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

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
                    'slug' => $pData['slug'],
                    'compare_at_price' => $pData['compare_at_price'],
                    'is_best_seller' => $pData['is_best_seller'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('product_details')->insert([
                    [
                        'product_id' => $prodId,
                        'variation_id' => $varId,
                        'key' => 'short_description',
                        'value' => $pData['short_description'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'product_id' => $prodId,
                        'variation_id' => $varId,
                        'key' => 'description',
                        'value' => $pData['short_description'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                $stock = ($pData['slug'] === 'ember-mug-smart-temperature') ? 0 : rand(15, 60);

                DB::table('variation_location_details')->insert([
                    'product_id' => $prodId,
                    'product_variation_id' => $prodVarId,
                    'variation_id' => $varId,
                    'location_id' => $locationId,
                    'qty_available' => $stock,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('purchase_lines')->insert([
                    'transaction_id' => $initPurchaseId,
                    'product_id' => $prodId,
                    'variation_id' => $varId,
                    'quantity' => $stock,
                    'purchase_price' => $pData['price'] * 0.6,
                    'purchase_price_inc_tax' => $pData['price'] * 0.6,
                    'item_tax' => 0.00,
                    'created_at' => now()->subDays(10),
                    'updated_at' => now()->subDays(10),
                ]);
            }
        }

        // ── Coupons ───────────────────────────────────────────────────────────────
        $coupon1 = DB::table('coupons')->insertGetId([
            'business_id' => $businessId,
            'code' => 'MINT50',
            'description' => '50% off storewide on orders over ৳5000!',
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'max_discount_amount' => 10000.00,
            'min_order_amount' => 5000.00,
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
            'description' => 'Flat ৳1000 off on orders over ৳4000!',
            'discount_type' => 'flat',
            'discount_value' => 1000.00,
            'min_order_amount' => 4000.00,
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

        $coupon3 = DB::table('coupons')->insertGetId([
            'business_id' => $businessId,
            'code' => 'FLASH25',
            'description' => '25% off sitewide — limited time flash sale!',
            'discount_type' => 'percentage',
            'discount_value' => 25.00,
            'max_discount_amount' => 7500.00,
            'min_order_amount' => 3000.00,
            'usage_limit' => 200,
            'usage_limit_per_user' => 2,
            'used_count' => 82,
            'starts_at' => now()->subDays(2),
            'expires_at' => now()->addDays(5),
            'status' => 'active',
            'created_by' => $userId,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        // ── Demo Orders ───────────────────────────────────────────────────────────
        $orders = [
            // ── Recent completed orders (today / yesterday) ──────────────────────
            [
                'ref_no' => 'ORD-100201',
                'invoice_no' => 'INV-2026-9041',
                'customer_name' => 'Rahim Ali',
                'address' => '12/A Dhanmondi, Dhaka, Bangladesh',
                'items' => [
                    ['slug' => 'quantum-chronograph-watch', 'qty' => 1],
                    ['slug' => 'aerobuds-pro-wireless',     'qty' => 1],
                ],
                'coupon_id' => $coupon2,
                'coupon_discount' => 1000.00,
                'shipping' => 150.00,
                'gateway' => 'stripe',
                'status' => 'completed',
                'payment_status' => 'paid',
                'date' => now()->subHours(2),
            ],
            [
                'ref_no' => 'ORD-100202',
                'invoice_no' => 'INV-2026-4859',
                'customer_name' => 'Abul Kalam',
                'address' => 'House 45, Road 11, Banani, Dhaka',
                'items' => [
                    ['slug' => 'lumbar-comfort-office-chair', 'qty' => 2],
                ],
                'coupon_id' => $coupon1,
                'coupon_discount' => 10000.00,
                'shipping' => 250.00,
                'gateway' => 'sslcommerz',
                'status' => 'completed',
                'payment_status' => 'paid',
                'date' => now()->subDays(1),
            ],
            // ── Pending / Processing orders ──────────────────────────────────────
            [
                'ref_no' => 'ORD-100203',
                'invoice_no' => 'INV-2026-1182',
                'customer_name' => 'Karim Uddin',
                'address' => 'Mirpur 10, Dhaka, Bangladesh',
                'items' => [
                    ['slug' => 'premium-cotton-polo-t-shirt-red-m', 'qty' => 1],
                ],
                'coupon_id' => null,
                'coupon_discount' => 0.00,
                'shipping' => 0.00,
                'gateway' => 'cod',
                'status' => 'ordered',
                'payment_status' => 'pending',
                'date' => now()->subDays(2),
            ],
            [
                'ref_no' => 'ORD-100204',
                'invoice_no' => 'INV-2026-3371',
                'customer_name' => 'Tasnim Jahan',
                'address' => 'GEC Circle, Chittagong, Bangladesh',
                'items' => [
                    ['slug' => 'velta-premium-sunglasses', 'qty' => 1],
                    ['slug' => 'kova-urban-sneakers',      'qty' => 1],
                ],
                'coupon_id' => $coupon3,
                'coupon_discount' => 4075.00,
                'shipping' => 100.00,
                'gateway' => 'stripe',
                'status' => 'ordered',
                'payment_status' => 'paid',
                'date' => now()->subDays(2)->subHours(5),
            ],
            [
                'ref_no' => 'ORD-100205',
                'invoice_no' => 'INV-2026-7723',
                'customer_name' => 'Nusrat Jahan',
                'address' => 'Zindabazar, Sylhet, Bangladesh',
                'items' => [
                    ['slug' => 'nimbus-4k-action-camera',       'qty' => 1],
                    ['slug' => 'lumix-portable-power-bank-20k', 'qty' => 2],
                ],
                'coupon_id' => null,
                'coupon_discount' => 0.00,
                'shipping' => 200.00,
                'gateway' => 'stripe',
                'status' => 'processing',
                'payment_status' => 'paid',
                'date' => now()->subDays(3),
            ],
            // ── Shipped orders (past week) ────────────────────────────────────────
            [
                'ref_no' => 'ORD-100206',
                'invoice_no' => 'INV-2026-5510',
                'customer_name' => 'Rahim Ali',
                'address' => '12/A Dhanmondi, Dhaka, Bangladesh',
                'items' => [
                    ['slug' => 'apex-aromatherapy-diffuser',    'qty' => 1],
                    ['slug' => 'sleek-bamboo-desk-organizer',   'qty' => 2],
                ],
                'coupon_id' => $coupon2,
                'coupon_discount' => 1000.00,
                'shipping' => 80.00,
                'gateway' => 'sslcommerz',
                'status' => 'shipped',
                'payment_status' => 'paid',
                'date' => now()->subDays(5),
            ],
            [
                'ref_no' => 'ORD-100207',
                'invoice_no' => 'INV-2026-8832',
                'customer_name' => 'Abul Kalam',
                'address' => 'House 45, Road 11, Banani, Dhaka',
                'items' => [
                    ['slug' => 'smart-fitness-band-v4-black', 'qty' => 1],
                    ['slug' => 'aura-light-ring-lamp',        'qty' => 2],
                ],
                'coupon_id' => null,
                'coupon_discount' => 0.00,
                'shipping' => 120.00,
                'gateway' => 'stripe',
                'status' => 'shipped',
                'payment_status' => 'paid',
                'date' => now()->subDays(6),
            ],
            // ── Completed orders (2 weeks ago) ────────────────────────────────────
            [
                'ref_no' => 'ORD-100208',
                'invoice_no' => 'INV-2026-2241',
                'customer_name' => 'Karim Uddin',
                'address' => 'Mirpur 10, Dhaka, Bangladesh',
                'items' => [
                    ['slug' => 'quantum-chronograph-watch', 'qty' => 1],
                ],
                'coupon_id' => $coupon1,
                'coupon_discount' => 10000.00,
                'shipping' => 150.00,
                'gateway' => 'bkash',
                'status' => 'completed',
                'payment_status' => 'paid',
                'date' => now()->subDays(14),
            ],
            [
                'ref_no' => 'ORD-100209',
                'invoice_no' => 'INV-2026-6614',
                'customer_name' => 'Nusrat Jahan',
                'address' => 'Zindabazar, Sylhet, Bangladesh',
                'items' => [
                    ['slug' => 'aerobuds-pro-wireless',    'qty' => 1],
                    ['slug' => 'velta-premium-sunglasses', 'qty' => 1],
                ],
                'coupon_id' => null,
                'coupon_discount' => 0.00,
                'shipping' => 0.00,
                'gateway' => 'stripe',
                'status' => 'completed',
                'payment_status' => 'paid',
                'date' => now()->subDays(16),
            ],
            // ── Cancelled order (older) ────────────────────────────────────────────
            [
                'ref_no' => 'ORD-100210',
                'invoice_no' => 'INV-2026-9999',
                'customer_name' => 'Tasnim Jahan',
                'address' => 'GEC Circle, Chittagong, Bangladesh',
                'items' => [
                    ['slug' => 'lumix-portable-power-bank-20k', 'qty' => 1],
                ],
                'coupon_id' => null,
                'coupon_discount' => 0.00,
                'shipping' => 50.00,
                'gateway' => 'cod',
                'status' => 'cancelled',
                'payment_status' => 'pending',
                'date' => now()->subDays(20),
            ],
        ];

        foreach ($orders as $order) {
            $subtotal = 0.00;
            $itemsToLink = [];

            foreach ($order['items'] as $itemSpec) {
                $var = DB::table('variations')->where('slug', $itemSpec['slug'])->first();
                if (! $var) {
                    continue;
                }
                $prod = DB::table('products')->where('id', $var->product_id)->first();
                if (! $prod) {
                    continue;
                }

                $subtotal += $var->default_sell_price * $itemSpec['qty'];

                $itemsToLink[] = [
                    'product_id' => $prod->id,
                    'variation_id' => $var->id,
                    'qty' => $itemSpec['qty'],
                    'price' => $var->default_sell_price,
                ];
            }

            $grandTotal = $subtotal - $order['coupon_discount'] + $order['shipping'];

            $custInfo = $customerMap[$order['customer_name']] ?? null;
            $contactIdForTrans = $custInfo ? $custInfo['contact_id'] : null;
            $userIdForTrans = $custInfo ? $custInfo['user_id'] : $userId;

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
                    ->where('location_id', $locationId)
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

            Transaction::checkAndGenerateSell($transId);
        }
    }
}
