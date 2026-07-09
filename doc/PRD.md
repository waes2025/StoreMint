# Product Requirements Document (PRD)
## E-Commerce Platform Upgrade — Admin Panel, Storefront, Payments, Invoicing & Coupons

**Stack:** Laravel + Vue.js + Inertia.js
**Base Reference:** Class demo project (SSLCommerz-based e-commerce app)
**Document Version:** 1.0

---

## 1. Overview

### 1.1 Purpose
This PRD defines the scope, functional requirements, and technical expectations for upgrading the class-reference e-commerce application. The upgrade spans five major areas: Admin Panel UI, Storefront UI, Payment Gateway expansion (Stripe), Invoice redesign, and a new Coupon system.

### 1.2 Background
The base project (built in class) provides core e-commerce functionality with SSLCommerz as the only payment gateway, a functional but basic admin panel, a plain storefront, and a simple invoice template. This project upgrades all of the above into a portfolio-quality, production-feeling application.

### 1.3 Goals
- Deliver a modern, usable admin panel with clear visual hierarchy.
- Deliver a polished, noticeably differentiated storefront experience.
- Add Stripe as an international payment option alongside COD and SSLCommerz.
- Redesign the invoice into a professional, itemized document.
- Introduce a full coupon/discount system with admin management and storefront application.

### 1.4 Non-Goals
- No multi-vendor/marketplace support.
- No mobile native app (web-responsive only).
- No multi-currency checkout (Stripe uses a fixed base currency for this scope).
- No subscription/recurring billing.

---

## 2. Users & Roles

| Role | Description |
|---|---|
| **Admin** | Manages products, orders, coupons, payments, and views analytics. |
| **Customer** | Browses storefront, adds to cart, applies coupons, checks out via COD/SSLCommerz/Stripe. |
| **Guest** | Can browse storefront and product pages; must authenticate (or checkout as guest, if base app supports it) to complete purchase. |

---

## 3. Functional Requirements

### 3.1 Admin Panel — UI Improvement

**Objective:** Redesign the admin panel for improved usability, visual hierarchy, and modern aesthetics — a genuine upgrade over the class version, not a cosmetic tweak.

**Requirements:**
- FR-1.1: Redesign global layout — persistent sidebar navigation, top bar (search, notifications, admin profile menu), breadcrumbs.
- FR-1.2: Apply a consistent design system (spacing scale, color palette, typography) across all admin screens using Tailwind and/or a component library (e.g., PrimeVue, shadcn-vue, or custom Vue components).
- FR-1.3: Dashboard/overview page with key metrics (total orders, revenue, pending orders, low-stock products, active coupons) shown as cards/widgets, using charts where useful.
- FR-1.4: Data tables (products, orders, coupons, users) upgraded with sorting, pagination, search/filter, and bulk actions where applicable.
- FR-1.5: Forms (create/edit product, coupon, etc.) redesigned with clear grouping, inline validation feedback, and responsive layout.
- FR-1.6: Consistent empty states, loading states, and toast/notification feedback for admin actions (create/update/delete success or failure).
- FR-1.7: Responsive behavior down to tablet width at minimum.
- FR-1.8: Dark/light mode is optional but counts as a plus.

**Acceptance Criteria:**
- Every admin CRUD screen (products, orders, coupons, categories, users) reflects the new design system.
- No raw/unstyled default HTML tables or forms remain from the base project.
- Admin can complete all existing workflows (product management, order management) without regression in functionality.

---

### 3.2 Storefront — UI Improvement

**Objective:** Redesign the customer-facing storefront so it looks and feels distinctly more polished than the class demo.

**Pages in scope:**
- Homepage
- Product Listing (category/search results)
- Product Detail Page (PDP)
- Cart
- Checkout

**Requirements:**
- FR-2.1: **Homepage** — hero/banner section, featured/trending products, category navigation, promotional/coupon highlight section.
- FR-2.2: **Product Listing** — responsive grid, filters (category, price range, availability), sorting (price, newest, popularity), pagination or infinite scroll.
- FR-2.3: **Product Detail Page** — image gallery/zoom, price display (with discount/strike-through if applicable), stock status, quantity selector, add-to-cart with feedback (toast/animation), related products section.
- FR-2.4: **Cart** — line-item view with quantity update/remove, subtotal, coupon code entry, order summary, clear CTA to checkout.
- FR-2.5: **Checkout** — shipping/billing details form, order summary with itemization, coupon application/re-validation, payment method selection (COD / SSLCommerz / Stripe), order confirmation step.
- FR-2.6: Consistent branding (logo, color scheme, typography) applied across all storefront pages.
- FR-2.7: Fully responsive (mobile, tablet, desktop).
- FR-2.8: Loading skeletons and empty states (empty cart, no search results, etc.).

**Acceptance Criteria:**
- All five storefront pages are visually redesigned and functionally complete.
- Cart and checkout flows work end-to-end with real product/order data.
- Design is visibly distinct from the class demo (layout, spacing, color, component style).

---

### 3.3 Stripe Payment Gateway (International) — Optional / Bonus

**Objective:** Add Stripe as an international payment method alongside the existing SSLCommerz (local) integration.

**Decision to document in final submission:**
> State explicitly whether checkout offers **COD + SSLCommerz + Stripe** (all three) or **Stripe-only for international customers** with COD/SSLCommerz remaining for local. Recommended: offer all three, with Stripe positioned as the international/card option.

**Requirements:**
- FR-3.1: Integrate Stripe Checkout (or Stripe Payment Element) using Laravel Cashier or direct Stripe PHP SDK.
- FR-3.2: Add "Stripe" as a selectable payment method at checkout alongside COD and SSLCommerz.
- FR-3.3: On order placement with Stripe selected, create a Stripe Checkout Session (or PaymentIntent) and redirect the customer to Stripe's hosted payment page (or embedded Payment Element).
- FR-3.4: Handle **success callback** — mark order as `paid`, store Stripe payment/transaction reference, redirect to order confirmation page.
- FR-3.5: Handle **failure/cancel callback** — mark order as `payment_failed` or `cancelled`, redirect to a clear failure page with retry option.
- FR-3.6: Implement a **Stripe webhook endpoint** (`checkout.session.completed`, `payment_intent.payment_failed`, etc.) to confirm payment status server-side (do not rely solely on redirect callback — webhook is source of truth).
- FR-3.7: Store Stripe keys (publishable/secret/webhook secret) in `.env`, never hardcoded.
- FR-3.8: Use Stripe **test mode** keys for development/demo; document test card numbers used.
- FR-3.9: Order/payment status table should support a `payment_gateway` field (`cod` | `sslcommerz` | `stripe`) and a `payment_status` field (`pending` | `paid` | `failed` | `cancelled`).

**Acceptance Criteria:**
- Customer can select Stripe at checkout and complete a test payment.
- Successful payment updates order status and displays confirmation with invoice.
- Failed/cancelled payment does not mark the order as paid, and the customer is informed.
- Webhook correctly reconciles payment status even if the browser redirect is interrupted.

**Fallback:** If Stripe is not implemented, checkout remains COD + SSLCommerz only — this does not block the 65%/85% tiers but is excluded from 100%-tier credit.

---

### 3.4 Invoice Design — Update

**Objective:** Redesign the invoice generated after a successful order into a professional document.

**Requirements:**
- FR-4.1: Header with business branding (logo, business name, address, contact info).
- FR-4.2: Invoice metadata — invoice number, order number, order date, payment date, payment method/gateway, payment status.
- FR-4.3: Customer/shipping details block (name, address, phone, email).
- FR-4.4: Itemized table — product name, SKU/variant (if applicable), unit price, quantity, line total.
- FR-4.5: Totals breakdown — subtotal, discount (coupon, if applied — show coupon code and amount), tax, shipping fee, **grand total**, clearly separated and right-aligned.
- FR-4.6: Payment status badge/indicator (Paid / Pending / COD).
- FR-4.7: Footer with terms/notes and support contact.
- FR-4.8: Available as a downloadable/printable PDF (e.g., via `barryvdh/laravel-dompdf` or similar) and viewable on the order confirmation/order history page.
- FR-4.9: Consistent styling with overall brand identity established in the storefront redesign.

**Acceptance Criteria:**
- Invoice generated after any successful order (COD, SSLCommerz, or Stripe) reflects correct itemization and totals.
- Coupon discounts, if applied, appear as a distinct line item.
- Invoice is downloadable as PDF and renders correctly (no broken layout) when printed.

---

### 3.5 Coupon System (New Feature)

**Objective:** Introduce a complete coupon/discount system — database schema, admin CRUD, and storefront application logic.

#### 3.5.1 Database Schema — `coupons` table

| Column | Type | Notes |
|---|---|---|
| `id` | bigint, PK | |
| `code` | string, unique | e.g., `WELCOME10` |
| `description` | string, nullable | admin-facing note |
| `discount_type` | enum(`flat`, `percentage`) | |
| `discount_value` | decimal(10,2) | flat amount or percentage |
| `max_discount_amount` | decimal(10,2), nullable | cap for percentage discounts |
| `min_order_amount` | decimal(10,2), nullable | minimum cart subtotal to qualify |
| `usage_limit` | integer, nullable | total redemptions allowed (null = unlimited) |
| `usage_limit_per_user` | integer, nullable | per-customer redemption cap |
| `used_count` | integer, default 0 | running counter |
| `starts_at` | datetime, nullable | activation date |
| `expires_at` | datetime, nullable | expiry date |
| `status` | enum(`active`, `inactive`) | |
| `created_at` / `updated_at` | timestamps | |

**Supporting table (recommended):** `coupon_usages` (`id`, `coupon_id`, `user_id`, `order_id`, `discount_applied`, `created_at`) — tracks redemption history and enforces per-user limits.

#### 3.5.2 Admin Requirements
- FR-5.1: Admin CRUD screen for coupons (list, create, edit, delete/deactivate) following the redesigned admin UI patterns.
- FR-5.2: List view shows code, type, value, usage (`used_count`/`usage_limit`), status, expiry, with filters (active/inactive/expired) and search by code.
- FR-5.3: Create/edit form with validation (unique code, positive numeric values, `expires_at` after `starts_at`, etc.).
- FR-5.4: Toggle coupon status (active/inactive) directly from the list view.

#### 3.5.3 Storefront Requirements
- FR-5.5: "Apply Coupon" input field on the Cart page and/or Checkout page.
- FR-5.6: On apply, validate server-side:
  - Coupon exists and `status = active`.
  - Current date is within `starts_at`/`expires_at` window.
  - Cart subtotal ≥ `min_order_amount`.
  - `used_count` < `usage_limit` (if set).
  - Current user's redemptions < `usage_limit_per_user` (if set).
- FR-5.7: On successful validation, compute and display the discount (flat amount or percentage, capped by `max_discount_amount` if applicable), update order summary totals in real time.
- FR-5.8: On invalid coupon, show a clear, specific error message (expired / not found / minimum not met / usage limit reached / inactive).
- FR-5.9: Allow removing an applied coupon before order placement.
- FR-5.10: On order placement, persist the applied coupon reference and discount amount against the order, increment `used_count`, and log to `coupon_usages`.
- FR-5.11: Discount must correctly reflect in the final invoice (see FR-4.5).

**Acceptance Criteria:**
- Admin can create, edit, deactivate, and delete coupons.
- Storefront correctly accepts valid coupons and rejects invalid ones with accurate reasons.
- Discount calculation is accurate for both flat and percentage types, including caps and minimums.
- Applied discount is reflected consistently in cart, checkout, order record, and invoice.
- Re-using an expired or limit-reached coupon is blocked.

---

## 4. Technical Requirements

- **Backend:** Laravel (existing base version from class), RESTful/Inertia-driven controllers, Form Request validation for all new endpoints (coupons, Stripe webhooks).
- **Frontend:** Vue.js via Inertia.js; component-based architecture; shared layout components for admin and storefront.
- **Styling:** Tailwind CSS (recommended base) optionally combined with a component library (PrimeVue, shadcn-vue, Headless UI, etc.).
- **PDF Generation:** `barryvdh/laravel-dompdf` or equivalent for invoice rendering.
- **Payments:** Existing SSLCommerz integration retained; Stripe added via official Stripe PHP SDK or Laravel Cashier.
- **Database:** New migration for `coupons` and `coupon_usages`; migrations for `orders` table additions (`coupon_id`, `discount_amount`, `payment_gateway`, `payment_status`) if not already present.
- **Environment Config:** All payment gateway credentials (SSLCommerz, Stripe) via `.env`, never committed.
- **Testing:** Manual QA checklist per module (see Section 6). Automated feature tests optional but recommended for coupon validation logic and Stripe webhook handling.

---

## 5. Non-Functional Requirements

- **Usability:** Admin and storefront navigable without documentation; clear feedback on every action.
- **Performance:** Product listing and admin tables should paginate server-side rather than loading full datasets client-side.
- **Security:** Validate and sanitize all coupon and payment inputs server-side; never trust client-calculated discount totals — recompute on the server before order finalization.
- **Responsiveness:** All redesigned pages functional across mobile, tablet, and desktop breakpoints.
- **Maintainability:** Reusable Vue components (buttons, cards, tables, form inputs) shared across admin/storefront where feasible.

---

## 6. QA / Acceptance Checklist

- [ ] Admin panel: all CRUD screens redesigned and functional (no regressions).
- [ ] Storefront: homepage, listing, PDP, cart, checkout redesigned and functional.
- [ ] Coupon: admin CRUD works end-to-end.
- [ ] Coupon: storefront validation covers expiry, usage limit, min order, active status.
- [ ] Coupon: discount reflects correctly in cart → checkout → order → invoice.
- [ ] Invoice: itemization, tax/discount breakdown, branding, and payment details all correct.
- [ ] Invoice: downloadable/printable PDF works for COD, SSLCommerz, and Stripe orders.
- [ ] Stripe (if implemented): success, failure, and webhook confirmation all handled correctly.
- [ ] Payment method selection at checkout works for all enabled gateways.
- [ ] No hardcoded credentials; `.env` used for all keys.

---

## 7. Deliverables

1. Updated Laravel + Vue/Inertia codebase with all five modules implemented.
2. Database migrations for `coupons`, `coupon_usages`, and any `orders` table additions.
3. Updated `.env.example` reflecting required Stripe/SSLCommerz keys.
4. Short write-up documenting:
   - Payment method decision (all three vs. Stripe-only for international).
   - Any additional features added toward the 100% tier.
5. This PRD, kept up to date as scope evolves.

---

## 8. Open Questions

- Should guest checkout be supported, or is authentication required before checkout?
- Should coupons be stackable with other promotions, or limited to one per order? *(Recommendation: one per order for this scope.)*
- Should Stripe support multiple currencies, or a single fixed currency? *(Recommendation: single currency for this scope.)*
