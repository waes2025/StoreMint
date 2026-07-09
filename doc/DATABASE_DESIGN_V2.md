# Database Design Document (v2)
## Based on Existing Schema — Gap Analysis + New Tables for Coupons, Invoices & Payment Gateways

**Companion to:** PRD.md, DESIGN_GRID_GUIDELINES.md
**Base:** Your existing SQL dump (POS-style schema — `business`, `transactions`, `contacts`, `variations`, etc.)
**Purpose:** Map your existing schema to the e-commerce PRD requirements, flag gaps, fix inconsistencies, and add only what's missing — rather than redesigning what already works.

---

## 1. How Your Existing Schema Maps to the PRD

Your schema is a POS-style structure (multi-tenant via `business`/`business_id`, generic `transactions` for both purchases and sales). Here's the mapping to standard e-commerce concepts used in the PRD:

| PRD Concept | Your Existing Table | Notes |
|---|---|---|
| Customers | `users` (role=`customer`) + `contacts` | `contacts` is CRM-style (supplier/customer); `users` handles auth/login |
| Orders | `transactions` (`type` = `sell`) | Generic transaction table also used for purchases — filtered by `type` |
| Order items | `transaction_sell_lines` | Line items for a sell transaction |
| Payments | `transaction_payments` | Supports multiple payments per transaction |
| Products | `products` + `variations` + `product_variations` | Variation-based catalog (size/color etc.) |
| Product images | `product_images` | Already present, supports multiple images + primary flag |
| Categories | `categories` + `categorizables` | Polymorphic tagging already supported |
| Cart | `carts` + `cart_items` | Already present, storefront-ready |
| Reviews | `reviews` | Already present |
| Wishlist | `wishlists` | Already present |
| Homepage banners | `hero_slides` | Already present |
| Order status trail | `order_status_histories` | Present but has a **type mismatch** (see §3.1) |
| Generic discounts | `discounts` + `discount_variations` | Rule-based discounts (by brand/category/location) — **not** the same as a customer-entered coupon code (see §3.2) |

**Conclusion:** Most of the e-commerce backbone already exists. The gaps are specifically: **coupons (code-based, customer-applied)**, **invoices (snapshotted, printable)**, and **clean payment-gateway tracking for COD/SSLCommerz/Stripe**. These are addressed in §4–§6 below.

---

## 2. Schema Audit Notes (things worth knowing before building on top)

- `transactions.type` is a free-text `varchar(50)` (comment: `sales_order, sell`) — for the storefront, all customer orders should consistently use `type = 'sell'`, `sub_type` reserved for POS/storefront distinction if needed (e.g., `sub_type = 'online'`).
- `transactions.payment_status` is `enum('paid','due','partial')` — this works for storefront orders too, but doesn't cover `failed` / `cancelled` / `refunded`, which the PRD's payment flows need (Stripe failure, SSLCommerz cancel, etc.). See §5.1.
- `transaction_payments.method` and `.gateway` are both free-text `varchar` — good for flexibility (`cod`, `sslcommerz`, `stripe`), but there's **no field to store the raw gateway response** (needed to reconcile Stripe webhooks) and **no dedicated status field** for a payment attempt (initiated/success/failed).
- `contacts` and `users` both exist — recommend `users` is the storefront login identity, and each storefront customer optionally links to a `contacts` row (`crm_contact_id` already exists on `users` for this).
- No structured shipping/billing **address** table — `transactions` has a single `shipping_address` text blob and `contacts` has flat address columns. A structured `addresses` table (§7) is recommended so customers can save multiple addresses and reorder.
- `discounts` table is rule-based (auto-applied by brand/category/location/date), not code-based. It should **stay as-is** for automatic promotions; **coupons are a new, separate table** since they require a customer-entered code, usage limits, and min-order validation, which `discounts` doesn't support.

---

## 3. Fixes Needed to Existing Tables

### 3.1 Fix `order_status_histories.order_id` type mismatch
```sql
-- Current: order_id is bigint(20) UNSIGNED, but transactions.id is int(10) UNSIGNED
ALTER TABLE `order_status_histories`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL;
```
Also consider renaming `order_id` → `transaction_id` for clarity, since it references `transactions.id`, not a separate `orders` table:
```sql
ALTER TABLE `order_status_histories`
  CHANGE `order_id` `transaction_id` int(10) UNSIGNED NOT NULL;
```

### 3.2 Extend `transactions.payment_status` for gateway-based flows
```sql
ALTER TABLE `transactions`
  MODIFY `payment_status` ENUM('pending','paid','due','partial','failed','cancelled','refunded') DEFAULT 'pending';
```

### 3.3 Add coupon + payment-gateway tracking directly on `transactions`
```sql
ALTER TABLE `transactions`
  ADD `coupon_id` INT(10) UNSIGNED DEFAULT NULL AFTER `discount_amount`,
  ADD `coupon_discount_amount` DECIMAL(20,2) NOT NULL DEFAULT 0.00 AFTER `coupon_id`,
  ADD `payment_gateway` ENUM('cod','sslcommerz','stripe') DEFAULT NULL AFTER `payment_status`,
  ADD `order_number` VARCHAR(30) DEFAULT NULL AFTER `id`,
  ADD UNIQUE KEY `transactions_order_number_unique` (`order_number`);
```
> `coupon_discount_amount` is kept separate from the existing generic `discount_amount` so admin-configured discounts (from `discounts`) and customer-applied coupon discounts remain independently auditable on the same order.

### 3.4 Extend `transaction_payments` for gateway reconciliation
```sql
ALTER TABLE `transaction_payments`
  ADD `status` ENUM('initiated','success','failed','cancelled','refunded') NOT NULL DEFAULT 'initiated' AFTER `gateway`,
  ADD `currency` VARCHAR(3) DEFAULT NULL AFTER `amount`,
  ADD `gateway_response` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)) AFTER `status`;
```
`gateway_response` stores the raw Stripe/SSLCommerz payload (session ID, PaymentIntent ID, IPN data) — this is the source of truth for webhook reconciliation, matching the pattern already used in your `activity_log.properties` and `passkeys.credential` (JSON-checked longtext).

---

## 4. New Table: `coupons`

```sql
CREATE TABLE `coupons` (
  `id` int(10) UNSIGNED NOT NULL,
  `business_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('flat','percentage') NOT NULL DEFAULT 'flat',
  `discount_value` decimal(20,2) NOT NULL DEFAULT 0.00,
  `max_discount_amount` decimal(20,2) DEFAULT NULL COMMENT 'Cap for percentage type',
  `min_order_amount` decimal(20,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL COMMENT 'Total redemptions allowed; null = unlimited',
  `usage_limit_per_user` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_business_id_code_unique` (`business_id`,`code`),
  ADD KEY `coupons_status_index` (`status`),
  ADD KEY `coupons_expires_at_index` (`expires_at`);

ALTER TABLE `coupons` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
```
- `business_id` included to stay consistent with the multi-tenant pattern used everywhere else in your schema; if this project is single-tenant, it can be dropped, but keeping it costs nothing and future-proofs multi-store use.
- Code uniqueness is scoped **per business**, matching your existing multi-tenant convention (see `roles`, `units`, `categories`, all scoped by `business_id`).

---

## 5. New Table: `coupon_usages`

```sql
CREATE TABLE `coupon_usages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coupon_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `transaction_id` int(10) UNSIGNED NOT NULL COMMENT 'FK to transactions.id (the order)',
  `discount_applied` decimal(20,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `coupon_usages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_usages_coupon_transaction_unique` (`coupon_id`,`transaction_id`),
  ADD KEY `coupon_usages_coupon_id_index` (`coupon_id`),
  ADD KEY `coupon_usages_user_id_index` (`user_id`);

ALTER TABLE `coupon_usages` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
```
- The unique constraint on (`coupon_id`,`transaction_id`) prevents the same coupon being double-counted against the same order under concurrent requests.
- Enforcing `usage_limit_per_user` should be done in application logic by counting rows here (`WHERE coupon_id = ? AND user_id = ?`), since MySQL can't natively cap a count via constraint.

---

## 6. New Table: `invoices`

```sql
CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` int(10) UNSIGNED NOT NULL COMMENT 'FK to transactions.id (the order)',
  `invoice_number` varchar(30) NOT NULL,
  `subtotal` decimal(20,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(20,2) NOT NULL DEFAULT 0.00,
  `coupon_discount_amount` decimal(20,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(20,2) NOT NULL DEFAULT 0.00,
  `shipping_charges` decimal(20,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(20,2) NOT NULL DEFAULT 0.00,
  `pdf_path` varchar(255) DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_transaction_id_unique` (`transaction_id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`);

ALTER TABLE `invoices` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
```
- Kept as a **separate snapshot table** rather than reusing `transactions.invoice_no` directly, because invoice totals must remain frozen at time of issue even if the underlying order/product/coupon data changes later — matching the same principle already implicit in your `variation_group_prices`/`purchase_lines` snapshotting of historical prices.
- `transactions.invoice_no` (already existing) can either be deprecated in favor of `invoices.invoice_number`, or kept in sync — recommend keeping `transactions.invoice_no` as a denormalized copy for quick display, with `invoices.invoice_number` as the source of truth.

---

## 7. New Table (Recommended): `addresses`

Not strictly required by the PRD's minimum scope, but strongly recommended since your current schema has no structured, reusable shipping/billing address per customer (only flat fields on `contacts` and a text blob on `transactions.shipping_address`).

```sql
CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `label` varchar(50) DEFAULT NULL COMMENT 'e.g. Home, Office',
  `full_name` varchar(191) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line_1` varchar(191) NOT NULL,
  `address_line_2` varchar(191) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_id_index` (`user_id`);

ALTER TABLE `addresses` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `transactions`
  ADD `shipping_address_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `shipping_address`,
  ADD `billing_address_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `shipping_address_id`;
```
The existing `transactions.shipping_address` text field can remain as a free-text fallback/legacy field; `shipping_address_id`/`billing_address_id` become the structured reference going forward.

---

## 8. Updated Entity Relationship Summary

```
business ──< business_locations
business ──< users ──< carts ──< cart_items >── products
   │            │
   │            ├──< addresses
   │            ├──< wishlists >── products
   │            ├──< reviews >── products
   │            └──< coupon_usages >── coupons
   │
   ├──< contacts
   ├──< categories ──< categorizables (polymorphic)
   ├──< brands
   ├──< products ──< product_images
   │        ├──< product_variations ──< variations ──< variation_location_details
   │        └──< reviews / wishlists / cart_items / transaction_sell_lines
   │
   ├──< coupons ──< coupon_usages
   │
   └──< transactions (type='sell')  ──< transaction_sell_lines >── variations
              │
              ├──< transaction_payments
              ├──< order_status_histories
              ├──< coupon_usages
              └──< invoices (1:1)
```

---

## 9. Payment Gateway Field Mapping (COD / SSLCommerz / Stripe)

| Field | COD | SSLCommerz | Stripe |
|---|---|---|---|
| `transactions.payment_gateway` | `cod` | `sslcommerz` | `stripe` |
| `transaction_payments.method` | `cash` | `online` | `card` |
| `transaction_payments.gateway` | `cod` | `sslcommerz` | `stripe` |
| `transaction_payments.transaction_no` | — (nullable) | SSLCommerz `tran_id` | Stripe `session_id` / `payment_intent_id` |
| `transaction_payments.status` | `success` on delivery confirm (or `initiated` until then) | `success`/`failed` from IPN callback | `success`/`failed` from webhook |
| `transaction_payments.gateway_response` | null | raw SSLCommerz IPN payload (JSON) | raw Stripe event payload (JSON) |
| `transactions.payment_status` | `due` until delivery, then `paid` | `paid`/`failed`/`cancelled` | `paid`/`failed`/`cancelled` |

**Reconciliation rule:** For Stripe and SSLCommerz, `transactions.payment_status` should only be set to `paid` by the **server-side webhook/IPN handler**, never by the browser redirect alone — the redirect only triggers the UI state; the webhook is the source of truth (as already noted in the PRD, §3.3).

---

## 10. Migration Order (Additions Only)

Run these against your existing schema, in order:

1. `ALTER TABLE order_status_histories` (fix type mismatch) — §3.1
2. `ALTER TABLE transactions` (payment_status enum, coupon fields, payment_gateway, order_number) — §3.2, §3.3
3. `ALTER TABLE transaction_payments` (status, currency, gateway_response) — §3.4
4. `CREATE TABLE coupons` — §4
5. `CREATE TABLE addresses` — §7
6. `ALTER TABLE transactions` (shipping_address_id, billing_address_id) — §7
7. `CREATE TABLE coupon_usages` — §5 *(depends on `coupons` + `transactions`)*
8. `CREATE TABLE invoices` — §6 *(depends on `transactions`)*

---

## 11. Summary of Changes

| Change Type | Item |
|---|---|
| **New tables** | `coupons`, `coupon_usages`, `invoices`, `addresses` |
| **Fixed** | `order_status_histories.order_id` type mismatch |
| **Extended** | `transactions` (+coupon fields, +payment_gateway, +order_number, wider payment_status enum) |
| **Extended** | `transaction_payments` (+status, +currency, +gateway_response) |
| **Unchanged, reused as-is** | `users`, `contacts`, `products`, `variations`, `product_images`, `categories`, `categorizables`, `brands`, `carts`, `cart_items`, `reviews`, `wishlists`, `hero_slides`, `discounts` |

This approach keeps every existing table and its data intact — nothing is dropped or renamed destructively (aside from the one type-fix in §3.1) — while adding exactly what the PRD's coupon system, invoice redesign, and multi-gateway checkout require.
