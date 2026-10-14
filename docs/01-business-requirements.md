# DOC-01 — Business Requirements Document (BRD)

| Field | Value |
|---|---|
| Project | Modern E-Commerce Website (Bangladesh) — working name **[Store Name]** |
| Document ID | DOC-01 |
| Version | 0.2 (Draft) |
| Date | 2026-10-13 |
| Prepared by | Claude (acting Senior BA) |
| Approver | [Product Owner — to be named] |
| Status | Draft — awaiting review |

---

## 1. Business background

The business intends to sell physical products online to customers in Bangladesh through its own branded website. The initial product line is expected to be consumer goods (the visual reference is a gadget and accessories store), but the business wants the freedom to change or add categories without redevelopment.

In the Bangladeshi online retail market, buyers commonly:

- shop mainly from mobile phones, often on mid-range devices and variable mobile data;
- prefer Cash on Delivery (COD) because they distrust paying in advance to unknown sellers;
- expect a phone call or message to confirm an order before dispatch;
- are sensitive to delivery charges, which usually differ inside and outside Dhaka;
- judge trustworthiness from clear pricing, genuine reviews, visible return policy and reachable support.

## 2. Problem statement

Selling only through social media pages and marketplaces leaves the business with:

- manual order taking through messages, causing errors in price, stock and address;
- no single, reliable view of stock, orders and customers;
- dependence on third-party platforms for reach, rules and fees;
- weak brand identity and limited customer trust;
- no structured data for sales and inventory decisions.

## 3. Business objectives

| ID | Objective |
|---|---|
| BO-01 | Provide a trustworthy, premium-looking, mobile-first storefront that customers can use without assistance. |
| BO-02 | Accept orders online with correct prices, discounts, delivery charges and totals, calculated by the server. |
| BO-03 | Support Cash on Delivery from launch, with a payment design that allows local gateways later. |
| BO-04 | Keep stock accurate and never oversell, including when customers buy at the same moment. |
| BO-05 | Give staff a single admin dashboard for catalog, stock, orders, customers, promotions, content and reports. |
| BO-06 | Allow the product range, categories, delivery charges and site content to be changed by administrators without code changes. |
| BO-07 | Run reliably and securely on affordable shared cPanel hosting. |
| BO-08 | Lay foundations for Bangla language, payment gateways, couriers and notifications without building them now. |

## 4. Expected benefits

- Fewer order-taking errors and less manual effort per order.
- Reduced overselling and stock disputes.
- Higher customer confidence through transparent pricing and policies.
- An owned sales channel and customer base.
- Reliable sales, product and inventory data for decisions.

Quantified targets are set in §11 once the business supplies baseline figures.

## 5. Stakeholders

| Stakeholder | Interest / responsibility |
|---|---|
| Business owner / Product Owner | Sets priorities, approves scope, documents and releases |
| Store manager | Runs day-to-day operations; oversees orders, stock and staff |
| Order / operations staff | Confirms orders by phone, packs and dispatches, records delivery outcome |
| Catalog / inventory staff | Maintains products, images, variants, prices and stock |
| Content / marketing staff | Manages banners, homepage sections, coupons and SEO text |
| Customer support | Answers queries, looks up orders, handles returns |
| Customers (registered) | Browse, buy, track orders, manage addresses, review products |
| Guest customers | Buy without registering, track an order with order number and phone |
| Delivery partner (manual in MVP) | Receives parcels, collects COD cash, reports delivery or return |
| Development, QA and DevOps team | Build, test, deploy and support the system |
| Hosting provider | Provides cPanel environment, PHP, MySQL, email, SSL |

## 6. Business scope

### 6.1 In scope — MVP (initial release)

| Area | Included |
|---|---|
| Storefront | Home page with admin-managed banners and sections; featured, latest and best-selling products; categories and subcategories; product listing with search, filters and sorting; product detail with gallery, variants, stock status, related products; static pages (About, Contact, FAQ, Privacy, Terms, Return/Refund, Shipping) |
| Customer | Registration, login, password reset, profile, multiple addresses, order history, order status tracking, wishlist, review submission |
| Guest | Guest checkout; order tracking by order number + phone |
| Cart & checkout | Cart, coupon codes, delivery charge by zone, transparent totals, COD, duplicate-order protection, order confirmation with unique order number |
| Orders | Order lifecycle with controlled status transitions; payment status tracked separately; cancellations and returns with defined stock effects |
| Inventory | Stock per variant, no negative stock, stock movement history, manual adjustments with reason and user, low-stock list |
| Admin | Dashboard; products, categories, images, variants/SKU; inventory; orders; customers; coupons; banners and homepage content; delivery zones and charges; payment method settings; review moderation; staff users, roles and permissions; reports; site, contact and SEO settings; audit trail |
| Notifications | Email order confirmation (when the customer gives an email) and new-order email to staff |
| SEO | Friendly URLs, editable meta tags, XML sitemap, robots.txt |
| Localization | BDT (৳), Bangladesh address and phone formats, English UI with all text prepared for translation |

### 6.2 Out of scope for MVP (future phases — not to be built until approved)

| ID | Future item |
|---|---|
| FS-01 | Online payment gateways (bKash, Nagad, SSLCommerz or similar) |
| FS-02 | Courier API integrations (e.g. booking, tracking, COD settlement) |
| FS-03 | SMS notifications and OTP login |
| FS-04 | Bangla language content and storefront switcher (UI text is prepared, content translation is not) |
| FS-05 | Advanced analytics, marketing pixels and dashboards beyond MVP reports |
| FS-06 | Loyalty points and referral programmes |
| FS-07 | Multi-vendor marketplace |
| FS-08 | Native mobile apps and a public mobile API |
| FS-09 | Combo/bundle pricing (e.g. "any 3 for X% off"), flash-sale countdown engine |
| FS-10 | Gift cards, store credit, partial advance payment for COD |
| FS-11 | Automated refunds to payment methods |
| FS-12 | Multiple warehouses / stock locations |

### 6.3 Exclusions

- Purchasing, supplier management and accounting/ERP functions.
- Storing card data or any raw payment credentials (never in scope).
- Supplying real product photography, copy or legal policy text — the business provides these; the system ships with clearly labelled demo content only.

## 7. Business rules

| ID | Rule |
|---|---|
| BR-01 | All prices are in Bangladeshi Taka (BDT, ৳). Prices shown are final prices including any applicable VAT (assumption A-06). |
| BR-02 | The server is the only authority for prices, discounts, delivery charges, stock and totals. Values sent by the browser are never trusted. |
| BR-03 | Stock is held per sellable variant. Every product has at least one variant (a "default" variant when it has no options). |
| BR-04 | Stock can never go below zero. An order cannot be placed for more units than are available at the moment of placement. |
| BR-05 | Stock is deducted when an order is placed (not when it is confirmed), so that Pending orders reserve stock. |
| BR-06 | Cancelling an order before it is shipped returns its stock automatically. |
| BR-07 | A returned order's stock is restored only when staff inspect the items and record them as resaleable; damaged items are written off with a stock movement record. |
| BR-08 | Every stock change is recorded as a stock movement with type, quantity, reason, related order (if any), user and time. |
| BR-09 | A customer may cancel their own order only while it is Pending. After that, cancellation is a staff action. |
| BR-10 | Order status changes follow the defined transition rules (SRS §6.3); invalid jumps are refused. |
| BR-11 | Payment status is tracked separately from order status. For COD, payment becomes Paid when staff record cash collection on delivery. |
| BR-12 | One coupon per order in MVP. A coupon must be active, within its dates, under its usage limits and meet its minimum order value. Discount never exceeds the eligible subtotal. |
| BR-13 | Delivery charge is determined by the delivery zone of the shipping address (e.g. Inside Dhaka, Dhaka suburbs, Outside Dhaka), optionally waived above an admin-set order value. |
| BR-14 | A mobile phone number is mandatory for every order and must be a valid Bangladeshi mobile number. Email is optional for guests. |
| BR-15 | Guests can track an order only by giving both the order number and the phone number used on the order. |
| BR-16 | Only customers who have received (Delivered) a product may review it; reviews are published only after staff approval. The system never generates or seeds fake reviews. |
| BR-17 | Discounted prices are shown with the original price only when a genuine, admin-set compare-at price exists. No fabricated discounts or sales counts. |
| BR-18 | Staff can only perform actions allowed by their role. Sensitive actions (price, stock, order status, refunds, roles, settings) are written to the audit trail. |
| BR-19 | Customers can see only their own orders, addresses and personal data. |
| BR-20 | Product categories, delivery zones, charges, payment methods and site content are configurable by administrators. |

## 8. Assumptions

| ID | Assumption | Impact if wrong |
|---|---|---|
| A-01 | A single business sells its own stock from one location (single warehouse). | Multi-location stock needs redesign of inventory. |
| A-02 | COD is the only payment method at launch. | Gateway work moves into MVP. |
| A-03 | Orders are confirmed by staff (phone/message) before processing, as in the reference's "we call to confirm" step. | Auto-confirm would change the order flow. |
| A-04 | Delivery is handled manually with one or more couriers; staff update statuses by hand. | Courier API moves into MVP. |
| A-05 | Initial launch is English only; Bangla content comes later. | Bangla at launch adds translation tables and content effort. |
| A-06 | Prices are VAT-inclusive and no separate VAT line or Mushak invoice is required in MVP. | Tax calculation and invoice format must be added. |
| A-07 | Hosting is shared cPanel with PHP 8.3+ (required by Laravel 13), MySQL 8 / MariaDB 10.6+, cron jobs, SSL, outgoing email, and ideally SSH and Composer access. | Deployment approach changes (see SAD). |
| A-08 | Wishlist and reviews require a customer account; checkout does not. | Minor flow changes. |
| A-09 | Return window and warranty periods are set by the business and shown as policy text; the system records returns but does not enforce warranty claims. | Warranty module needed. |
| A-10 | Expected scale at launch: up to a few thousand products and a few hundred orders per day. | Higher scale may need VPS hosting, caching layer or search engine. |
| A-11 | The business supplies brand name, logo, product data, images and policy texts. | Launch delayed; demo content is clearly marked. |

## 9. Constraints

- Technology: Laravel (PHP), MySQL/MariaDB, Blade, Tailwind CSS, Alpine.js, Vite — as agreed with the business.
- Production must run on shared cPanel hosting without a Node.js runtime, Redis, or long-running processes; front-end assets are built before upload.
- Background work (emails, sitemap) must run via the database queue and cron.
- Must not copy proprietary assets, branding or copy from the visual reference.
- Budget- and hosting-sensitive: avoid paid or heavy dependencies.

## 10. Risks and dependencies

### 10.1 Risks

| ID | Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| R-01 | Shared hosting limits (no SSH, low memory, blocked symlinks, slow disks) | Medium | High | Confirm hosting early (Q-06); deployment guide with no-SSH path; caching and image optimisation |
| R-02 | Overselling under simultaneous orders | Medium | High | Row locking in database transactions; concurrency tests |
| R-03 | Fake/abandoned COD orders | High | Medium | Staff confirmation step; rate limits; phone validation; later OTP (FS-03) |
| R-04 | Scope creep from future items | Medium | Medium | Future items held in FS list until approved; change control |
| R-05 | Late delivery of product data, images and policies | Medium | Medium | Content checklist with owner and date in project plan |
| R-06 | Security breach exposing customer data | Low | High | Security requirements (SRS NFR-SEC), pre-launch security checklist |
| R-07 | Email deliverability on shared hosting | Medium | Low | SPF/DKIM setup; optional SMTP provider |
| R-08 | Poor mobile performance on slow networks | Medium | Medium | Performance budget, image resizing, lazy loading |

### 10.2 Dependencies

- D-01: Hosting account, domain and SSL certificate.
- D-02: Brand assets — name, logo, colour choice, favicon.
- D-03: Product catalogue data, images and stock opening balances.
- D-04: Approved policy texts (privacy, terms, returns, shipping).
- D-05: Delivery zone list and charges.
- D-06: Transactional email account (hosting mail or SMTP service).

## 11. Success metrics

Targets marked [TBD] require a baseline or business decision.

| ID | Metric | Target |
|---|---|---|
| SM-01 | Orders with a price, discount or total error | 0 |
| SM-02 | Oversold order lines | 0 |
| SM-03 | Mobile checkout completion (cart → order placed) | [TBD]% within 3 months |
| SM-04 | Home and listing page load (Largest Contentful Paint, mid-range phone, 4G) | ≤ 2.5 s at the 75th percentile |
| SM-05 | Staff time to process an order from Pending to Shipped in the system | [TBD] minutes |
| SM-06 | Share of orders placed without staff help | [TBD]% |
| SM-07 | Critical/high security findings open at launch | 0 |
| SM-08 | Uptime (subject to hosting SLA) | ≥ 99.5% monthly |

## 12. Open business questions

| ID | Question | Owner | Default if unanswered |
|---|---|---|---|
| Q-01 | Store name, logo and choice of primary colour (reference offers indigo or sky blue / navy) | Product Owner | Neutral working name; restrained sky-blue/navy direction |
| Q-02 | Launch product category and typical variant options (colour, size, model…) | Product Owner | Generic catalogue; colour and size seeded as example options |
| Q-03 | Delivery zones, charges and free-delivery threshold | Product Owner | Inside Dhaka / Dhaka suburbs / Outside Dhaka, amounts left blank for admin |
| Q-04 | Return window, refund method and warranty wording | Product Owner | 7-day return window as editable text |
| Q-05 | Will every order be phone-confirmed, or should some auto-confirm? | Operations | All orders start Pending, staff confirm |
| Q-06 | Hosting provider, PHP version, SSH and Composer access | IT / Owner | No-SSH deployment path documented |
| Q-07 | VAT display and invoice/Mushak requirements | Finance | VAT-inclusive prices, simple printable invoice |
| Q-08 | Staff roles needed at launch | Owner | Roles proposed in SRS §4 |
| Q-09 | Is there an existing domain, email, or product spreadsheet to import? | Owner | Manual entry; CSV import as later enhancement |

## Appendix A — Reference design findings (discovery)

The reference artifact ("TekGenie Website", a two-variant design canvas: *Version 1 · Indigo* and *Version 2 · Sky blue*, each with a Home and Product page at 1440 px) was inspected on 2026-10-13. It is used as visual inspiration only; its brand name, genie theme, slogans and copy belong to its owner and will not be reused.

Observations that inform the design:

- **Palette** — very light backgrounds; Version 2 uses deep navy text (≈`#1B3C52`), sky-blue accent (≈`#7FD6F7`), pale blue surfaces (≈`#E8F7FD`) and a vivid indigo (≈`#3119EB`) sparingly for emphasis. Version 1 leads with indigo (≈`#3518F6`) and cyan.
- **Typography** — one geometric sans family (Outfit), weights 500–800, large fluid hero headings, 14–17 px body.
- **Shape** — pill-shaped buttons and chips, generously rounded cards (≈18–28 px radius), soft navy-tinted shadows.
- **Structure** — announcement bar (free-delivery threshold) → header with Home/Shop/New/Deals and cart → hero with a search-style prompt → category grid with sub-labels → tabbed best sellers → time-limited deal band → featured product spotlight → "how it works" in three steps (order, phone confirmation, delivery) → trust badges (genuine, secure checkout, fast delivery, human support, easy returns) → newsletter → footer with shop, support and contact columns.
- **Product page** — breadcrumb, gallery with discount badge, stock indicator, price with compare-at price and saving, colour swatches, quantity stepper, Add to cart plus Buy now, delivery/warranty/returns assurances, tabbed details/specs/delivery, related products.
- **Localisation cues already present** — Taka pricing, delivery promises split Inside/Outside Dhaka, phone confirmation step. These match the Bangladesh requirements and are kept.
- **Deliberately not carried over** — countdown timers and combo promotions (FS-09) until a real promotion engine exists; placeholder texts such as "[X]h battery" become admin-managed attributes.

## Change log

| Version | Date | Author | Change |
|---|---|---|---|
| 0.1 | 2026-10-13 | Claude | Initial draft |
| 0.2 | 2026-10-13 | Claude | A-07: PHP minimum raised to 8.3 (Laravel 13 requirement) |
