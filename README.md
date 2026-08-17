# Advanced Inventory Management System

A Laravel 12 inventory platform rebuilt from the reference Inventorysystem project and extended with mandatory email OTP authentication, role-aware user management, a cream business UI, auditable stock movements, security logs, suppliers, categories, procurement/receiving, customers, sales orders, inventory alerts, reporting/valuation, returns/refunds and barcode support.

## Core features

- Laravel 12 / PHP 8.2+
- Mandatory email OTP after every password sign-in
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
- Returns/refunds workflow that restores returned stock
- Inventory alert centre with unread/read state
- Management reporting and inventory valuation
- Inventory value by category and stock movement summaries
- Administrator, manager and staff roles
- Administrator user management
- Cream, charcoal and bronze visual theme
- PHPUnit tests and GitHub Actions CI

## Valuation model

Reports distinguish three management values:

- **Cost value:** current stock multiplied by product cost price.
- **Retail value:** current stock multiplied by selling price.
- **Potential gross margin:** retail value minus cost value.

The reporting layer also shows low/out-of-stock counts, sales totals, purchase totals, stock movement summaries and valuation by category.

## Returns and refunds

Returns are tied to sales orders and order items. The system validates that returned quantities do not exceed the quantity originally sold less previous returns. Processing occurs inside a database transaction, restores returned quantities to stock and records a `RETURN` stock movement. Refund status remains explicit so future payment-gateway processing can be attached without changing inventory accounting.

## Barcode support

Products now have an optional unique barcode in addition to SKU. The product create/edit forms accept barcodes and the inventory search can match product name, SKU or barcode. This provides the data layer needed for USB/Bluetooth scanner workflows without tying the application to a specific scanner vendor.

## Authentication flow

1. User submits email and password.
2. Credentials and account status are validated.
3. A six-digit OTP is generated and stored only as a hash.
4. The OTP is emailed to the registered address.
5. The authentication event is logged.
6. The user must enter the OTP before Laravel authenticates the session.

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

For real OTP email delivery, configure SMTP values in `.env`. Never commit `.env` or production credentials.

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

## Build order

All functional phases are being executed before the final hardening/polishing pass. The final pass will address CI, regression tests, UX consistency, validation edge cases, database indexing, performance, deployment configuration, documentation and production security.

## Reference

The original `kisamac1/Inventorysystem` repository was used only as the functional reference. This repository is the independent rebuilt implementation and intentionally improves architectural weaknesses identified during the review.
