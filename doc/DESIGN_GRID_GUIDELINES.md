# Design Grid & System Guidelines
## Admin Panel + Storefront — Layout, Spacing, Typography & Color Standards

**Companion to:** PRD.md
**Purpose:** Define a consistent visual grid and design system so the Admin Panel and Storefront redesigns feel like one cohesive product rather than two unrelated UIs.

---

## 1. Grid System

### 1.1 Base Grid
- **Columns:** 12-column fluid grid
- **Gutter:** 24px (desktop), 16px (tablet), 12px (mobile)
- **Max content width:**
  - Storefront: `1280px` centered container
  - Admin Panel: `100%` fluid within sidebar layout (no hard max-width; content area scales)

### 1.2 Breakpoints

| Name | Width | Usage |
|---|---|---|
| `xs` | < 480px | Small mobile |
| `sm` | 480px – 767px | Mobile |
| `md` | 768px – 1023px | Tablet |
| `lg` | 1024px – 1279px | Small desktop / admin default |
| `xl` | 1280px – 1535px | Desktop |
| `2xl` | ≥ 1536px | Large desktop |

### 1.3 Column Behavior by Breakpoint

| Layout | xs/sm | md | lg+ |
|---|---|---|---|
| Storefront product grid | 1 col | 2 col | 3–4 col |
| Storefront cart/checkout | 1 col (stacked) | 1 col | 2 col (form + summary) |
| Admin data tables | scroll/stacked cards | scroll table | full table |
| Admin forms | 1 col | 2 col | 2–3 col |

### 1.4 Admin Panel Layout Grid
```
┌─────────────┬──────────────────────────────────────────┐
│             │  Topbar (search · notifications · profile)│  64px height
│  Sidebar    ├──────────────────────────────────────────┤
│  240px      │  Breadcrumb                                │  40px height
│  (72px      ├──────────────────────────────────────────┤
│  collapsed) │                                            │
│             │  Content area (12-col grid, 24px gutter,   │
│             │  24px page padding)                        │
│             │                                            │
└─────────────┴──────────────────────────────────────────┘
```

### 1.5 Storefront Layout Grid
```
┌────────────────────────────────────────────────────────┐
│  Announcement bar (optional)                    36px    │
├────────────────────────────────────────────────────────┤
│  Header: logo · nav · search · cart              80px   │
├────────────────────────────────────────────────────────┤
│                                                          │
│  Main content — 1280px max, centered,                   │
│  12-col grid, 24px gutter                                │
│                                                          │
├────────────────────────────────────────────────────────┤
│  Footer                                                  │
└────────────────────────────────────────────────────────┘
```

---

## 2. Spacing Scale

Use a single consistent spacing scale (Tailwind default multiples of 4px) across both admin and storefront — no arbitrary pixel values.

| Token | Value | Common Use |
|---|---|---|
| `space-1` | 4px | icon-to-text gap |
| `space-2` | 8px | tight inline spacing |
| `space-3` | 12px | form field internal padding |
| `space-4` | 16px | default component padding |
| `space-6` | 24px | grid gutter, card padding |
| `space-8` | 32px | section spacing (mobile) |
| `space-12` | 48px | section spacing (desktop) |
| `space-16` | 64px | major section breaks |
| `space-24` | 96px | hero/landing spacing |

**Rule:** Never mix arbitrary values (e.g. `13px`, `27px`) into layouts — always round to the nearest scale token.

---

## 3. Typography Grid

### 3.1 Type Scale (1.25 modular ratio)

| Token | Size | Line Height | Usage |
|---|---|---|---|
| `text-xs` | 12px | 16px | badges, table meta |
| `text-sm` | 14px | 20px | table body, form labels |
| `text-base` | 16px | 24px | body copy |
| `text-lg` | 18px | 28px | card titles |
| `text-xl` | 20px | 28px | section headers (admin) |
| `text-2xl` | 24px | 32px | page titles (admin) |
| `text-3xl` | 30px | 38px | storefront section headers |
| `text-4xl` | 36px | 44px | PDP product title |
| `text-5xl` | 48px | 56px | homepage hero headline |

### 3.2 Font Weight Usage
- **400 (Regular):** body text
- **500 (Medium):** labels, nav items, table headers
- **600 (Semibold):** card titles, buttons
- **700 (Bold):** page titles, hero headline, prices

### 3.3 Typography Rules
- One font family for UI (e.g., Inter, Manrope, or similar) — optionally a second display font for storefront hero/marketing sections only.
- Body text line length capped at ~75 characters for readability.
- Prices and totals always use tabular/monospaced numerals where the font supports it, for alignment in tables and invoices.

---

## 4. Color System

### 4.1 Palette Structure

| Role | Token | Example Use |
|---|---|---|
| Primary | `primary-500` (+ 50–900 scale) | Buttons, links, active states |
| Secondary/Accent | `accent-500` | Highlights, badges, promo tags |
| Neutral | `gray-50` → `gray-900` | Backgrounds, borders, text |
| Success | `green-500` | Paid, in-stock, success toasts |
| Warning | `amber-500` | Low stock, pending payment |
| Danger | `red-500` | Errors, failed payment, delete actions |
| Info | `blue-500` | Informational banners |

### 4.2 Usage Rules
- Backgrounds: `gray-50` (page background), `white` (cards/panels).
- Borders: `gray-200` default, `gray-300` on hover/focus.
- Body text: `gray-700`–`gray-900`; muted text: `gray-500`.
- Primary color reserved for primary actions only (1 primary CTA per view) to preserve visual hierarchy.
- Status badges (order/payment status) use fixed color mapping:
  - `Paid` → green
  - `Pending` → amber
  - `Failed / Cancelled` → red
  - `COD` → blue/neutral

### 4.3 Contrast
- All text/background pairs must meet WCAG AA (4.5:1 for body text, 3:1 for large text/UI components).

---

## 5. Component Grid Rules

### 5.1 Cards
- Padding: `space-6` (24px) desktop, `space-4` (16px) mobile.
- Border radius: `8px` (admin), `12px` (storefront — slightly softer/friendlier).
- Shadow: subtle single-layer shadow (`0 1px 3px rgba(0,0,0,0.08)`); avoid heavy/multiple shadows.

### 5.2 Buttons
- Height: `40px` (default), `48px` (primary storefront CTAs), `32px` (compact/admin table row actions).
- Padding: `space-4` horizontal minimum.
- Border radius: `6px` (admin), `8px` (storefront).

### 5.3 Tables (Admin)
- Row height: `48px` minimum for touch/click comfort.
- Header row: `gray-50` background, `text-sm`, weight 500.
- Zebra striping optional; prefer bottom-border row separators over full striping.

### 5.4 Forms
- Field height: `40px`–`44px`.
- Label above field, `space-2` gap.
- Field spacing: `space-4` vertical between fields, `space-6` between field groups.
- Validation messages: `text-sm`, red, `space-1` below field.

### 5.5 Product Grid (Storefront)
- Card aspect ratio: `1:1` for product image.
- Grid gap: `space-6` desktop, `space-4` mobile.
- Consistent card anatomy: image → title (2-line clamp) → price → CTA.

---

## 6. Iconography & Imagery
- Icon set: single consistent icon library (e.g., Lucide, Heroicons) — no mixing icon styles.
- Icon sizes: `16px` (inline), `20px` (buttons/nav), `24px` (feature/empty states).
- Product images: consistent aspect ratio and background treatment across listing, PDP, cart, and invoice thumbnails.

---

## 7. Application Summary

| Element | Admin Panel | Storefront |
|---|---|---|
| Grid | 12-col, fluid, sidebar layout | 12-col, max 1280px, centered |
| Radius | 6–8px (functional, dense) | 8–12px (friendlier, spacious) |
| Density | Compact, data-dense | Comfortable, marketing-forward |
| Type scale top | `text-2xl` page titles | `text-5xl` hero headline |
| Primary use of color | Status/action clarity | Brand expression + conversion CTAs |

This shared foundation (spacing scale, type scale, color tokens, grid breakpoints) ensures the Admin Panel and Storefront read as one product built on one design system, while each retains a layout density appropriate to its purpose.
