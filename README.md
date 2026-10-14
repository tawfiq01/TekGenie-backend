# [Store Name] — E-Commerce Platform

A mobile-first e-commerce website for Bangladesh, with Cash on Delivery, delivery-zone pricing, and a full admin panel for catalogue, inventory, orders and content. Built with Laravel 13, MySQL and Tailwind CSS, deployable on shared cPanel hosting.

## Documentation

Full requirements, architecture and plan live in [`docs/`](docs/00-document-index.md):

| Doc | Content |
|---|---|
| [00](docs/00-document-index.md) | Document index and version control |
| [01](docs/01-business-requirements.md) | Business Requirements (BRD) |
| [02](docs/02-software-requirements-specification.md) | Software Requirements (SRS) |
| [03](docs/03-use-cases-and-workflows.md) | Use cases & workflows |
| [04](docs/04-ui-ux-specification.md) | UI/UX specification |
| [05](docs/05-software-architecture.md) | Software architecture (SAD) |
| [06](docs/06-database-design.md) | Database design |
| [07](docs/07-project-plan-and-wbs.md) | Project plan & WBS |
| [08](docs/08-test-strategy-and-test-cases.md) | Test strategy (+ [173 test cases](docs/08-test-cases.csv)) |
| [09](docs/09-api-and-integration-specification.md) | API & integration spec |
| [11](docs/11-requirement-traceability-matrix.csv) | Requirement traceability matrix |

## Requirements

- PHP 8.3+, Composer
- MySQL 8.0+ or MariaDB 10.6+
- Node.js 20+ (build only — not required at runtime)

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# point DB_* in .env at a local MySQL database, e.g. `tekgenie`
php artisan migrate --seed
npm run build   # or `npm run dev` while working on front-end
php artisan serve
```

## Quality gates

```bash
./vendor/bin/pint             # code style
./vendor/bin/phpstan analyse  # static analysis (Larastan, level 6)
php artisan test              # Pest, against a real MySQL database
```

All three run in CI on every push (`.github/workflows/ci.yml`).
