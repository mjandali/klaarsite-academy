# Klaarsite Academy

MVP Laravel + Vue/Inertia academy platform for selling and delivering programming courses.

## What is included

- Arabic/English UI structure with Arabic as the default language.
- Public pages: home, courses, course details, about, contact, privacy, terms.
- Admin dashboard.
- Course CRUD.
- Section CRUD.
- Lesson CRUD with video/text/mixed lesson content.
- Lesson attachments upload and protected download.
- Student dashboard and “My Courses”.
- Learning page with curriculum sidebar.
- Mark lesson as completed.
- Course progress calculation.
- Orders/payments tables.
- Stripe Checkout integration using Laravel HTTP client.
- Local test-payment fallback when Stripe keys are not configured and `PAYMENT_TEST_MODE=true`.

## Default language

Arabic is the default language.

Switch language:

- `/language/ar`
- `/language/en`

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate:fresh --seed
npm run dev
php artisan serve
```

Default seeded admin:

```text
Email: mjandaline@gmail.com
Password: password
```

## Payments

For local development without Stripe keys, keep:

```env
PAYMENT_TEST_MODE=true
```

For real Stripe Checkout, set:

```env
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
PAYMENT_TEST_MODE=false
```

Webhook endpoint:

```text
/stripe/webhook
```

## Production notes

Before production launch:

1. Replace Privacy/Terms text with proper legal content.
2. Set real Stripe keys.
3. Set `APP_ENV=production` and `APP_DEBUG=false`.
4. Configure real database credentials.
5. Configure mail sending.
6. Run `php artisan storage:link`.
7. Verify payment webhook with Stripe CLI or dashboard.

## Verification done in this handoff

- PHP syntax check passed for all PHP files.
- Laravel route list generated successfully.
- Frontend production build succeeded with Vite.

Note: full database migrations/tests could not be executed in the current container because its PHP build does not include a database PDO driver such as SQLite/MySQL, and some artisan output commands require the PHP DOM extension. Run `php artisan migrate:fresh --seed` in your local PHP environment.

# Run deploy command:
sudo -iu deploy

# then
/home/deploy/bin/academy-deploy.sh

# or directly from root:
sudo -u deploy /home/deploy/bin/academy-deploy.sh

# deploy script path:
/home/deploy/bin/academy-deploy.sh

# project folder:
/home/deploy/apps/academy