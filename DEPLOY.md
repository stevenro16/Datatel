# Production Deploy Checklist (GoDaddy / shared hosting)

Run through this list on **every** deploy. The failure modes below have all happened at least once.

## Before pulling

- [ ] CI is green on `master` (tests + pint).
- [ ] No pending migrations that drop/rename columns without a backup taken first.

## Pull & install

```powershell
git pull
composer install --no-dev --optimize-autoloader   # vendor/ is NOT committed — skipping this causes a fatal error on every request
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Verify after every deploy

- [ ] `.env` on the server has `APP_ENV=production` and `APP_DEBUG=false`.
- [ ] `https://<site>/error_log` returns **403/404** (blocked by `public/.htaccess`). Shared hosting writes PHP errors to `error_log` in the web root; if this file is ever downloadable, server paths and stack traces leak.
- [ ] Delete any `error_log` files that accumulated in `public/`: they are gitignored but still occupy the web root.
- [ ] Log in works for each role (customer / employee / admin).

## One-time / occasional

- [ ] The seeded admin account (`admin@datatel.local`) must NOT have the dev default password in production. Seeding outside local/testing requires `ADMIN_SEED_PASSWORD` in `.env` (the seeder throws otherwise).
- [ ] `ORS_API_KEY` set in `.env` if travel-time estimates are wanted (see `.env.example`).
- [ ] After changing `.env`: `php artisan config:cache` again.
