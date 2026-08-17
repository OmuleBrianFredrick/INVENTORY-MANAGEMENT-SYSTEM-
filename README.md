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

No real secrets are committed. `.env` is explicitly ignored by Git. fileciteturn113file0

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

The application has a transaction-safe payment record and `PaymentService`. It records provider, method, reference, amount, status and payment time, prevents overpayment, and automatically updates an order from `unpaid` to `partial` or `paid`. The current provider is intentionally manual/gateway-ready; real provider credentials and webhook contracts belong in final deployment configuration rather than source code.

## Authentication flow

### Manager / administrator

1. User submits email and password.
2. Credentials and account status are validated.
3. A six-digit OTP is generated and stored only as a hash.
4. The OTP is emailed to the registered address.
5. The authentication event is logged.
6. The user must enter the OTP before Laravel authenticates the manager session.

### Ordinary staff

1. User submits email and password.
2. Credentials and account status are validated.
3. The user is authenticated directly without a manager OTP challenge.

## Roles

- **Administrator:** full platform and user-management access.
- **Manager:** inventory, suppliers, categories, procurement and sales-management access.
- **Staff:** authenticated platform access without management privileges by default.

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
- Kept provider credentials and external webhook configuration out of source code.

### Phase — Hardening and UI polish

- Added deterministic XAMPP/MySQL environment template.
- Added mock SMTP/payment/SMS placeholders without committing secrets.
- Added stricter CI diagnostics and PHPUnit configuration.
- Restricted OTP to managers/administrators according to the application security requirement.
- Improved responsive cream UI, keyboard focus states, mobile layouts, tables and action controls.

## Next stage: hardening and polish

The remaining work is deliberately **not local database creation**. Local migration into XAMPP, database credentials and real external provider credentials are deployment/integration steps for the developer machine. Repository-side work continues with verification and production hardening: make CI green, run the complete regression suite, fix migration/database edge cases, tighten authorization and validation, add indexes and pagination where needed, review transactions/concurrency, configure notification transports, document deployment, and perform the final security/performance audit.

## Reference

The original `kisamac1/Inventorysystem` repository was used only as the functional reference. This repository is the independent rebuilt implementation and intentionally improves architectural weaknesses identified during the review.
