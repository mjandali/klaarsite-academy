# Deployment Notes

This repository is prepared for local development by default. Keep local secrets in `.env` only and never commit that file.

## Production environment

Set these values on the server before going live:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
PAYMENT_TEST_MODE=false
```

## Required deployment steps

1. Create a real production `.env` on the server. Do not copy local secrets into the repository.
2. Configure the production database, mail settings, Stripe keys, and `STRIPE_WEBHOOK_SECRET`.
3. Install PHP dependencies with `composer install --no-dev --optimize-autoloader`.
4. Install frontend dependencies with `npm install` and build assets with `npm run build`.
5. Run database migrations with `php artisan migrate --force`.
6. Link public storage if needed with `php artisan storage:link`.
7. Point the Stripe webhook endpoint to `/stripe/webhook`.

## Repository hygiene

- `.env` is ignored by Git and should remain untracked.
- `node_modules` and `vendor` should stay outside source-control archives and repository commits.
