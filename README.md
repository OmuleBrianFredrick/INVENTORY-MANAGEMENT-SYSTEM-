# Advanced Inventory Management System

A Laravel 12 inventory platform rebuilt from the reference Inventorysystem project and extended with mandatory email OTP authentication, role-aware user management, a cream business UI, auditable stock movements, suppliers, categories, procurement/receiving, customers, sales orders, inventory alerts, reporting/valuation, returns/refunds, barcode support and a gateway-ready payment layer.

## Functional scope completed before hardening

- Laravel 12 / PHP 8.2+
- Mandatory email OTP for inventory managers/administrators after password validation; ordinary staff use password authentication without OTP
- OTP hashing, expiry, one-time verification, attempt limits and resend cooldown
- Authentication event logging
- Product CRUD with SKU, barcode, category, cost/selling price, images, stock and reorder levels
- Barcode-aware product search
- Auditable stock-in, stock-out, opening, adjustment and return movements
- Supplier and category management
- Purchase orders and partial/full goods receiving
- Customer records and sales orders
- Atomic stock deduction for sales
- Payment and delivery status tracking
- Transaction-safe payment records with provider/method/reference fields
- Returns/refunds workflow that restores returned stock
- Inventory alert centre with unread/read state
- Management reporting and inventory valuation
- Inventory value by category and stock movement summaries
- Administrator, manager and staff roles
- Administrator user management
- Cream, charcoal and bronze visual theme
- PHPUnit feature coverage for core business rules
- GitHub Actions CI workflow

## Environment configuration

The repository contains a complete **`.env.example` template**. It includes:

- XAMPP/MySQL connection placeholders
- local application settings
- mock SMTP configuration for OTP email
- OTP security settings
- first-install administrator placeholders
- mock payment/SMS integration placeholders

No real secrets are committed. `.env` is explicitly ignored by Git.

### Example XAMPP configuration

The intended local database configuration is:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_management_system
DB_USERNAME=root
DB_PASSWORD=
```

Adjust `DB_USERNAME` and `DB_PASSWORD` if your XAMPP/MySQL installation uses different credentials.

### Example SMTP configuration

`.env.example` contains deliberately fake SMTP values such as `smtp.example.com`. These are **configuration placeholders, not working credentials**. Replace them locally with your SMTP provider's host, port, username, password and encryption settings before testing real OTP delivery.

For a development environment where you do not want to configure SMTP yet, use:

```env
MAIL_MAILER=log
```

Laravel will write outgoing mail to the application log instead of attempting delivery.

## Local XAMPP setup

After cloning the repository:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

Then create a MySQL database in XAMPP/phpMyAdmin named `inventory_management_system` (or use another name and update `DB_DATABASE`).

**Database migration is intentionally a local integration step.** Run:

```bash
php artisan migrate --seed
```

The repository already contains the complete migration definitions for the application tables; the command above creates those tables in your local XAMPP database.

Then run:

```bash
php artisan test
php artisan serve
```

If you are using the XAMPP Apache/PHP stack instead of `php artisan serve`, point the web server at the Laravel application's `public` directory and keep the same `.env` database configuration.

## Reporting and valuation

Reports distinguish cost value, retail value and potential gross margin, and summarize low/out-of-stock products, sales, purchases, stock movements and valuation by category.

## Returns and refunds

Returns are tied to sales orders and order items. The system validates remaining returnable quantities, restores returned goods inside a transaction and records a `RETURN` stock movement. Refund status is explicit and is ready to be connected to the payment layer.

## Barcode support

Products have an optional unique barcode in addition to SKU. Product creation/editing accepts barcodes and inventory search matches product name, SKU or barcode. This provides the data layer required for USB/Bluetooth scanner workflows without tying the application to one hardware vendor.

## Payment layer

The application has a transaction-safe payment record and `PaymentService`. It records provider, method, reference, amount, status and payment time, prevents overpayment, serializes concurrent payment updates per order, and automatically updates an order from `unpaid` to `partial` or `paid`. The current provider is intentionally manual/gateway-ready; real provider credentials and webhook contracts belong in final deployment configuration rather than source code.

## Authentication flow

### Manager / administrator

1. User submits email and password.
2. Credentials and account status are validated.
3. A six-digit OTP is generated and stored only as a hash.
4. The OTP is emailed to the registered address.
5. The authentication event is logged.
6. The user must enter the OTP before Laravel authenticates the manager session.

Manager login attempts are also rate-limited by email/IP.

### Ordinary staff

1. User submits email and password.
2. Credentials and account status are validated.
3. The user is authenticated directly without a manager OTP challenge.

## Roles

- **Administrator:** full platform and user-management access.
- **Manager:** inventory, suppliers, categories, procurement and sales-management access.
- **Staff:** authenticated platform access without management privileges by default.

Administrator management also prevents the last active administrator from being removed and prevents an administrator from accidentally removing their own administrator access.

## Security hardening

- Baseline security response headers: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, and HSTS on HTTPS responses.
- Login brute-force throttling through Laravel's rate limiter.
- OTP remains hashed and time-limited.
- Manager-only OTP enforcement is covered by feature tests.
- CSRF protection remains provided by Laravel's web middleware.
- Real credentials remain outside Git via `.env` and `.gitignore`.
- Payment updates use row locking to prevent concurrent overpayment races.

## CI and automated verification

GitHub Actions uses SQLite independently of the local XAMPP/MySQL configuration. The pipeline validates Composer configuration, installs dependencies, lints PHP source, boots the application, runs migrations, verifies routes, executes PHPUnit and uploads diagnostic logs as an artifact even when a job fails.

This separation is intentional: **CI validates the repository; your local XAMPP environment validates MySQL integration.**

## Implementation log

### Phase — First-class categories

- Added normalized categories and product relationship.
- Added manager category creation/archive workflow.
- Preserved legacy category text for compatibility.

### Phase — Procurement and receiving

- Added supplier-linked purchase orders.
- Added draft → ordered → partial/received lifecycle.
- Added transaction-safe receiving and stock ledger entries.

### Phase — Customers and sales

- Added customer directory.
- Added sales orders and order items.
- Added atomic stock deduction and `STOCK_OUT` ledger entries.
- Added payment and delivery states.
- Fixed the sales-order item form so users add only the products they actually intend to sell.

### Phase — Inventory alerts

- Added persistent manager/admin inventory alerts.
- Added low-stock alert generation.
- Added alert centre and read state.

### Phase — Reporting and valuation

- Added management report controller and dashboard.
- Added cost, retail and potential-margin valuation.
- Added category valuation and movement summaries.
- Added sales/purchase totals and stock-health indicators.

### Phase — Returns, refunds and barcode

- Added return and return-item tables.
- Added return processing with remaining-quantity validation.
- Restored returned goods into stock and recorded `RETURN` movements.
- Added unique product barcode and barcode-aware search.
- Added barcode inputs to product creation/editing.

### Phase — Payment foundation

- Added payment records linked to sales orders.
- Added transaction-safe payment service.
- Added partial/full payment state updates.
- Added manager payment recording endpoint.
- Added row locking for concurrent payment safety.
- Kept provider credentials and external webhook configuration out of source code.

### Phase — Hardening and UI polish

- Added deterministic XAMPP/MySQL environment template.
- Added mock SMTP/payment/SMS placeholders without committing secrets.
- Added stricter CI diagnostics and PHPUnit configuration.
- Removed an obsolete Composer CLI option from CI.
- Added PHP source linting, route verification and failure diagnostics artifacts.
- Restricted OTP to managers/administrators according to the application security requirement.
- Added login throttling and administrator-continuity protections.
- Added baseline HTTP security headers.
- Improved responsive cream UI, keyboard focus states, mobile layouts, tables and action controls.
- Added security regression tests.

## Repository-side completion boundary

The repository-side implementation includes the application code, models, controllers, routes, migrations, tests, CI, UI and configuration templates. **Local XAMPP database creation/migration, real SMTP credentials, real payment credentials, real SMS credentials and local machine integration remain intentionally outside this repository-side execution boundary.**

## Reference

The original `kisamac1/Inventorysystem` repository was used only as the functional reference. This repository is the independent rebuilt implementation and intentionally improves architectural weaknesses identified during the review.
