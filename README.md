# UJUZI SHOP MALL

Smart Inventory & Shopping Management

A Laravel 12 inventory and shopping platform rebuilt from the reference Inventorysystem project and extended with mandatory email OTP authentication for privileged accounts, controlled employee account management, customer self-registration, a cream business UI, auditable stock movements, suppliers, categories, procurement/receiving, customers, sales orders, inventory alerts, reporting/valuation, returns/refunds, barcode support and a gateway-ready payment layer.

## Functional scope completed before hardening

- Laravel 12 / PHP 8.2+
- Mandatory email OTP for inventory managers/administrators after password validation; staff and customers use password authentication without OTP
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
- Administrator, manager, staff and customer roles
- Controlled employee account creation: administrators can create managers/staff; managers can create staff
- Public registration is customer-only; public users cannot self-register as employees or administrators
- Administrator user management and last-active-administrator protection
- Cream, charcoal and bronze visual theme
- PHPUnit feature coverage for core business and security rules
- GitHub Actions CI workflow

## Account and role workflow

UJUZI SHOP MALL separates public customers from organization employees.

### Administrator

- Provisioned by the first-install administrator seeder/environment configuration.
- Has full platform and employee-account management access.
- Uses password + email OTP at sign-in.
- Can create managers and staff.
- Can edit employee roles and status while preserving at least one active administrator.

### Manager

- Created by an administrator through Employee Accounts.
- Uses password + email OTP at sign-in.
- Can manage operational inventory, procurement, sales and staff accounts.
- Can create and manage staff accounts, but cannot create or promote another manager.

### Staff

- Created by an administrator or manager through Employee Accounts.
- Uses password authentication without OTP.
- Does not have employee-account administration privileges.

### Customer

- Created through the public registration page.
- Public signup always assigns the `customer` role.
- Cannot self-register as an administrator, manager or staff member.
- Uses password authentication without manager OTP.

Employee account creation is currently controlled in the application by authorized administrators/managers. The employee receives their initial credentials through a secure channel. A future invitation-email flow can replace manual credential handoff without changing the role boundaries.

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

## Authentication flow

### Manager / administrator

1. User submits email and password.
2. Credentials and account status are validated.
3. A six-digit OTP is generated and stored only as a hash.
4. The OTP is emailed to the registered address.
5. The user must enter the OTP before Laravel authenticates the privileged session.

Manager login attempts are rate-limited by email/IP.

### Staff / customer

1. User submits email and password.
2. Credentials and account status are validated.
3. The user is authenticated directly without a manager OTP challenge.

## Security hardening

- Baseline security response headers: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, and HSTS on HTTPS responses.
- Login brute-force throttling through Laravel's rate limiter.
- OTP remains hashed and time-limited.
- Manager-only OTP enforcement is covered by feature tests.
- CSRF protection remains provided by Laravel's web middleware.
- Real credentials remain outside Git via `.env` and `.gitignore`.
- Payment updates use row locking to prevent concurrent overpayment races.
- Public registration cannot create privileged employee accounts.
- Employee creation is restricted by role: administrators may create managers/staff; managers may create staff.

## CI and automated verification

GitHub Actions uses SQLite independently of the local XAMPP/MySQL configuration. The pipeline validates Composer configuration, installs dependencies, lints PHP source, boots the application, runs migrations, verifies routes, executes PHPUnit and uploads diagnostic logs as an artifact even when a job fails.

This separation is intentional: **CI validates the repository; your local XAMPP environment validates MySQL integration.**

## Implementation log

### Phase — Identity and account-role hardening

- Rebranded the application as **UJUZI SHOP MALL — Smart Inventory & Shopping Management**.
- Added an explicit `customer` role alongside administrator, manager and staff roles.
- Removed the unsafe first-registration administrator behavior.
- Restricted public registration to customer accounts.
- Added controlled Employee Accounts creation for authorized administrators/managers.
- Administrators can create managers and staff; managers can create staff only.
- Kept manager/administrator email OTP while staff/customers use password authentication.
- Added role-aware employee management visibility and permissions.
- Added automated coverage for customer registration and controlled employee creation.

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
