# Advanced Inventory Management System

A Laravel 12 inventory platform rebuilt from the reference Inventorysystem project and extended with mandatory email OTP authentication, role-aware user management, a cream business UI, auditable stock movements, security logs, supplier management and controlled product categories.

## Core features

- Laravel 12 / PHP 8.2+
- Email + password sign-in followed by a mandatory six-digit email OTP
- OTP hashing, expiry, one-time verification, attempt limits and resend cooldown
- Authentication event logging for login attempts, OTP sent/resent, verification and logout
- Product CRUD with SKU, controlled category, cost/selling price, images, stock and reorder levels
- Stock-in / stock-out operations with an auditable stock movement ledger
- Low-stock indicators
- Supplier directory with search, contacts, tax number, status, notes and archive workflow
- First-class category directory with active/archive workflow and product relationships
- Administrator, manager and staff roles
- Administrator-only user management with role/status/password editing
- Cream, charcoal and bronze visual theme replacing the reference blue interface
- Laravel migrations and PHPUnit feature tests
- GitHub Actions CI with SQLite database initialization and migration before tests

## Category architecture

Products now reference a normalized `categories` table through `products.category_id`. The legacy `products.category` text field remains populated with the selected category name so existing data and reporting do not break during the transition.

Default categories are seeded for a fresh installation: General, Electronics, Home & Office, Fashion, and Food & Beverage. Managers can create and archive categories from the Categories directory.

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
- **Manager**: product, stock, supplier and category-management access.
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

## Implementation log

### Phase — First-class categories

- Added `categories` migration with unique names, descriptions, active status and soft deletion.
- Added `Category` model and product relationship.
- Added manager-only category directory, creation and archive workflow.
- Added `products.category_id` foreign key while preserving the legacy category name.
- Changed product create/edit flows to require an active controlled category.
- Added default category seeding for fresh databases.
- Updated sidebar navigation and this README.

## Build notes

The reference repository was used as a functional baseline for authentication, product management, images, stock operations and Laravel project conventions. This repository deliberately improves the weak points identified during the review instead of copying them unchanged.

## Roadmap

- Purchase orders and receiving
- Sales/orders and customer accounts
- Delivery/status notifications beyond authentication
- Richer reporting and inventory valuation
- Barcode support
- Production deployment hardening and green CI
