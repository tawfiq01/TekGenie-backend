# DOC-08 — Test Strategy & Test Cases

| Field | Value |
|---|---|
| Project | Modern E-Commerce Website (Bangladesh) — working name **[Store Name]** |
| Document ID | DOC-08 |
| Version | 0.1 (Draft) |
| Date | 2026-10-13 |
| Prepared by | Claude (acting QA Engineer) |
| Depends on | DOC-02 SRS v0.2, DOC-03 v0.1, DOC-05 v0.1 |
| Companion | `08-test-cases.csv` — authoritative list of 173 test cases with execution columns |
| Status | Draft — awaiting review |

---

## 1. Objectives

1. Prove every Must (M) and Should (S) requirement in the SRS works as specified (traced in DOC-11).
2. Prove money and stock can never be wrong: totals, discounts, delivery charges, oversell, duplicate orders.
3. Prove customers and staff can only reach what they are allowed to.
4. Prove the storefront is fast, accessible and usable on mid-range phones.
5. Prove the system installs and runs on the target shared cPanel host.

## 2. Scope

| In scope | Out of scope |
|---|---|
| All MVP functional requirements (FR-*), validation rules (VR-*), error handling (EH-*), non-functional requirements (NFR-*) | Future integrations (payment gateways, courier APIs, SMS) — contract tests only when built |
| Storefront, customer account, admin panel, scheduled jobs, emails | Penetration testing by a third party (recommended before launch, see §11) |
| Staging deployment on the target hosting type | Hosting provider's infrastructure SLAs |

## 3. Test levels and approach

| Level | What | Tool | Who | When |
|---|---|---|---|---|
| Unit | Pure logic: `PricingService`, coupon rules, zone resolution, BD phone normaliser, order-status state machine, order number generator | Pest | Developers | With each change |
| Feature (integration) | HTTP + database: controllers, validation, policies, transactions, emails (Mail fake), queues | Pest + `RefreshDatabase` on MySQL 8 (not SQLite — locking must match production) | Developers | With each change; CI on every push |
| Concurrency | Simultaneous orders, cancellations, coupon last-slot | Pest test spawning parallel PHP processes against a real MySQL instance | Developers + QA | CI nightly and before release |
| Browser / end-to-end | Critical journeys in a real browser: guest checkout, variant picker, filters, admin order processing | Laravel Dusk *or* Playwright (decided at setup; Dusk keeps the stack PHP-only) | QA | Each sprint, before release |
| Accessibility | axe-core automated scan + manual keyboard and screen-reader checks | axe, NVDA, VoiceOver | QA | Each sprint for changed pages; full pass before UAT |
| Performance | Lighthouse CI (mobile, throttled), k6 load test on staging | Lighthouse CI, k6 | QA / DevOps | Before UAT and before launch |
| Security | Automated checks in tests + checklist (§9) + dependency and secret scans | Pest, composer/npm audit, gitleaks, OWASP ZAP baseline | QA / DevOps | CI + pre-launch |
| UAT | Business scenarios by store staff (§10) | Manual, scripts in this document | Product Owner + staff | After system test exit |

Test design techniques: equivalence partitioning and boundary values (prices, quantities, coupon caps, thresholds, phone formats), state-transition testing (order status), decision tables (coupon eligibility, delivery charge), error guessing (tampering, replays), and pairwise for filter combinations.

## 4. Test environments

| Environment | Purpose | Configuration |
|---|---|---|
| Local | Developer tests | PHP 8.3+, MySQL 8 (Docker or Herd), Mailpit for email |
| CI (GitHub Actions) | Automated suite on every push / PR | PHP 8.3 matrix, MySQL 8 service, MariaDB 10.6 nightly, Node for Vite build, Lighthouse CI and axe on built preview |
| Staging | System test, performance, UAT | Same hosting type and PHP/MySQL versions as production (cPanel); `APP_ENV=staging`, `APP_DEBUG=false`, HTTP basic auth, `noindex`; SMTP to a sandbox inbox |
| Production | Post-deployment smoke only | Smoke checklist (§12); no test orders except one flagged order cancelled immediately, agreed with the business |

**Devices for manual testing:** one mid-range Android (Chrome, Samsung Internet), one iPhone (Safari), Windows laptop (Chrome, Edge, Firefox), Mac (Safari).

## 5. Test data

| Data | Source |
|---|---|
| Geography, roles, settings | Production seeders (same as live) |
| Catalogue | `DemoCatalogSeeder` — labelled `[Demo]`, includes edge cases: product without options, product with 2 option types, out-of-stock variant, low-stock variant, compare-at price equal to price, Bangla product name |
| Customers & staff | Factories; one user per default role; customers with/without orders |
| Coupons | Percentage with cap, fixed, free delivery, expired, future, limit 1, per-customer 1, category-restricted |
| Delivery zones | Inside Dhaka ৳60 free ≥ ৳5,000; Dhaka suburbs ৳100; Outside Dhaka ৳130; one district unassigned (test values only) |
| Phone numbers | Valid: `01712345678`, `+8801812345678`, `8801912345678`. Invalid: `0123456789`, `01212345678`, `0171234567`, `01712345678 9` |

No real customer data is ever copied into test environments.

## 6. Entry and exit criteria

**System test entry:** feature complete for the release scope; CI green; build deployed to staging; seeders run; test data loaded.

**System test exit:**
- 100% of Critical and High priority test cases executed; ≥ 95% of all cases executed.
- 100% of Critical and High cases passed.
- No open defects of severity S1 or S2; S3 open defects have agreed workarounds; S4 logged for backlog.
- Concurrency suite (TC-INV-003/004/005, TC-CHK-010, TC-PRM-007) passed 3 consecutive runs.
- Lighthouse and axe gates met (TC-PRF-001, TC-UI-004).
- Security checklist (§9) complete with no open high findings.
- RTM (DOC-11) shows every M requirement with at least one passed test.

**Defect severity:**

| Severity | Definition | Example |
|---|---|---|
| S1 Critical | Wrong money/stock, data exposure, checkout down, security breach | Two orders sold the last unit; customer sees another's order |
| S2 High | Core feature broken without workaround | Staff cannot confirm orders |
| S3 Medium | Feature impaired with workaround | Filter chip doesn't clear but "Clear all" works |
| S4 Low | Cosmetic, copy | Misaligned icon |

## 7. Test case inventory

All 173 cases are in `08-test-cases.csv` with ID, requirement IDs, preconditions, steps, data, expected result, automation approach and regression flag, plus empty execution columns (status, tester, date, actual result/defect).

| Module | Cases | Notable coverage |
|---|---|---|
| Catalogue (CAT) | 13 | Variants, default variant, SKU and combination uniqueness, compare-at rule, image pipeline, visibility |
| Search (SRC) | 7 | SKU-first ranking, URL-persisted filters, sort correctness, injection safety |
| Storefront (STF) | 10 | Home sections, best-seller rules (no fake counts), sanitised pages, dead-link crawl |
| Cart (CRT) | 9 | Merge on login, stock/line caps, price-change notice, tampered price |
| Checkout (CHK) | 16 | Guest and customer COD, BD phone rules, zone charges, change detection, idempotency, owner-only confirmation, rate limits |
| Orders (ORD) | 18 | Snapshot, state machine (valid/invalid/terminal), returns with restock/write-off, refunds, IDOR |
| Inventory (INV) | 10 | Non-negative stock, ledger, last-unit race, deadlock resistance, exactly-once restore |
| Account (ACC) | 11 | Registration, login by email/phone, throttling, reset links, addresses IDOR, guest tracking |
| Wishlist & Reviews (WSH/REV) | 7 | Verified-buyer reviews, moderation, no seeded reviews |
| Promotions (PRM) | 8 | Coupon types, caps, rejection reasons, usage release, last-slot race |
| Shipping (SHP) | 4 | Seed data, area override, unassigned districts |
| Payments (PAY) | 4 | Interface contract, COD settings, collection variance, future gateways locked |
| Notifications (NTF) | 3 | After-commit emails, toggles |
| Admin & access (ADM) | 7 | Separate guard, full permission matrix, last Super Admin protection, 2FA |
| Reports (RPT) | 4 | Reconciliation to reference SQL, CSV export safety |
| Settings & SEO (SET) | 7 | Meta, sitemap, robots, 301 on slug change, JSON-LD |
| Audit (AUD) | 2 | Coverage of sensitive actions, append-only |
| Security (SEC) | 12 | CSRF, mass assignment, upload attacks, headers, debug, cookies, secrets, dependencies |
| Performance & reliability (PRF/REL) | 6 | LCP, TTFB, N+1, load, backup/restore, idempotent jobs |
| UI, accessibility, compatibility (UI) | 9 | Breakpoints, mobile checkout, keyboard, axe, screen reader, Bangla, browsers |
| Operations & quality gates (OPS/QA) | 7 | cPanel deployment, monitoring, code standards, coverage, privacy, retention |

By type: Functional 91, Security 33, Negative 22, Concurrency 6, Performance 4, UI 4, Accessibility 3, Reliability 3, Review 3, Regression 2, Compatibility 2. **101 cases are flagged for the regression suite.**

## 8. Critical test designs

### 8.1 Checkout money calculation (decision table)

| # | Subtotal | Coupon | Zone (charge / free ≥) | Discount | Delivery | Total |
|---|---|---|---|---|---|---|
| 1 | ৳4,740 | none | Inside Dhaka ৳60 / ৳5,000 | ৳0 | ৳60 | ৳4,800 |
| 2 | ৳4,740 | 10% cap ৳500 | Inside Dhaka | ৳474 | ৳60 | ৳4,326 |
| 3 | ৳5,100 | none | Inside Dhaka | ৳0 | ৳0 | ৳5,100 |
| 4 | ৳5,400 | 10% cap ৳500 | Inside Dhaka | ৳500 | ৳60 (৳4,900 < ৳5,000 after discount) | ৳4,960 |
| 5 | ৳8,000 | 10% cap ৳500 | Outside Dhaka ৳130 / none | ৳500 | ৳130 | ৳7,630 |
| 6 | ৳1,500 | fixed ৳200 | Outside Dhaka | ৳200 | ৳130 | ৳1,430 |
| 7 | ৳1,500 | free delivery | Outside Dhaka | ৳0 | ৳0 | ৳1,500 |
| 8 | ৳150 | fixed ৳200 | Outside Dhaka | ৳150 (capped at subtotal) | ৳130 | ৳130 |

Implemented as a Pest dataset against `PricingService` (unit) and re-run end-to-end through checkout (feature, TC-CHK-006). Row 4 confirms the free-delivery threshold uses the amount *after* discount, as specified in SRS §6.1.

### 8.2 BD phone normalisation (boundary values)

| Input | Valid? | Stored |
|---|---|---|
| `01712345678` | Yes | `01712345678` |
| `+8801712345678` / `8801712345678` | Yes | `01712345678` |
| `017-1234-5678` (separators) | Yes | `01712345678` |
| `01312345678` (lowest valid operator digit) | Yes | `01312345678` |
| `01212345678` (operator digit 2) | No | — |
| `0171234567` (10 digits) / `017123456789` (12 digits) | No | — |
| `+8811712345678` (wrong country code) | No | — |

### 8.3 Order status transitions (state-transition table)

Every pair of the 7 statuses (49 combinations) is tested as a dataset: the 9 allowed transitions in SRS §6.3 must succeed for an actor with permission and fail for an actor without; the other 40 must fail for every actor. Side effects asserted: stock restore on cancel (once), coupon release on cancel (once), restock/write-off on return.

### 8.4 Concurrency tests

Run against MySQL 8 (and MariaDB 10.6 nightly) with separate processes, not threads in one connection:

| Test | Setup | Assertion |
|---|---|---|
| TC-INV-003 last unit | stock 1, 2 buyers × 20 repetitions | exactly 1 order per repetition, stock never < 0, movement count = orders |
| TC-INV-004 overlapping carts | carts {V1,V2} and {V2,V1}, 50 pairs | all succeed via retry; no unhandled 1213/1205 |
| TC-INV-005 double cancel | 3 parallel cancels | stock restored once |
| TC-CHK-010 duplicate submit | same token × 5 parallel | 1 order; others return it |
| TC-PRM-007 last coupon slot | limit 1, 2 parallel orders | 1 discounted order; other refused |

## 9. Security test checklist (pre-launch)

| # | Check | Test case |
|---|---|---|
| 1 | Passwords hashed, never logged | TC-SEC-010 |
| 2 | Server-side authorisation on all admin routes (role × route matrix) | TC-ADM-004 |
| 3 | Customer data isolation (orders, addresses, confirmation pages) | TC-ORD-018, TC-ACC-008, TC-CHK-012 |
| 4 | CSRF on every state-changing route | TC-SEC-001 |
| 5 | SQL injection and XSS probes on search, forms, rich text | TC-SRC-007, TC-STF-007 |
| 6 | Mass assignment | TC-SEC-002 |
| 7 | File upload abuse | TC-SEC-003 |
| 8 | Rate limits: login, reset, checkout, tracking, contact, search | TC-ACC-003, TC-ACC-004, TC-CHK-014, TC-ACC-010, TC-STF-008 |
| 9 | Session and cookie flags; admin timeout | TC-SEC-006, TC-SEC-007 |
| 10 | Security headers and HTTPS/HSTS | TC-SEC-004 |
| 11 | Debug off, generic error pages | TC-SEC-005 |
| 12 | Secrets scan and dependency audit | TC-SEC-008, TC-SEC-009 |
| 13 | Client-supplied prices/totals ignored | TC-CRT-006 |
| 14 | Audit trail for sensitive actions, append-only | TC-AUD-001, TC-AUD-002 |
| 15 | No card data stored; DB least privilege | TC-SEC-011, TC-SEC-012 |
| 16 | CSV exports safe from formula injection | TC-RPT-004 |
| 17 | OWASP ZAP baseline scan on staging, no high alerts | (scan report attached at sign-off) |

## 10. UAT scenarios

Executed on staging by the business with real-looking (not real) data. Each scenario is a business story covering several requirements.

| ID | Scenario | Performed by | Pass condition |
|---|---|---|---|
| UAT-01 | A first-time visitor on a phone finds a product by category, picks a colour, buys as a guest with COD inside Dhaka | Staff acting as customer | Order received in admin with correct total and address; customer saw clear charges before placing |
| UAT-02 | A registered customer outside Dhaka uses a coupon and a saved address | Staff acting as customer | Correct discount and delivery; order in "My orders" |
| UAT-03 | Order manager works the morning queue: confirms two orders by phone, cancels one (customer declined), packs and ships the rest, prints slips | Order Manager | Stock correct after cancellation; statuses and slips correct |
| UAT-04 | Courier reports one delivered (cash collected short by ৳20) and one refused | Order Manager | Delivered order Paid with variance noted; refused order Returned, item restocked |
| UAT-05 | Customer returns a damaged item after delivery and is refunded by bKash (recorded manually) | Order Manager | Return recorded as write-off; refund recorded; payment status Refunded |
| UAT-06 | Catalog manager adds a new product with colour and size variants, images and specs, then receives new stock | Catalog Manager | Product live with correct variants; stock movements show receipt |
| UAT-07 | Content editor schedules a banner, reorders home sections, edits the returns policy, creates a coupon | Content Editor | Changes visible at the right time; coupon works |
| UAT-08 | Super Admin creates a Support user, verifies they cannot change prices or order statuses | Super Admin | Support limited as per role |
| UAT-09 | Store manager reviews the week's sales, low stock and coupon reports and exports CSV | Store Manager | Figures match the week's test orders |
| UAT-10 | Guest tracks an order with order number and phone; tries with a wrong phone | Staff acting as customer | Correct status shown; wrong phone gives generic message |
| UAT-11 | Two staff members try to buy the last unit at the same moment from two phones | Two staff | Only one order succeeds |
| UAT-12 | Delivery zone charges are changed; a customer mid-checkout sees the new charge | Store Manager + staff | Existing orders unchanged; new checkout uses new charge |

**UAT sign-off criteria:** all 12 scenarios passed (or failed only for S4 issues with agreed fixes); no open S1/S2 defects; Product Owner and Store Manager sign the sign-off sheet below.

| Role | Name | Decision (Accept / Accept with conditions / Reject) | Signature | Date |
|---|---|---|---|---|
| Product Owner | | | | |
| Store Manager | | | | |
| QA Lead | | | | |

## 11. Regression strategy

- The **automated regression suite** (all cases flagged `Y` that are automatable) runs in CI on every pull request; merge is blocked on failure.
- Manual regression (flagged `Y`, manual) runs before every production release: guest checkout on a real phone, keyboard pass on checkout, cross-browser smoke, cPanel deployment smoke.
- Every fixed S1/S2 defect gets a new automated test that reproduces it before the fix.
- A third-party penetration test is recommended before launch or within the first month (business decision; not in this plan's effort).

## 12. Production smoke checklist (after each deployment)

1. Home, a category, a product and checkout page load over HTTPS with no console errors.
2. Admin login works; dashboard loads.
3. Search returns results.
4. Add to cart and open checkout (do not place an order unless agreed).
5. `php artisan schedule:list` shows tasks; last scheduler run < 2 minutes ago (System health panel).
6. Queue backlog is zero; no new failed jobs.
7. Error log has no new entries since deployment.
8. `/sitemap.xml` and `/robots.txt` respond.

## 13. Reporting

- Execution status tracked in `08-test-cases.csv` (status, tester, date, defect ID) and summarised weekly: executed / passed / failed / blocked by priority.
- Defects logged in the team's tracker with severity, steps, expected vs actual, environment, screenshots; linked to test case and requirement IDs.
- RTM acceptance status updated from test results at each milestone.

## Change log

| Version | Date | Author | Change |
|---|---|---|---|
| 0.1 | 2026-10-13 | Claude | Initial draft with 173 test cases |
