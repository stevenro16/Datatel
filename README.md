# DataTel

A B2B service-management portal for a data-communications and cabling company. It combines a public marketing site with three role-gated portals:

- **Public site** — service listings, contact form, quote/account requests.
- **Customer portal** — submit and edit work orders, track progress, sign invoices.
- **Employee portal** — calendar of assignments, time tracking, on-site completion signatures.
- **Admin portal** — full work-order management, dispatching, scheduling, invoicing, analytics, and configuration.

## Tech stack

| Layer | Choice |
|-------|--------|
| Framework | Laravel 12 (PHP 8.3) |
| Database | SQLite (local dev) / MySQL 8.0 (production) |
| Auth | Laravel Breeze (Blade) + custom `RoleMiddleware` |
| PDF | barryvdh/laravel-dompdf |
| Image/signature | intervention/image |
| SMS | twilio/sdk (config-gated, off by default) |
| Frontend | Blade + Vite + Tailwind |

## Local development

No Docker — local dev uses `php artisan serve` + SQLite.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve            # http://localhost:8000
```

For the full dev stack (server + queue + logs + Vite) in one command:

```bash
composer dev
```

### Database

- **Local:** SQLite (`DB_CONNECTION=sqlite`); the file lives at `database/database.sqlite` (gitignored).
- **Production:** MySQL 8.0 — set `DB_*` in `.env` on the server.

After a schema change, rebuild with `php artisan migrate:fresh --seed`.

### Default seeded admin

`admin@datatel.local` — password from `ADMIN_SEED_PASSWORD` in `.env`. Outside `local`/`testing` the seeder requires that variable to be set (it will not fall back to a default).

## Testing

```bash
php artisan test
```

The suite runs on in-memory SQLite (`phpunit.xml`) and covers role-based authorization, work-order completion, invoice math, and mass-assignment guards. CI (`.github/workflows/ci.yml`) runs the suite plus a Pint style check on every push and PR.

## Deployment

See [`DEPLOY.md`](DEPLOY.md) for the production (GoDaddy/MySQL) checklist.

## Further docs

- [`CLAUDE.md`](CLAUDE.md) — architecture, conventions, and patterns (read this before making changes).
- `DataTel_Website_Specification.docx` — original functional spec.
