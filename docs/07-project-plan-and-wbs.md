# DOC-07 — Project Plan & Work Breakdown Structure

| Field | Value |
|---|---|
| Project | Modern E-Commerce Website (Bangladesh) — working name **[Store Name]** |
| Document ID | DOC-07 |
| Version | 0.1 (Draft) |
| Date | 2026-10-13 |
| Prepared by | Claude (acting Project Manager / BA) |
| Depends on | DOC-02 SRS v0.2, DOC-05 SAD v0.1, DOC-06 v0.1, DOC-08 v0.1 |
| Status | Draft — awaiting review |

---

## 1. Purpose

Defines how the MVP will be delivered: the work breakdown, phases and deliverables, dependencies, milestones, roles, effort estimates and risks. Durations are expressed in **relative weeks from project start (W1 = first week after the documentation baseline is approved)**. No calendar dates are committed until the start date, team and hosting are confirmed.

## 2. Estimating assumptions

| ID | Assumption |
|---|---|
| EA-01 | Scope = all **Must** and **Should** requirements in SRS v0.2. Could (C) and Future (F) items are excluded. |
| EA-02 | Team: Senior Laravel Developer 1.0 FTE, Laravel Developer 1.0 FTE, Front-end/UI Developer 0.5 FTE, QA Engineer 0.5 FTE (1.0 FTE during system test and UAT), Business Analyst / Project Manager 0.3 FTE, DevOps 0.1 FTE. |
| EA-03 | A person-day (PD) = 6 productive hours. Estimates include unit/feature tests written by developers and code review. |
| EA-04 | Developers are experienced with Laravel, Blade, Tailwind and MySQL; no framework learning time included. |
| EA-05 | The approved UI direction (DOC-04 + prototype) does not change substantially after W2. |
| EA-06 | Hosting (cPanel, PHP 8.3+, MySQL 8/MariaDB 10.6+, cron, SSL, SMTP) is available by W2, ideally with SSH and Composer. Without SSH, add ~2 PD for an FTP/CI-artefact deployment path. |
| EA-07 | Business answers open questions (BRD §12) and supplies brand assets by W2, delivery charges by W8, and real catalogue data and policy texts by W14. |
| EA-08 | Business staff are available for UAT for 2 weeks (W16–W17), at least 2 hours/day. |
| EA-09 | One review cycle per document and one UI revision round are included; further rounds go through change control. |
| EA-10 | Estimates are three-point (low / likely / high); expected = (low + 4 × likely + high) / 6. |

## 3. Work Breakdown Structure and effort

| WBS | Work package | Lead role | Low | Likely | High | Expected |
|---|---|---|---|---|---|---|
| **1** | **Project management & documentation** | | | | | |
| 1.1 | Complete remaining docs (DOC-10, DOC-12, ADR files) and keep docs in sync | BA | 4 | 6 | 9 | 6.2 |
| 1.2 | Planning, stand-ups, sprint reviews, status reporting, change control | PM | 8 | 12 | 16 | 12.0 |
| **2** | **Setup & foundation** | | | | | |
| 2.1 | Repository, Laravel 13 install, environment config, CI pipeline, Pint/Larastan/Pest | Senior Dev | 2 | 3 | 4 | 3.0 |
| 2.2 | Design tokens, Tailwind/Vite setup, base layouts, Blade component library v1 | Front-end | 4 | 6 | 8 | 6.0 |
| 2.3 | Staging cPanel account, first deployment, cron and queue proof (early risk check) | DevOps | 1 | 2 | 4 | 2.2 |
| **3** | **Data model & seed data** | | | | | |
| 3.1 | All migrations, models, relationships, enums, factories | Senior Dev | 3 | 4 | 6 | 4.2 |
| 3.2 | Seeders: geography, roles/permissions, settings, pages, demo catalogue | Developer | 2 | 3 | 4 | 3.0 |
| **4** | **Authentication, authorisation & audit** | | | | | |
| 4.1 | Customer auth (Fortify) with custom Blade views, throttling, email verification | Developer | 3 | 4 | 6 | 4.2 |
| 4.2 | Admin guard and login, staff & role management, permission middleware, matrix tests | Senior Dev | 4 | 5 | 7 | 5.2 |
| 4.3 | Audit logger and audit log screen | Developer | 1.5 | 2 | 3 | 2.1 |
| **5** | **Admin shell & catalogue** | | | | | |
| 5.1 | Admin layout, navigation by permission, table/filter/form components | Front-end | 3 | 4 | 5 | 4.0 |
| 5.2 | Categories, brands, option types & values | Developer | 2 | 3 | 4 | 3.0 |
| 5.3 | Products, variant matrix, image upload pipeline, specs, related, SEO fields | Senior Dev | 6 | 8 | 11 | 8.2 |
| **6** | **Inventory** | | | | | |
| 6.1 | InventoryService (locking, ledger), adjustments UI, inventory list & history | Senior Dev | 3 | 4 | 6 | 4.2 |
| **7** | **Storefront & search** | | | | | |
| 7.1 | Home page sections, banners, announcement bar, static pages & contact form | Front-end | 4 | 6 | 8 | 6.0 |
| 7.2 | Listing, category pages, filters, sort, pagination, search (FULLTEXT) | Developer | 4 | 6 | 8 | 6.0 |
| 7.3 | Product page: gallery, variant picker, stock states, tabs, related | Front-end | 3 | 5 | 7 | 5.0 |
| **8** | **Cart & checkout** | | | | | |
| 8.1 | CartService, cart page and drawer, guest token, merge on login | Developer | 3 | 4 | 6 | 4.2 |
| 8.2 | Delivery zones admin and zone resolution | Developer | 2 | 3 | 4 | 3.0 |
| 8.3 | Coupons admin and CouponService | Developer | 3 | 4 | 5 | 4.0 |
| 8.4 | PricingService, checkout page, place-order transaction, idempotency, confirmation | Senior Dev | 6 | 8 | 11 | 8.2 |
| 8.5 | Concurrency test suite (parallel processes on MySQL/MariaDB) | Senior Dev | 2 | 3 | 5 | 3.2 |
| **9** | **Orders, payments & notifications** | | | | | |
| 9.1 | Admin order list/detail, state machine, notes, courier fields, packing slip | Senior Dev | 5 | 6 | 8 | 6.2 |
| 9.2 | Returns (restock/write-off), refunds, COD collection, payment interface + COD | Senior Dev | 3 | 4 | 6 | 4.2 |
| 9.3 | Emails, database queue, scheduler tasks, failed-job surfacing | Developer | 2 | 3 | 4 | 3.0 |
| **10** | **Customer account** | | | | | |
| 10.1 | Account dashboard, order history/detail, cancel, addresses, profile | Front-end | 3 | 5 | 6 | 4.8 |
| 10.2 | Wishlist, reviews + moderation, guest order tracking | Developer | 4 | 5 | 7 | 5.2 |
| **11** | **Reports & settings** | | | | | |
| 11.1 | Dashboard, sales/product/inventory/coupon reports, CSV export | Developer | 4 | 5 | 7 | 5.2 |
| 11.2 | Store, payment, notification and SEO settings screens | Developer | 2 | 3 | 4 | 3.0 |
| **12** | **SEO & performance** | | | | | |
| 12.1 | Meta tags, sitemap, robots, slug redirects, JSON-LD | Developer | 2 | 3 | 4 | 3.0 |
| 12.2 | Performance pass: caching, image sizes, Lighthouse fixes | Front-end | 2 | 3 | 5 | 3.2 |
| **13** | **Quality assurance** | | | | | |
| 13.1 | Test environment, test data refresh, test-case upkeep | QA | 2 | 3 | 4 | 3.0 |
| 13.2 | System test execution (manual + browser) and defect re-tests | QA | 8 | 12 | 18 | 12.3 |
| 13.3 | Accessibility, cross-browser, security checklist, load test | QA | 4 | 6 | 8 | 6.0 |
| 13.4 | Defect-fix allowance during system test | Senior Dev | 5 | 8 | 12 | 8.2 |
| **14** | **User acceptance testing** | | | | | |
| 14.1 | UAT preparation, support and fixes | QA | 4 | 6 | 10 | 6.3 |
| **15** | **Deployment & handover** | | | | | |
| 15.1 | Production setup, runbook (DOC-10), backups, monitoring, go-live | DevOps | 2 | 3 | 5 | 3.2 |
| 15.2 | Support for loading real catalogue and content | BA | 1 | 2 | 4 | 2.2 |
| 15.3 | Manuals (DOC-12), staff training, handover, 2-week hypercare | BA | 3 | 4 | 6 | 4.2 |
| | **Total (person-days)** | | **129.5** | **186** | **265** | **189.8** |

### 3.1 Effort by role (expected)

| Role | Expected PD | Allocation | Approx. busy weeks |
|---|---|---|---|
| Senior Laravel Developer | 54.5 | 1.0 FTE | 11 |
| Laravel Developer | 48.8 | 1.0 FTE | 10 |
| Front-end/UI Developer | 29.0 | 0.5 FTE | 12 |
| QA Engineer | 27.7 | 0.5 → 1.0 FTE | 8–10 |
| Business Analyst | 12.5 | 0.3 FTE (shared with PM) | — |
| Project Manager | 12.0 | 0.3 FTE (shared with BA) | — |
| DevOps | 5.3 | 0.1 FTE + go-live days | — |
| **Total** | **189.8** | | |

### 3.2 Contingency and confidence

- Expected effort **≈ 190 PD**; the range across the three-point estimates is **130–265 PD**.
- Recommended planning figure: **expected + 15% contingency ≈ 218 PD**, held by the PM and released through change control, not spread across tasks.
- The biggest uncertainties are system-test defect volume (WBS 13.2/13.4), the variant/image admin (5.3), checkout (8.4) and hosting constraints (2.3, 15.1).

## 4. Phases, deliverables and schedule (relative weeks)

```
Week:             1  2  3  4  5  6  7  8  9  10 11 12 13 14 15 16 17 18 19 20
A Foundation      ██ ██
B Admin+catalog         ██ ██ ██ ██
C Store+checkout              ██ ██ ██ ██ ██ ██
D Orders+account                          ██ ██ ██ ██ ██
E System test                                      ░░ ██ ██ ██
F UAT                                                          ██ ██
G Go-live                                                            ★
H Hypercare                                                          ██ ██ ██
QA automation           ░░ ░░ ░░ ░░ ░░ ░░ ░░ ░░ ░░ ░░
(░ = partial / alongside other work; QA automates browser tests at 0.5 FTE during development)
```

| Phase | Weeks | WBS | Key deliverables | Exit milestone |
|---|---|---|---|---|
| A Foundation | W1–W2 | 2, 3, 4.1 (start) | Repo + CI green; tokens and component library v1; all migrations and seeders; staging deployed on cPanel with cron/queue working | **M1** Foundation ready (end W2) |
| B Admin & catalogue | W3–W6 | 4, 5, 6 | Customer and staff auth; roles/permissions; audit log; admin shell; categories, options, products, variants, images; inventory ledger and adjustments | **M2** Catalogue & inventory demo (end W6) |
| C Storefront & checkout | W5–W10 | 7, 8 | Home, listing, search, product page; cart; delivery zones; coupons; checkout with transactional order placement; concurrency suite | **M3** End-to-end order demo (end W10) |
| D Orders, account, reports | W9–W13 | 9, 10, 11, 12 | Order processing, returns, refunds, COD collection; emails/queue; customer account, wishlist, reviews, tracking; reports and settings; SEO and performance pass | **M4** Feature complete (end W13) |
| E System test | W12–W15 | 13 | Executed test cases (DOC-08), defect fixes, accessibility/security/performance reports, updated RTM | **M5** System test exit (end W15) |
| F UAT | W16–W17 | 14 | UAT scenarios executed, fixes, signed UAT sheet | **M6** UAT sign-off (end W17) |
| G Go-live | W18 | 15.1, 15.2 | Production deployment, real catalogue loaded, smoke test, backups and monitoring live | **M7** Go-live (W18) |
| H Hypercare & handover | W18–W20 | 15.3 | Training, DOC-12 manuals, handover pack, defect fixes during hypercare | **M8** Handover complete (end W20) |

**Indicative duration: about 18 weeks to go-live and 20 weeks to handover** with the team in EA-02. With one developer fewer, expect roughly 24–26 weeks to go-live. Overlaps between phases are deliberate (e.g. storefront starts while admin catalogue finishes, because both depend only on the data model).

Each milestone ends with a demo on staging and a short written status (done, not done, risks, decisions needed).

## 5. Dependencies

### 5.1 Technical dependencies (finish → start unless noted)

| Successor | Depends on | Why |
|---|---|---|
| All development | 2.1 Repository & CI | Common baseline |
| 4, 5, 6, 7, 8 | 3.1 Migrations | Data model needed |
| 5.x admin screens | 4.2 Admin auth & permissions, 5.1 admin shell | Screens are permission-gated |
| 7.x storefront pages | 2.2 Component library, 5.3 products (or demo seeder) | Components and data |
| 8.4 Checkout | 8.1 Cart, 8.2 Zones, 8.3 Coupons, 6.1 Inventory | Pricing and stock inputs |
| 8.5 Concurrency tests | 8.4 Checkout | Tests the order transaction |
| 9.1 Order processing | 8.4 Checkout | Orders must exist |
| 9.2 Returns/refunds | 9.1, 6.1 | State machine + ledger |
| 10.2 Reviews | 9.1 (Delivered status) | Verified-buyer rule |
| 11.1 Reports | 9.1, 9.2 | Order and return data |
| 13.2 System test | M4 feature complete (start-to-start overlap from W12 for finished modules) | |
| 14 UAT | M5 | Exit criteria met |
| 15.1 Production | 2.3 staging proven, M6 | Same procedure as staging |

### 5.2 Business dependencies

| ID | Item | Needed by | Blocks |
|---|---|---|---|
| BD-01 | Answers to BRD Q-01…Q-09; brand name, logo, colour | W2 | 2.2 final tokens, 3.2 settings |
| BD-02 | Hosting account and domain (Q-06) | W2 | 2.3, 15.1 |
| BD-03 | Delivery zones and charges (Q-03) | W8 | 8.2 configuration, UAT data |
| BD-04 | Return/warranty policy wording (Q-04), VAT/invoice decision (Q-07) | W9 | 9.1 packing slip/invoice, static pages |
| BD-05 | Real catalogue data, images, opening stock | W14 | 15.2, go-live |
| BD-06 | Policy texts (privacy, terms, returns, shipping) | W14 | Go-live |
| BD-07 | SMTP mailbox / transactional email account | W8 | 9.3 |
| BD-08 | UAT participants and sign-off authority named | W14 | 14 |

## 6. Roles and responsibilities (RACI)

R = Responsible, A = Accountable, C = Consulted, I = Informed.

| Activity | Product Owner | PM/BA | Senior Dev | Developer | Front-end | QA | DevOps |
|---|---|---|---|---|---|---|---|
| Scope, priorities, change approval | A | R | C | I | I | I | I |
| Requirements & documents | A | R | C | C | C | C | C |
| Architecture & ADRs | I | C | A/R | C | C | C | C |
| Database design & migrations | I | I | A/R | R | I | C | I |
| Backend modules | I | I | A | R | C | C | I |
| UI components & storefront pages | C | C | C | C | A/R | C | I |
| Automated tests | I | I | A | R | R | R | I |
| System test & defect triage | I | C | C | C | C | A/R | I |
| Hosting, CI/CD, backups, monitoring | I | I | C | I | I | I | A/R |
| UAT | A/R | R (facilitate) | C | C | C | R (support) | I |
| Go-live decision | A | R | C | I | I | C | C |
| Training & manuals | C | A/R | I | I | I | C | I |

## 7. Ways of working

- Two-week sprints within the phases above; sprint review = milestone demo where applicable.
- Definition of Done per item: code reviewed; Pint/Larastan clean; unit/feature tests added and green in CI; acceptance criteria met on staging; RTM row updated; no new S1/S2 defects; docs updated if behaviour changed.
- Branching: trunk-based with short-lived feature branches and pull requests; `main` always deployable to staging.
- **Change control:** any new requirement or change to an approved document is logged (ID, description, impact on effort/schedule/risk, decision, date). Small changes (≤ 1 PD) can be approved by the PM within contingency; larger changes need Product Owner approval and a document version bump.

## 8. Testing, UAT, deployment and handover activities

| Activity | When | Reference |
|---|---|---|
| Developer tests in CI on every push | W1 onwards | DOC-08 §3 |
| QA automates browser journeys as modules complete | W5 onwards | DOC-08 §3 |
| Concurrency suite nightly | From W10 | DOC-08 §8.4 |
| System test cycle 1, fixes, cycle 2 (regression) | W12–W15 | DOC-08 §6 |
| Performance (Lighthouse, k6) and security checklist on staging | W14–W15 | DOC-08 §9 |
| UAT scenarios UAT-01…12 | W16–W17 | DOC-08 §10 |
| Production deployment dry-run on staging using the runbook | W17 | DOC-10 |
| Go-live, smoke checklist, backups verified | W18 | DOC-08 §12, DOC-10 |
| Staff training (2 × 2 h sessions: operations; catalogue/content) | W18 | DOC-12 |
| Hypercare: daily check of errors/queues/orders, fixes for go-live defects | W18–W20 | — |
| Handover pack: source access, credentials transfer procedure, docs, known limitations, backlog of C/F items | End W20 | DOC-00 |

## 9. Risk register

Probability (P) and impact (I): 1 low – 3 high; score = P × I.

| ID | Risk | P | I | Score | Owner | Mitigation | Contingency / trigger |
|---|---|---|---|---|---|---|---|
| PR-01 | Hosting limits (no SSH, blocked symlinks, old PHP, low memory) | 2 | 3 | 6 | DevOps | Prove deployment on staging in W2 (WBS 2.3); confirm PHP 8.3 | Switch to CI-artefact FTP deploy; recommend different plan/VPS if PHP 8.3 unavailable |
| PR-02 | Late business inputs (brand, charges, catalogue, policies) | 3 | 2 | 6 | PM | Dependency dates in §5.2; weekly reminders | Launch with placeholders only where legally acceptable; otherwise move go-live |
| PR-03 | Overselling or wrong totals in production | 1 | 3 | 3 | Senior Dev | Transactional design, concurrency suite, decision-table tests | Hotfix procedure; manual stock freeze option |
| PR-04 | Scope creep (gateways, couriers, SMS pulled forward) | 2 | 2 | 4 | Product Owner | Future items held in BRD §6.2; change control | Separate phase after go-live |
| PR-05 | Variant/image admin more complex than estimated | 2 | 2 | 4 | Senior Dev | Spike in W3; keep matrix UI simple | Use contingency; defer bulk editing |
| PR-06 | Key developer unavailable | 2 | 3 | 6 | PM | Code reviews spread knowledge; docs and ADRs | Bring in backup developer; re-plan |
| PR-07 | Performance on shared hosting below target | 2 | 2 | 4 | Front-end | Budgets from W2; Lighthouse in CI; image pipeline | Add CDN (e.g. Cloudflare free tier), upgrade hosting |
| PR-08 | Email deliverability problems | 2 | 1 | 2 | DevOps | SPF/DKIM/DMARC in W8; test inboxes | Transactional email provider |
| PR-09 | Fake COD orders after launch | 3 | 2 | 6 | Store Manager | Phone confirmation workflow, rate limits | OTP verification (FS-03) prioritised next |
| PR-10 | UAT staff availability | 2 | 2 | 4 | Product Owner | Book UAT dates at M3 | Extend UAT by one week |
| PR-11 | Security vulnerability found late | 1 | 3 | 3 | Senior Dev | Security tests in CI from start; checklist at W14 | Delay go-live until fixed |
| PR-12 | Legal/tax requirements (VAT invoice, data protection) discovered late | 2 | 2 | 4 | Product Owner | Raise Q-07 early; review policies with advisor | Add invoice format as change request |

## 10. Reporting and governance

| Forum | Frequency | Participants | Output |
|---|---|---|---|
| Stand-up | Daily, 15 min | Delivery team | Blockers |
| Sprint review / milestone demo | Every 2 weeks | Team + Product Owner | Accepted items, feedback |
| Status report | Weekly | PM → Product Owner | Progress vs plan, RAG status, risks, decisions needed |
| Change control | As needed | PM + Product Owner | Change log updates |
| Go/no-go meeting | W17 end | Product Owner, PM, Senior Dev, QA, DevOps | Go-live decision against DOC-10 readiness checklist |

## Change log

| Version | Date | Author | Change |
|---|---|---|---|
| 0.1 | 2026-10-13 | Claude | Initial draft |
