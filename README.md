# Advanced Inventory Management System

A Laravel 12 inventory platform rebuilt from the reference Inventorysystem project and extended with mandatory email OTP authentication, role-aware user management, a cream business UI, auditable stock movements and security logs.

## Core features

- Laravel 12 / PHP 8.2+
- Email + password sign-in followed by a mandatory six-digit email OTP
- OTP hashing, expiry, one-time verification, attempt limits and resend cooldown
- Authentication event logging for login attempts, OTP sent/resent, verification and logout
- Product CRUD with SKU, category, cost/selling price, images, stock and reorder levels
- Stock-in / stock-out operations with an auditable stock movement ledger
- Low-stock indicators
- Administrator, manager and staff roles
- Administrator-only user management with role/status/password editing
- Cream, charcoal and bronze visual theme replacing the reference blue interface
- Laravel migrations and PHPUnit feature tests

## Authentication flow

1. User submits email and password.
2. Credentials and account status are validated.
3. A six-digit OTP is generated and stored only as a hash.
4. The OTP is emailed to the user's registered address.
5. The sign-in event is recorded in `authentication_logs`.
6. The user must enter the OTP before Laravel authenticates the session.
7. Invalid, expired or exhausted OTP challenges cannot grant access.

## Roles

- **Administrator**: full inventory and user-management access.
- **Manager**: product and stock-management access.
- **Staff**: authenticated platform access without management privileges by default.

The first account registered in a fresh database is provisioned as administrator. For deterministic deployments, set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env` and run the database seeder.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
mkdir -p database
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan test
php artisan serve
```

### Real email delivery

The default `.env.example` uses Laravel's `log` mailer so local development does not require an SMTP server. For real OTP delivery, configure `MAIL_MAILER=smtp` and the corresponding SMTP host, port, username, password and from address.

## Security notes

- Never commit `.env` or real SMTP credentials.
- OTP values are not stored in plaintext.
- Keep OTP expiry short and use HTTPS in production.
- Use a real SMTP provider for production email delivery.
- Rotate administrator credentials before deployment.

## Build notes

The reference repository was used as a functional baseline for authentication, product management, images, stock operations and Laravel project conventions. This repository deliberately improves the weak points identified during the review instead of copying them unchanged.

## Roadmap

Next improvements can include supplier management, categories as first-class entities, purchase orders, sales/orders, notifications beyond authentication, richer reporting, barcode support, pagination/search and CI/CD.
