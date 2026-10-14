# DOC-04 — UI/UX Design Specification

| Field | Value |
|---|---|
| Project | Modern E-Commerce Website (Bangladesh) — working name **[Store Name]** |
| Document ID | DOC-04 |
| Version | 0.1 (Draft) |
| Date | 2026-10-13 |
| Prepared by | Claude (acting UI/UX Designer) |
| Depends on | DOC-01 BRD v0.2, DOC-02 SRS v0.2, DOC-03 v0.1 |
| Companion | Clickable prototype (Design canvas): Home, Product listing, Product detail, Checkout — https://claude.ai/artifact/KbBTDvgrnq877DZE8nCAbn |
| Status | Draft — awaiting visual sign-off |

---

## 1. Design principles

1. **Calm and trustworthy.** White space, one restrained brand colour, honest information (real prices, real stock, real policies). Trust is the conversion driver for COD shoppers.
2. **Mobile first, thumb first.** Designed at 360 px then expanded. Primary actions reachable by thumb; sticky Add-to-cart and Place-order bars on mobile.
3. **Price clarity.** Price is always the most legible element on cards and product pages; delivery charge and total are visible before the customer commits.
4. **Fewer, better choices.** Concise navigation, filters that collapse on mobile, one primary button per view.
5. **Fast by default.** System-light pages, compressed images, no decorative animation; motion only to confirm actions (≤ 200 ms).
6. **Accessible for everyone.** WCAG 2.1 AA, keyboard paths, readable sizes, Bangla-ready type.
7. **No dark patterns.** No fake timers, fake scarcity, pre-ticked marketing consent, hidden charges or confirm-shaming.

## 2. Visual direction

Derived from the reference's *Sky blue* version (see BRD Appendix A), made quieter:

- **Navy** (`#1B3C52`) is the single brand colour — headings, primary buttons, key icons.
- **Sky** (`#7FD6F7`) is a supporting tint for highlights, badges and the announcement bar — never used for text on white (contrast 1.6:1).
- Pale sky surfaces (`#E8F7FD`) distinguish promotional or informational blocks from white content areas.
- The reference's vivid indigo is dropped to keep a single-hue palette.
- Corner radii are reduced from the reference (28 px cards → 16 px) for a more premium, less playful feel; buttons remain pill-shaped.

Brand name, logo and final primary colour are pending (BRD Q-01). Tokens below are the single place to change them.

## 3. Design tokens

Implemented as CSS custom properties in `resources/css/app.css` and mapped into the Tailwind theme, so components use semantic names (`bg-primary`, `text-muted`) rather than raw hex.

### 3.1 Colour

| Token | Hex | Use | Contrast (on white unless stated) |
|---|---|---|---|
| `--color-ink` / primary | `#1B3C52` | Headings, body emphasis, primary button fill | 11.6:1 |
| `--color-ink-soft` | `#3D5A6C` | Body text | 7.3:1 |
| `--color-muted` | `#5A7384` | Secondary text, captions, placeholders | 5.0:1 (4.7:1 on subtle bg) |
| `--color-link` | `#0E6E9E` | Text links, focus ring | 5.6:1 |
| `--color-accent` | `#7FD6F7` | Highlights, badges (navy text on it 7.1:1), announcement bar | decorative only |
| `--color-accent-soft` | `#E8F7FD` | Info/promo surfaces (navy text 10.6:1) | — |
| `--color-bg` | `#FFFFFF` | Page and card background | — |
| `--color-bg-subtle` | `#F5F9FB` | Section alternation, inputs at rest | — |
| `--color-border` | `#D3E1EA` | Card and divider lines (decorative) | 1.3:1 (non-essential) |
| `--color-border-strong` | `#7C929F` | Input borders, checkbox outlines | 3.3:1 (meets 3:1 for UI components) |
| `--color-success` / `-soft` | `#0B6B3A` / `#E7F5EC` | In stock, success messages | 6.6:1 / 5.9:1 |
| `--color-warning` / `-soft` | `#8A5300` / `#FFF4DB` | Low stock, notices | 5.8:1 on soft |
| `--color-danger` / `-soft` | `#B42318` / `#FDECEA` | Errors, out of stock, destructive | 6.6:1 |

Colour is never the only signal: stock and status always include text ("In stock", "Only 3 left", "Out of stock").

### 3.2 Typography

| Token | Value |
|---|---|
| Font family | `"Outfit", "Hind Siliguri", system-ui, -apple-system, "Segoe UI", sans-serif` — Outfit (Latin, SIL OFL) with Hind Siliguri as the Bangla fallback (OFL), both self-hosted as WOFF2 in production to avoid third-party requests |
| Weights | 400 regular, 500 medium, 600 semibold, 700 bold |
| Numerals | Tabular figures for prices and tables (`font-variant-numeric: tabular-nums`) |

| Style | Mobile | Desktop | Weight / line height |
|---|---|---|---|
| Display (hero) | 32 px | 52 px | 700 / 1.1 |
| H1 page title | 26 px | 36 px | 700 / 1.2 |
| H2 section | 22 px | 28 px | 600 / 1.25 |
| H3 card/panel | 18 px | 20 px | 600 / 1.3 |
| Body | 16 px | 16 px | 400 / 1.6 |
| Body small | 14 px | 14 px | 400 / 1.5 |
| Caption / label | 13 px | 13 px | 500 / 1.4 |
| Price (card) | 18 px | 18 px | 700 |
| Price (product page) | 28 px | 32 px | 700 |
| Button | 15 px | 15 px | 600 |

Body text never below 14 px; form inputs 16 px to prevent iOS zoom.

### 3.3 Spacing, radius, elevation

- **Spacing scale** (4 px base): 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96. Section padding: 48 px mobile / 80 px desktop.
- **Content width**: max 1280 px, side gutters 16 px (mobile) / 24 px (tablet) / 32 px (desktop).
- **Radius**: `sm` 8 px (inputs, chips), `md` 12 px (thumbnails, small cards), `lg` 16 px (product cards, panels), `xl` 24 px (hero, promo bands), `pill` 999 px (buttons, badges).
- **Elevation**: `e0` none + 1 px border (default cards); `e1` `0 4px 14px rgba(27,60,82,.08)` (hover, dropdowns); `e2` `0 18px 40px rgba(27,60,82,.14)` (drawers, dialogs).
- **Motion**: 150–200 ms ease-out for hover/press; drawers 220 ms; all disabled under `prefers-reduced-motion`.

## 4. Responsive breakpoints

Tailwind defaults, mobile first:

| Name | Min width | Layout notes |
|---|---|---|
| base | 0 (designed at 360) | 2-column product grid; hamburger menu; bottom-sticky CTAs; filters in a slide-over sheet |
| `sm` | 640 px | 2–3 column grid |
| `md` | 768 px | 3-column grid; checkout still single column |
| `lg` | 1024 px | Full header navigation; filters in left sidebar; product page two-column; checkout two-column (form + sticky summary) |
| `xl` | 1280 px | 4-column grid; max content width reached |
| `2xl` | 1536 px | Centered content; no further scaling |

## 5. Information architecture

### 5.1 Page inventory

| Area | Page | Route (proposed) | Notes |
|---|---|---|---|
| Storefront | Home | `/` | Admin-managed sections |
| | Shop all / listing | `/shop` | Filters + sort |
| | Category listing | `/category/{slug}` | Subcategory chips at top |
| | Search results | `/search?q=` | |
| | New arrivals / Deals | `/new`, `/deals` | Deals = variants with genuine compare-at price |
| | Product detail | `/product/{slug}` | |
| | Cart | `/cart` | Plus mini-cart drawer |
| | Checkout | `/checkout` | Single page |
| | Order confirmation | `/order/{number}/thank-you` | Owner-only / signed URL |
| | Track order | `/track-order` | Order number + phone |
| | Static pages | `/page/{slug}` | About, FAQ, Privacy, Terms, Returns, Shipping |
| | Contact | `/contact` | |
| | Errors | 404, 403, 419, 429, 500, 503 | Branded |
| Account | Login, Register, Forgot/Reset password | `/login`, `/register`, `/forgot-password`, `/reset-password/{token}` | |
| | Dashboard (overview) | `/account` | Recent orders, default address |
| | Orders, Order detail | `/account/orders`, `/account/orders/{number}` | |
| | Addresses | `/account/addresses` | |
| | Wishlist | `/account/wishlist` | |
| | Profile & password | `/account/profile` | |
| Admin | Login | `/admin/login` | Separate guard |
| | Dashboard | `/admin` | |
| | Catalogue | `/admin/products`, `/admin/categories`, `/admin/options`, `/admin/brands` | |
| | Inventory | `/admin/inventory` | |
| | Orders | `/admin/orders` | |
| | Customers | `/admin/customers` | |
| | Marketing | `/admin/coupons`, `/admin/banners`, `/admin/home-sections`, `/admin/pages` | |
| | Reviews | `/admin/reviews` | |
| | Settings | `/admin/settings/*` (store, delivery zones, payments, SEO, notifications) | |
| | Staff & roles | `/admin/staff`, `/admin/roles` | |
| | Reports | `/admin/reports/*` | |
| | Audit log | `/admin/audit-log` | |

### 5.2 Storefront navigation

```
Announcement bar (optional, admin text)
Header:  [Logo]  [Shop ▾ categories]  New  Deals   [ Search ............ ]   [Track] [Wishlist] [Account] [Cart (n)]
Mobile:  [☰]  [Logo]                                          [Search icon] [Cart (n)]
         Drawer: Search · Categories (accordion) · New · Deals · Track order · Account · Help pages
Footer:  Shop links · Help (Shipping, Returns, FAQ, Contact, Track order) · Legal (Privacy, Terms) · Contact details · Payment: "Cash on Delivery"
```

The "Shop" mega-menu lists top categories with their subcategories (from the admin category tree, max 8 shown + "All categories").

### 5.3 Admin navigation

Left sidebar (collapsible; off-canvas on mobile), grouped and filtered by permission:
Dashboard · Orders · Catalogue (Products, Categories, Options, Brands) · Inventory · Customers · Marketing (Coupons, Banners, Home sections, Pages) · Reviews · Reports · Settings (Store, Delivery zones, Payments, SEO, Notifications) · Staff & roles · Audit log.

## 6. Low-fidelity wireframes

### 6.1 Home (desktop / mobile order is the same top-to-bottom)

```
┌──────────────────────────────────────────────────────────────────┐
│ Free delivery inside Dhaka over ৳[amount] · Track your order →   │ announcement
├──────────────────────────────────────────────────────────────────┤
│ LOGO   Shop▾  New  Deals     [ Search products…        🔍]  ♡ 👤 🛒│ header
├──────────────────────────────────────────────────────────────────┤
│  HEADLINE (2 lines)                    │                          │
│  Supporting sentence                   │      HERO IMAGE          │ hero
│  [Shop now]  [Browse deals]            │                          │ (banner)
│  ✓ COD  ✓ Genuine  ✓ Easy returns      │                          │
├──────────────────────────────────────────────────────────────────┤
│ Shop by category                                     All →        │
│ [img][img][img][img][img][img]  (scroll on mobile, 2 rows desktop)│
├──────────────────────────────────────────────────────────────────┤
│ Featured | New | Best sellers   (tabs)               View all →   │
│ [card][card][card][card]                                          │
├──────────────────────────────────────────────────────────────────┤
│ PROMO BANNER (pale sky surface, image + text + CTA)               │
├──────────────────────────────────────────────────────────────────┤
│ How ordering works: 1 Order online · 2 We call to confirm · 3 COD │
├──────────────────────────────────────────────────────────────────┤
│ Trust row: Genuine · COD · Delivery estimate · Returns · Support  │
├──────────────────────────────────────────────────────────────────┤
│ Footer                                                            │
└──────────────────────────────────────────────────────────────────┘
```

### 6.2 Product listing

```
Breadcrumb · H1 Category name · (n) products
[Sub-cat chip][chip][chip]
┌──────────────┬───────────────────────────────────────────────┐
│ FILTERS      │ Active: [৳1k–3k ×] [In stock ×]   Sort [Newest▾]│
│ Category     │ [card][card][card][card]                       │
│ Price ৳–৳    │ [card][card][card][card]                       │
│ Colour ○○○   │ …                                              │
│ Brand  ☐☐    │ ‹ 1 2 3 … ›                                    │
│ ☐ In stock   │                                                │
└──────────────┴───────────────────────────────────────────────┘
Mobile: [Filters (2)] [Sort ▾] buttons → bottom sheets; 2-column grid
```

### 6.3 Product detail

```
Breadcrumb
┌─────────────────────────┬────────────────────────────────────┐
│  MAIN IMAGE             │ Category · Brand                    │
│                         │ H1 Product name                     │
│ [th][th][th][th]        │ ★ 4.6 (12 reviews)   (only if real) │
│                         │ ৳2,790  ~~৳3,490~~  Save ৳700       │
│                         │ ● In stock  ·  SKU ABC-123          │
│                         │ Colour: Black  (●)(○)(○)            │
│                         │ Qty [– 1 +]                         │
│                         │ [ Add to cart ]  [ Buy now ]   ♡    │
│                         │ 🚚 Dhaka 1–2 days · Outside 2–4 days │
│                         │ ↺ 7-day returns · 💵 COD available  │
└─────────────────────────┴────────────────────────────────────┘
Tabs: Description | Specifications | Delivery & returns | Reviews (n)
Related products: [card][card][card][card]
Mobile: sticky bottom bar [৳2,790 | Add to cart]
```

### 6.4 Checkout

```
Logo (minimal header, no menu)      Secure checkout · Need help? 01X…
┌──────────────────────────────────────┬────────────────────────┐
│ 1 Contact                             │ ORDER SUMMARY (sticky) │
│   Full name [        ]                │ [img] Item × 1   ৳2,790│
│   Mobile    [01XXXXXXXXX]             │ [img] Item × 2   ৳1,980│
│   Email (optional) [      ]           │ Coupon [      ][Apply] │
│ 2 Delivery address                    │ Subtotal        ৳4,770 │
│   Division ▾  District ▾  Area ▾      │ Discount         –৳300 │
│   Street address [                 ]  │ Delivery (Dhaka)  ৳60  │
│   Instructions (optional) [        ]  │ ─────────────────────  │
│ 3 Delivery                            │ Total           ৳4,530 │
│   Inside Dhaka · ৳60 · 1–2 days       │                        │
│ 4 Payment                             │                        │
│   (●) Cash on Delivery                │                        │
│ [ Place order · ৳4,530 ]              │                        │
│ By placing the order you agree to Terms & Returns policy        │
└──────────────────────────────────────┴────────────────────────┘
Mobile: summary collapsed at top ("Show summary · ৳4,530"), sticky Place-order bar.
```

### 6.5 Admin — order detail

```
← Orders   #ST-261013-4K7Q2   [Pending]  [Unpaid]          [Print slip] [More ▾]
┌────────────────────────────────────┬──────────────────────────┐
│ Items table (img, name, variant,   │ Customer: name, 📞 tap,  │
│ SKU, qty, unit, total)             │ email, orders: 3 prior   │
│ Subtotal / discount / delivery /   │ Address (edit while      │
│ total                              │ Pending/Confirmed)       │
│                                    │ Courier, tracking ref    │
│ Status timeline + notes            │ Actions: [Confirm]       │
│ [Add internal note]                │ [Cancel…]                │
└────────────────────────────────────┴──────────────────────────┘
Only valid next-status buttons are displayed (SRS §6.3).
```

## 7. High-fidelity direction

The companion **Design canvas** shows four high-fidelity, clickable pages using the tokens above: Home, Product listing, Product detail and Checkout. Pages are fluid and can be viewed at phone width. All names, prices and product data in the prototype are labelled demo content; product images are neutral placeholders until real photography is supplied.

Characteristic details:

- Hero: large navy headline on white with a pale-sky image panel, two buttons (primary filled navy, secondary outline), a row of three short trust points.
- Product cards: white, 1 px border, 16 px radius, square image on subtle background, category label, name (2 lines max), price row, stock note when low, round "add" button; hover lifts to `e1`.
- Badges: pill, sky background with navy text for "New"; navy background with white text for genuine discount percentages.
- Checkout: minimal header (no navigation, no distractions), numbered sections, sticky summary.

## 8. Component inventory

Built as Blade components (`resources/views/components/…`) with Alpine.js where interactive.

| Component | Variants / states | Interactivity |
|---|---|---|
| `x-button` | primary, secondary (outline), ghost, danger; sm/md/lg; loading, disabled; icon-only (with `aria-label`) | Loading spinner on submit |
| `x-input`, `x-select`, `x-textarea`, `x-checkbox`, `x-radio-card` | default, focus, error (message + `aria-describedby`), disabled, with hint | — |
| `x-phone-input` | BD prefix hint, normalisation feedback | Alpine |
| `x-address-fields` | Division → District → Area dependent selects, "Other area" text | Alpine + small JSON endpoint |
| `x-price` | regular, sale (with compare-at and saving), large | — |
| `x-stock-badge` | in stock, low (n left), out of stock | — |
| `x-badge` | new, discount %, status (order statuses colour-coded with text) | — |
| `x-product-card` | default, out of stock, with discount, compact (related) | Wishlist toggle, quick add (single-variant only) |
| `x-product-gallery` | main + thumbnails, zoom on desktop, swipe on mobile | Alpine |
| `x-variant-picker` | swatch (colour), chip (size/model), disabled combos | Alpine; updates price/stock/SKU/image |
| `x-qty-stepper` | min/max bound to stock | Alpine |
| `x-header`, `x-mega-menu`, `x-mobile-nav` | — | Alpine (focus-trapped drawer) |
| `x-search-bar` | with suggestions (S) | Alpine, debounced |
| `x-filter-panel` | sidebar (desktop), sheet (mobile), active filter chips | Alpine; GET form |
| `x-sort-select` | — | GET form |
| `x-pagination` | — | Links |
| `x-breadcrumbs` | — | JSON-LD output |
| `x-cart-drawer`, `x-cart-line` | normal, price changed, unavailable | Alpine + fetch |
| `x-order-summary` | checkout (with coupon), confirmation, account | — |
| `x-coupon-field` | idle, applying, applied, error | Alpine + fetch |
| `x-status-timeline` | customer view, admin view (with actors/notes) | — |
| `x-rating-stars`, `x-review-item` | display, input | — |
| `x-alert` / `x-toast` | info, success, warning, error | Auto-dismiss toasts (not for errors) |
| `x-modal` / `x-confirm-dialog` | — | Focus trap, Esc to close |
| `x-empty-state` | icon, title, text, action | — |
| `x-skeleton` | card, line | — |
| `x-banner`, `x-section-heading`, `x-trust-row` | — | — |
| Admin: `x-admin.table`, `x-admin.filters`, `x-admin.stat-card`, `x-admin.form-section`, `x-admin.image-uploader`, `x-admin.variant-matrix` | sortable, bulk select | Alpine |

## 9. States

| Context | Empty | Loading | Error | Success |
|---|---|---|---|---|
| Listing / search | "No products match" + clear filters + popular categories | Skeleton cards (only for client-side updates; pages are server-rendered) | Generic error with retry | — |
| Cart | "Your cart is empty" + Shop now | Line spinner on qty change | Inline line message (stock/price) | Mini-cart opens with "Added" |
| Coupon | — | "Applying…" | Specific reason | "Saved ৳X" |
| Checkout | Redirect to cart if empty | Button "Placing order…" (disabled) | Field errors summary at top with links to fields; change notices | Redirect to confirmation |
| Account orders | "No orders yet" + Shop now | — | — | Toast on cancel |
| Wishlist | "Nothing saved yet" | — | — | Heart fills + toast |
| Admin tables | Empty state with primary action ("Add first product") | Table skeleton for async filters | Inline alert | Toast; row highlight |
| Dashboard | "No orders in this period" (no fake zeros styled as charts) | — | — | — |

Error summary pattern: on submit failure, focus moves to an alert listing errors, each linking to its field.

## 10. Device behaviour

| Feature | Mobile (< 1024) | Desktop (≥ 1024) |
|---|---|---|
| Navigation | Hamburger drawer; search icon expands full-width field | Inline menu with Shop mega-menu; persistent search |
| Product grid | 2 columns (3 at `md`) | 4 columns |
| Filters | Bottom sheet, apply button shows result count | Sidebar, applies on change |
| Product gallery | Swipe carousel with dots | Thumbnails + hover zoom |
| Add to cart | Sticky bottom bar with price | Inline buttons |
| Cart | Full page; drawer on add | Drawer + full page |
| Checkout | Single column; collapsible summary at top; sticky Place-order bar | Two columns; sticky summary right |
| Admin | Off-canvas sidebar; tables become stacked cards for key lists (orders) | Fixed sidebar; full tables |

Tablet (768–1023) uses the mobile patterns with wider grids.

## 11. Accessibility guidelines (WCAG 2.1 AA)

- Contrast per §3.1; focus ring 2 px `--color-link` with 2 px offset on every interactive element (never removed).
- Semantic HTML: one `h1` per page, landmark regions (`header`, `nav`, `main`, `footer`), lists for grids, `button` for actions and `a` for navigation.
- Skip-to-content link as first focusable element.
- Forms: visible labels (no placeholder-only fields), required indicated in text, errors announced (`aria-live="polite"`) and linked via `aria-describedby`, autocomplete attributes (`name`, `tel`, `email`, `street-address`).
- Drawers and dialogs: focus trap, return focus to trigger, Esc closes, `aria-modal`.
- Variant swatches: radio group semantics with visible names; disabled options announced as unavailable.
- Images: meaningful alt text from admin field; decorative images `alt=""`.
- Touch targets ≥ 44×44 px; spacing between adjacent targets ≥ 8 px.
- Respect `prefers-reduced-motion`; no auto-advancing carousels (hero banners rotate only on user action).
- Language attribute set per locale (`lang="en"` / `lang="bn"`).
- Test with keyboard only, NVDA/VoiceOver smoke test, and axe automated checks (DOC-08).

## 12. Content guidelines

- Plain, friendly English; short sentences; avoid jargon ("Cash on Delivery", not "COD" alone on first mention).
- Prices formatted `৳2,790` (no decimals when whole).
- Phone shown as `01XXX-XXXXXX` for readability, stored without separators.
- Demo content is labelled `[Demo]` in admin and seeded only via a demo seeder never run in production.

## Change log

| Version | Date | Author | Change |
|---|---|---|---|
| 0.1 | 2026-10-13 | Claude | Initial draft |
