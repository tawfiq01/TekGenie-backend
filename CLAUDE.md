# Project memory

This is a Laravel 13 e-commerce MVP for Bangladesh (working name **[Store Name]**). Full specs live in `docs/` — read `docs/00-document-index.md` first; it indexes the BRD, SRS, use cases, UI/UX spec, architecture (SAD), database design, project plan, test strategy, API spec and RTM. Those documents are authoritative; this file only orients you to them.

## Stack and conventions

- Laravel 13, PHP 8.3+, MySQL 8 / MariaDB 10.6+, Blade + Tailwind CSS v4 + Alpine.js, Vite.
- No public API, no Redis, no Node.js at runtime — database queue + cron scheduler only (see `docs/05-software-architecture.md`).
- Domain logic belongs in `app/Services/<Module>`; controllers stay thin.
- Money: `DECIMAL(12,2)`, integer-poisha arithmetic inside `PricingService` — never floats.
- Every business rule, validation rule and error case has an ID (`BR-*`, `FR-*`, `VR-*`, `EH-*`) — cite it in commits/PRs when relevant, and check `docs/11-requirement-traceability-matrix.csv` before marking anything done.
- Code style: Pint (`./vendor/bin/pint`), static analysis: Larastan level 6 (`./vendor/bin/phpstan analyse`), tests: Pest against a real MySQL database (not SQLite — see `docs/08-test-strategy-and-test-cases.md` §3).
- Run `composer require` with `--dev` for anything test/tooling-only; every new dependency needs an ADR entry in `docs/05-software-architecture.md` §12 (NFR-MNT-03).

## Local setup

- `.env` points at a local MySQL database `tekgenie` (test suite uses `tekgenie_test`, configured in `phpunit.xml`).
- `php artisan migrate:fresh --seed` rebuilds schema + seed data (geography, roles/permissions, settings, pages, demo catalogue outside production).
- `php artisan test` or `./vendor/bin/pest` runs the suite.
