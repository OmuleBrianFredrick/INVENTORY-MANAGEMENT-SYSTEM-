# UJUZI SHOP MALL

**Smart Inventory & Shopping Management**

UJUZI SHOP MALL is a Laravel 12 inventory and shopping management platform designed to separate public customers from organization employees while providing controlled inventory, procurement, sales, payment, reporting and security workflows.

The project was rebuilt from the reference Inventorysystem project and extended with stronger authentication, role separation, secure employee onboarding, auditable stock operations, security hardening, automated testing and a production-oriented application structure.

---

## Current project status

### Automated application verification: COMPLETE

The current automated suite is green:

- **30 tests passed**
- **120 assertions passed**
- **0 failures**
- Authentication and authorization coverage
- Employee invitation lifecycle coverage
- Inventory, procurement and sales coverage
- Payment and barcode coverage
- Security-hardening coverage

The repository has therefore completed the current automated application-test phase.

### Next phase: Browser and local integration testing

The next verification stage is deliberately different from PHPUnit. It will validate the real application through the browser against the local XAMPP/MySQL environment, including:

1. Application startup on `http://localhost:8000`.
2. Administrator sign-in and real email OTP delivery.
3. Employee invitation creation from the administrator account.
4. Invitation email delivery and secure activation link.
5. Employee password creation and account activation.
6. Manager sign-in with password + email OTP.
7. Staff sign-in with password only.
8. Customer public registration and password sign-in.
9. Inventory, category, supplier and procurement workflows.
10. Sales, stock deduction, payments and alerts.
11. Flutterwave test-mode payment integration once local payment configuration is ready.
12. Browser/session behavior, authorization boundaries and responsive UI.

Automated tests and browser testing are intentionally treated as separate verification layers.

---

## Functional scope

- Laravel 12 / PHP 8.2+
- Administrator, manager, staff and customer roles
- Customer-only public registration
- Mandatory email OTP for privileged administrator/manager sign-in
- Password-only authentication for staff and customers
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
- Secure employee invitation onboarding
- Administrator user management and last-active-administrator protection
- Cream, charcoal and bronze visual theme
- PHPUnit feature coverage for core business, onboarding and security rules
- GitHub Actions CI workflow

---

# Account and role architecture

UJUZI SHOP MALL deliberately separates **customers** from **organization employees**.

```text
                         UJUZI SHOP MALL
                                │
                ┌───────────────┴───────────────┐
                │                               │
             CUSTOMERS                       EMPLOYEES
                │                               │
         Public registration          Controlled invitation
                                                │
                         ┌──────────────────────┴──────────────────────┐
                         │                                             │
                      MANAGER                                        STAFF
                         │                                             │
                  Password + OTP                                  Password only
```

## Administrator

- Provisioned by the first-install administrator seeder/environment configuration.
- Has full platform and employee-account management access.
- Uses password + email OTP at sign-in.
- Can invite managers and staff.
- Can edit employee roles and status while preserving at least one active administrator.
- Must not be created through public registration.

## Manager

- Invited by an administrator through Employee Accounts.
- Uses password + email OTP at sign-in.
- Can manage operational inventory, procurement, sales and staff accounts.
- Can invite staff.
- Cannot invite or promote another manager.

## Staff

- Invited by an administrator or manager through Employee Accounts.
- Uses password authentication without OTP.
- Does not have employee-account administration privileges.

## Customer

- Created through the public registration page.
- Public signup always assigns the `customer` role.
- Cannot self-register as an administrator, manager or staff member.
- Uses password authentication without manager OTP.

### Important security rule

The old **"first person to register becomes administrator"** behavior has been removed. Public registration is no longer an employee-account provisioning mechanism.

---

# Employee invitation workflow

Employee onboarding no longer relies on an administrator creating and manually handing out an employee password.

The active workflow is:

```text
Administrator / Manager
          │
          ▼
   Employee Accounts
          │
          ▼
 Name + company email + role
          │
          ▼
 Inactive employee record
 + random unusable password
          │
          ▼
 Secure single-use invitation token
          │
          ▼
       Email invite
          │
          ▼
 Employee opens invitation link
          │
          ▼
 Employee creates own password
          │
          ▼
 Account activated
          │
          ▼
 Employee signs in
```

Invitation security:

- Token is generated randomly.
- Only the SHA-256 token hash is stored.
- Invitation links are single-use.
- Invitations expire after 24 hours by default.
- Revoked invitations cannot be accepted.
- Expired invitations cannot be accepted.
- Already accepted invitations cannot be reused.
- Employee passwords are created by the employee and stored hashed.
- Administrators can invite managers and staff.
- Managers can invite staff only.
- Staff cannot manage employee accounts.

Authorized users can resend valid pending invitations. Administrators can revoke pending invitations.

---

# Authentication workflow

## Administrator / Manager

```text
Email + password
       │
       ▼
Credentials validated
       │
       ▼
Six-digit email OTP generated
       │
       ▼
OTP stored as a hash
       │
       ▼
OTP emailed to registered address
       │
       ▼
OTP verified
       │
       ▼
Privileged session authenticated
```

Manager/admin login attempts are rate-limited by email/IP.

## Staff / Customer

```text
Email + password
       │
       ▼
Credentials validated
       │
       ▼
Authenticated directly
```

Staff and customers do not receive the privileged manager/admin OTP challenge.

---

# Security hardening

- Baseline security response headers: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, and HSTS on HTTPS responses.
- Login brute-force throttling through Laravel's rate limiter.
- OTP values remain hashed and time-limited.
- Manager/admin OTP enforcement is covered by feature tests.
- CSRF protection remains provided by Laravel's web middleware.
- Real credentials remain outside Git via `.env` and `.gitignore`.
- Payment updates use row locking to prevent concurrent overpayment races.
- Public registration cannot create privileged employee accounts.
- Employee invitations are restricted by role and use hashed, expiring, single-use tokens.
- Last-active-administrator protection prevents removal of the final active administrator.

---

# Inventory and business modules

## Products and inventory

- Product CRUD
- SKU and unique barcode support
- Categories
- Suppliers
- Cost and selling prices
- Stock quantities and reorder levels
- Barcode-aware search
- Auditable inventory movements
- Low-stock alerts

## Procurement

- Supplier-linked purchase orders
- Draft → ordered → partial/received lifecycle
- Transaction-safe receiving
- Automatic stock updates and movement ledger entries

## Sales

- Customer directory
- Sales orders and order items
- Atomic stock deduction
- `STOCK_OUT` ledger entries
- Payment and delivery states

## Payments

- Payment records linked to sales orders
- Provider/method/reference fields
- Partial and full payment states
- Transaction-safe payment updates
- Row locking for concurrent payment safety
- Gateway-ready payment layer
- Flutterwave test-mode integration is part of the upcoming local integration/browser verification stage

## Returns and refunds

- Return and return-item records
- Remaining-quantity validation
- Returned goods restored to stock
- `RETURN` movement records

## Reporting

- Management reports
- Inventory valuation
- Cost, retail and potential-margin calculations
- Category valuation
- Stock movement summaries
- Sales and purchase totals
- Stock-health indicators

---

# Environment configuration

The repository contains a complete **`.env.example`** template.

It includes:

- XAMPP/MySQL connection placeholders
- Local application settings
- Mock SMTP configuration for OTP and invitation email
- OTP security settings
- First-install administrator placeholders
- Mock payment/SMS integration placeholders
- Employee invitation expiry configuration

No real secrets are committed. `.env` is ignored by Git.

### XAMPP/MySQL example

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_management_system
DB_USERNAME=root
DB_PASSWORD=
```

Adjust the credentials to match the local XAMPP/MySQL installation.

### Employee invitation example

```env
EMPLOYEE_INVITATION_EXPIRY_HOURS=24
```

### SMTP

`.env.example` contains deliberately fake SMTP values such as `smtp.example.com`. These are **placeholders, not working credentials**.

For real OTP and employee invitation delivery, replace the SMTP host, port, username, password, encryption and sender address with the chosen mail provider's values.

For local development without SMTP delivery:

```env
MAIL_MAILER=log
```

Laravel will write outgoing mail to the application log instead of attempting delivery.

---

# Local XAMPP setup

After cloning the repository:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

Create a MySQL database in XAMPP/phpMyAdmin named `inventory_management_system`, or update `DB_DATABASE` to match the local database name.

Run migrations and the initial administrator seeder:

```bash
php artisan migrate
php artisan db:seed
```

Do **not** use `php artisan migrate:fresh` during normal development when the local database contains accounts or business data. It drops and recreates the application's tables.

Then clear application caches when configuration or Blade files change:

```bash
php artisan optimize:clear
php artisan view:cache
```

Run the automated suite:

```bash
php artisan test
```

Start the local application on the browser-testing port:

```bash
php artisan serve --port=8000
```

Then open:

```text
http://localhost:8000
```

If using XAMPP Apache instead of `php artisan serve`, point the web server at the Laravel application's `public` directory and retain the same `.env` database configuration.

---

# Automated verification

The current repository baseline has:

```text
30 tests passed
120 assertions passed
0 failures
```

The suite covers:

- Manager/admin OTP authentication
- Staff authentication
- Customer authentication
- Customer-only public registration
- Employee invitation authorization
- Invitation activation
- Invitation expiry
- Invitation revocation
- Role restrictions
- Category management
- Inventory management
- Procurement and receiving
- Sales orders and stock deduction
- Payment business rules
- Barcode uniqueness
- Security headers
- Login throttling
- Failed-login recovery
- Last-active-administrator protection

### CI versus local database testing

GitHub Actions uses SQLite independently of the local XAMPP/MySQL configuration. This is intentional:

**CI validates the repository and application logic; the local XAMPP environment validates MySQL and real service integration.**

The automated suite therefore should not be used as evidence that the local MySQL database still contains user accounts. Local account/data state must be checked against the actual XAMPP database.

---

# Browser testing plan

The browser-testing phase begins after the automated suite is green.

## Stage 1 — Application startup

- Confirm `http://localhost:8000` loads.
- Confirm the UJUZI SHOP MALL branding is displayed.
- Confirm no Blade compilation errors.
- Confirm security headers are present.

## Stage 2 — Administrator authentication

- Sign in with the seeded administrator account.
- Confirm the password is accepted.
- Confirm the OTP is delivered to the configured email address.
- Enter the OTP.
- Confirm privileged authentication succeeds.
- Confirm the administrator dashboard loads.

## Stage 3 — Employee onboarding

- Open Employee Accounts as administrator.
- Invite a manager.
- Confirm the invitation is created as pending/inactive.
- Confirm the invitation email is sent.
- Open the invitation link.
- Create the manager password.
- Confirm the manager account becomes active.
- Sign in as the manager.
- Confirm manager OTP is required.

Then repeat the relevant workflow for a staff employee and confirm staff does not receive the manager OTP challenge.

## Stage 4 — Customer onboarding

- Open public registration.
- Create a customer account.
- Confirm the account receives the `customer` role.
- Confirm the customer can sign in with password only.
- Confirm customer permissions do not expose employee-management functions.

## Stage 5 — Core business workflows

Test the real browser flows for:

- Categories
- Products
- Suppliers
- Purchase orders
- Goods receiving
- Customers
- Sales orders
- Stock deduction
- Inventory alerts
- Returns/refunds
- Payments
- Reports

## Stage 6 — Flutterwave test mode

After the browser flow is stable, configure the local Flutterwave test credentials and verify payment initiation, callback handling and payment-state updates using test-mode transactions.

## Stage 7 — Final integration hardening

- Verify authorization boundaries through the UI.
- Verify invalid/expired invitation behavior.
- Verify OTP expiry and resend behavior.
- Verify session/logout behavior.
- Verify responsive layouts.
- Verify error handling.
- Verify real SMTP delivery.
- Verify payment integration.
- Re-run the full automated suite after integration fixes.

---

# Implementation log

## Phase — UJUZI SHOP MALL identity and role hardening

- Rebranded the application as **UJUZI SHOP MALL — Smart Inventory & Shopping Management**.
- Added an explicit `customer` role alongside administrator, manager and staff roles.
- Removed the unsafe first-registration administrator behavior.
- Restricted public registration to customer accounts.
- Separated public customer onboarding from employee onboarding.

## Phase — Secure employee invitation onboarding

- Replaced manual employee password creation with controlled employee invitation onboarding.
- Administrators can invite managers and staff.
- Managers can invite staff only.
- Staff cannot manage employee accounts.
- Added single-use, SHA-256-hashed, 24-hour invitation tokens.
- Added employee password creation and activation through invitation links.
- Added invitation expiry and revocation handling.
- Added invitation resend support for authorized pending employees.
- Kept manager/administrator email OTP while staff/customers use password authentication.
- Added automated tests for the complete invitation lifecycle and authorization boundaries.

## Phase — Categories

- Added normalized categories and product relationship.
- Added manager category creation/archive workflow.
- Preserved legacy category text for compatibility.

## Phase — Procurement and receiving

- Added supplier-linked purchase orders.
- Added draft → ordered → partial/received lifecycle.
- Added transaction-safe receiving and stock ledger entries.

## Phase — Customers and sales

- Added customer directory.
- Added sales orders and order items.
- Added atomic stock deduction and `STOCK_OUT` ledger entries.
- Added payment and delivery states.
- Fixed the sales-order item form so users add only the products they actually intend to sell.

## Phase — Inventory alerts

- Added persistent manager/admin inventory alerts.
- Added low-stock alert generation.
- Added alert centre and read state.

## Phase — Reporting and valuation

- Added management report controller and dashboard.
- Added cost, retail and potential-margin valuation.
- Added category valuation and movement summaries.
- Added sales/purchase totals and stock-health indicators.

## Phase — Returns, refunds and barcode

- Added return and return-item tables.
- Added return processing with remaining-quantity validation.
- Restored returned goods into stock and recorded `RETURN` movements.
- Added unique product barcode and barcode-aware search.
- Added barcode inputs to product creation/editing.

## Phase — Payment foundation

- Added payment records linked to sales orders.
- Added transaction-safe payment service.
- Added partial/full payment state updates.
- Added manager payment recording endpoint.
- Added row locking for concurrent payment safety.
- Kept provider credentials and external webhook configuration out of source code.

## Phase — Hardening and UI polish

- Added deterministic XAMPP/MySQL environment template.
- Added mock SMTP/payment/SMS placeholders without committing secrets.
- Added stricter CI diagnostics and PHPUnit configuration.
- Added PHP source linting, route verification and failure diagnostics artifacts.
- Restricted OTP to managers/administrators according to the application security requirement.
- Added login throttling and administrator-continuity protections.
- Added baseline HTTP security headers.
- Corrected Blade compilation issues discovered during security testing.
- Corrected security messaging so the UI accurately states that manager accounts use email OTP rather than claiming every account requires OTP.
- Rebranded visible application UI and configuration from the previous inventory-system identity to UJUZI SHOP MALL.
- Added responsive UI, keyboard focus states, mobile layouts, tables and action controls.

---

# Repository-side completion boundary

The repository-side implementation includes the application code, models, controllers, routes, migrations, tests, CI, UI and configuration templates.

The following remain local/integration responsibilities and are intentionally not committed as real secrets or machine-specific state:

- Local XAMPP database creation and data
- Real SMTP credentials and delivery
- Real Flutterwave credentials and payment configuration
- Real SMS credentials and provider configuration
- Local machine-specific `.env` values
- Browser/end-to-end verification against the local environment

---

# Development rule

GitHub `main` is the source of truth for repository-side changes. Local development should normally follow this sequence:

```text
GitHub main
    ↓
git pull origin main
    ↓
Local VS Code repository
    ↓
php artisan optimize:clear
    ↓
php artisan test
    ↓
Browser / integration testing
```

Avoid `migrate:fresh` unless intentionally resetting a disposable development database.

---

# Reference

The original `kisamac1/Inventorysystem` repository was used only as the functional reference. This repository is the independent rebuilt implementation and intentionally improves architectural weaknesses identified during review.
