# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**DataTel** is a B2B service management web portal for a data communications and cabling company. It provides:
- A **public marketing site** with service listings, contact form, and quote requests
- A **customer portal** for submitting/editing work orders, tracking progress, and digitally signing invoices
- An **employee portal** for viewing calendar assignments and completing work orders with digital signatures
- An **admin portal** for full work order management, dispatching, invoicing, reporting, and system configuration

Full spec: `DataTel_Website_Specification.docx` | Legacy DB reference: `Old_DB.txt` | Logo: `public/images/logo.png`

---

## Tech Stack

| Layer | Choice |
|-------|--------|
| Framework | Laravel 12 (PHP 8.3) |
| Database | SQLite (local dev) / MySQL 8.0 (production) |
| Auth | Laravel Breeze (Blade stack) + custom RoleMiddleware |
| PDF | barryvdh/laravel-dompdf |
| Image/Signature | intervention/image |
| SMS | twilio/sdk (config-gated, off by default) |
| Frontend | Blade templates, CSS Grid/Flexbox, minimal vanilla JS |
| Dev environment | `php artisan serve` (local PHP 8.3 via WinGet) + SQLite — no Docker needed for local dev |

Design colors — Primary: `#1A3C5E`, Accent: `#2E86C1`, Content bg: `#E8ECF0`, Body bg: `#F8F9FA`
Portal/employee header: **white** (`#fff`) with `1px solid #dde` bottom border — nav links use `var(--primary)` / `var(--accent)` (not white)

---

## Local Development

> **Local dev uses `php artisan serve` + SQLite — no Docker required.** Docker was removed due to slowness.

### Start the dev server
```bash
php artisan serve
```
App runs at http://localhost:8000

### Database

**Local dev (default)** — SQLite, zero config, fast:
```
DB_CONNECTION=sqlite          # in .env
```
The SQLite file lives at `database/database.sqlite` (gitignored).

**Production** — MySQL 8.0. Set these in `.env` on the server:
```
DB_CONNECTION=mysql
DB_HOST=your-host
DB_PORT=3306
DB_DATABASE=datatel
DB_USERNAME=datatel
DB_PASSWORD=your-password
```

### Run migrations and seeders
```bash
php artisan migrate --seed
```
After a code pull or schema change, wipe and rebuild:
```bash
php artisan migrate:fresh --seed
```

### Common Artisan commands
```bash
php artisan make:controller Admin/WorkOrderController
php artisan make:model WorkOrder -m
php artisan route:list
php artisan cache:clear
```

---

## Architecture

### User Roles & Route Groups

All users share the `users` table with a `role` column (`customer | employee | admin`).

| Role | Default landing route | Middleware |
|------|----------------------|------------|
| customer | `portal.work-orders.index` | `auth`, `role:customer` |
| employee | `employee.calendar` | `auth`, `role:employee` |
| admin | `admin.dashboard` | `auth`, `role:admin` |

Post-login redirect is handled in `app/Http/Controllers/Auth/AuthenticatedSessionController.php`.
The customer portal has **no Dashboard tab** — work orders is the landing page with stat cards at the top.

### Key Models & Relationships

```
User  ──< WorkOrder (as customer)
User  ──< WorkOrderAssignment (as employee)
User  ──< TimeEntry
User  ──< CompanyMember >── Company
WorkOrder ──< WorkOrderNote
WorkOrder ──< WorkOrderHistory        (audit trail — every field change logged)
WorkOrder ──< WorkOrderAttachment
WorkOrder ──< WorkOrderAssignment >── User (employees)
WorkOrder ──< WorkOrderService >── ServiceType
WorkOrder ──  WorkOrderSignature      (employee-collected completion signature)
WorkOrder ──  Invoice ──< InvoiceLineItem
                       ──  InvoiceSignature  (customer invoice signature)
```

### Work Order Status Flow

```
New → Triaged → Scheduled → Awaiting Customer Feedback
                          → Services Performed → Invoice Prepared → Billed → Completed
                                                                           → Canceled
```

- Every field/status change writes a row to `work_order_history` (field, old_value, new_value, comment, changed_by, changed_at).
- When an employee marks a work order **Services Performed**, the customer signs on an HTML5 canvas. The PNG is saved to `storage/app/signatures/work-orders/` and a `WorkOrderSignature` record is created.
- The completion signature (signer name, date/time, who collected it) is displayed on the admin and customer work order detail views.

### Invoice Status Flow

```
Draft → Issued → Payment Received → Completed
              → Canceled
```

- `draft` — invoice created by admin, being prepared
- `issued` — invoice sent to customer
- `payment_received` — customer submitted/confirmed payment
- `completed` — payment confirmed and invoice closed
- `canceled` — invoice voided

Invoice constants on `Invoice` model: `STATUS_DRAFT`, `STATUS_ISSUED`, `STATUS_PAYMENT_RECEIVED`, `STATUS_COMPLETED`, `STATUS_CANCELED`.

The invoice show page uses the same stepper UI pattern as work orders (dots, connector lines, action buttons, override modal).

### Registration & User Profile

- `users.title` (varchar 100, nullable) — captures the customer's job title/position at registration
- The public nav **"Create a Customer Account"** button links to `/register` (was previously "Get a Quote" → quote form)
- All layout logos link to `route('home')` and are sized with `overflow: visible` on the header so the logo can be taller than the nav bar strip without expanding it

### Invoice Creation Flow

1. Admin opens a work order with status `services_performed`
2. Clicks **Generate Invoice** → redirected to `/admin/invoices/create?work_order_id=X`
3. Two-column form: left panel shows work order reference (services, description, site details, completion signature inline PNG); right panel has the invoice form
4. Line items are **pre-seeded** from the work order's service types (editable, admin fills in prices)
5. Tax rate pre-filled from `AdminSetting::get('default_tax_rate')`, displayed as percentage, editable
6. Live JS recalculates subtotal → tax amount → total as admin types
7. On submit: `subtotal`, `tax_rate` (fraction), `tax_amount`, `total`, `payment_terms`, `footer_note` are all stored on the `Invoice` record. Work order status advances to `invoice_prepared`.

### Invoice Totals Storage

- Tax rate stored as decimal fraction in DB (e.g., `0.0750` = 7.5%)
- Displayed as percentage in UI (multiply × 100)
- `invoice_line_items.line_total` is a STORED generated column (`quantity * unit_price`) — works in SQLite 3.31+ and MySQL 8.0+
- Subtotal, tax amount, and total are also persisted on the `invoices` table for quick display

### Admin Settings Keys

Accessed via `AdminSetting::get('key', $default)` — key-value store in `admin_settings` table:

| Key | Description | Default |
|-----|-------------|---------|
| `default_tax_rate` | Tax rate as decimal fraction (0.0750 = 7.5%) | `0.0750` |
| `invoice_terms` | Payment terms text | `Net 30` |
| `invoice_footer` | Footer note on invoices | `Thank you for your business.` |
| `invoice_due_days` | Days until invoice due | `30` |
| `company_name` | Company display name | `DataTel` |
| `company_phone` | Company phone | — |
| `company_email` | Company email | — |
| `company_address` | Company address | — |
| `sla_routine_hours` | SLA for routine urgency | `48` |
| `sla_urgent_hours` | SLA for urgent | `24` |
| `sla_emergency_hours` | SLA for emergency | `4` |

### Employee Portal — Calendar & Work Orders

- Default landing page: **Calendar** (`/employee/calendar`)
- Supports **day view** (6 AM–8 PM timeline, 80px/hour, orders as absolutely-positioned blocks) and **week view** (7-column CSS grid)
- Each calendar block shows: time range, status label (using PHP `$statusLabel` closure), customer name, address
- Clicking a block navigates to the full work order detail view (`/employee/work-orders/{id}`)
- Employee detail view shows: all notes, photos, documents, history timeline, assigned team, and completion signature if already collected
- **Mark Services Performed** button opens a signature modal with HTML5 canvas (mouse + touch support, `passive: false` on touch events)
- On completion: PNG saved to `storage/app/signatures/work-orders/`, `WorkOrderSignature` created, status → `services_performed`, `WorkOrderHistory` entry written
- **Status back-fill**: if an employee completes an order that was never scheduled (still `new`/`triaged`), `WorkOrder::backfillScheduledStep()` first records a `scheduled` transition so the lifecycle/audit trail stays continuous (two history rows). Admins are exempt — the admin **Override Status** modal sets any status directly.

### Customer Portal — Work Orders

- Stat cards at the top of the work orders list (Active, Awaiting Feedback, Ready to Sign, Completed This Month) — clicking filters the list
- Default filter: active orders sorted by scheduled date (nulls last via `orderByRaw`)
- Customers can **edit** submitted work orders (description, equipment details, urgency, preferred date, site info, services) until status is `completed` or `canceled`. Every changed field is recorded in `WorkOrderHistory`.
- Customers can add/remove photos (max 3) and documents (max 3) on their work orders
- Customer profile stores phone number; it auto-fills on-site contact fields when creating a new work order
- Completion signature displayed in the right sidebar when work has been performed
- On completed work orders that have an invoice, the invoice details are shown inline below the WO details card
- **Printable invoice**: `portal.invoices.print` route → `Customer\InvoiceController::printView()` → standalone Blade view (`customer/invoices/print.blade.php`, no layout extension). Includes logo, WO details, invoice line items, totals, payment terms, footer note. Opened in a new tab.

### Reports (admin)

- Catalog at `/admin/reports` (`admin.reports.index`), linked in the sidebar under Customer Analytics. Each report card is a small GET form that opens the report in a new tab.
- 9 reports across four areas: **Work Orders** (summary, open-order aging), **Invoicing** (invoice register, A/R aging), **Technicians** (productivity, time detail), **Customers & Companies** (customer statement, company performance, service-catalog usage).
- `ReportController` (one method per report) resolves the period via `AnalyticsService::resolveRange()` and pulls data from `ReportService`. Range reports accept `range`/`from`/`to`; some accept `customer_id`/`company_id`/`tech_id`. Aging/AR are "as-of-now" snapshots.
- Reports render with `resources/views/admin/reports/layout.blade.php` — a **standalone** print layout (does NOT extend `layouts.admin`), with `@media print` rules, repeating table headers (`thead { display: table-header-group }`), a fixed print footer, and a Print/Close bar. Each report view does `@extends('admin.reports.layout')` + `@section('body')`.
- Completion/billing dates are derived from `work_order_history` (`field_name='status'`, `new_value='completed'`) since there is no `completed_at` column. Service→revenue attribution isn't available (line items are free-text), so Service Usage uses catalog list price × request count as an estimate.

### Service Catalog Admin

- `ServiceType` records are toggled active/inactive — **never hard deleted** (referenced on historical work orders)
- Admin manages via `/admin/services` (resource routes minus destroy/show + custom toggle)
- Inactive services hide from new work order creation forms but remain visible on historical records
- `ServiceType.default_unit_price` (nullable decimal) — pre-fills the unit price when the service is added to an invoice line item
- Sort order is managed via **drag-and-drop** on the index page (HTML5 native DnD + AJAX `POST admin/services/reorder`); sort_order is auto-assigned on create
- **Route ordering**: `POST services/reorder` must be declared **before** `Route::resource('services', ...)` to prevent the `{service}` binding from capturing the literal string "reorder"

---

## Key Directories

```
app/Http/Controllers/
  Auth/                  Laravel Breeze auth controllers
  Admin/                 Admin portal controllers
    WorkOrderController  — CRUD/show + updateStatus, updateUrgency, notes, attachments
    WorkOrderScheduleController   — scheduling, visits, travel-time, confirmation workflows
    WorkOrderAssignmentController — assign / unassign employees
    InvoiceController    — includes updateStatus()
    ReportController     — Reports catalog + 9 print reports (admin.reports.*)
    ServiceTypeController — service catalog CRUD + toggle
    CalendarController   — admin calendar
  Customer/
    WorkOrderController  — includes update(), addAttachment(), removeAttachment()
  Employee/
    CalendarController   — day/week calendar view
    WorkOrderController  — show() + complete() (signature collection)
  Public/                Public marketing pages
app/Http/Middleware/
  RoleMiddleware.php     Redirects unauthorized role access
app/Models/              Eloquent models (one per DB table)
  WorkOrder.php          Status + urgency + confirmation constants
  WorkOrderSignature.php Employee-collected completion signatures ($timestamps = false)
  Invoice.php            STATUS_DRAFT/ISSUED/PAYMENT_RECEIVED/COMPLETED/CANCELED constants
  InvoiceLineItem.php    line_total is a STORED generated column
  AdminSetting.php       get(key, default) / set(key, value) — cached 1 hour
app/Services/
  AnalyticsService.php   Dashboard/analytics KPIs, time-series bucketing, leaderboards
  ReportService.php      Query/aggregation logic behind the admin Reports section
resources/views/
  layouts/               Base layouts (public, portal, admin, employee)
    portal-styles.blade.php  Shared CSS — alert classes, badge classes, etc.
  admin/
    work-orders/         Full CRUD + schedule modal + tech timeline
    invoices/            create (two-column WO-linked form), show (stepper), edit, index
    services/            index, create, edit (service catalog)
  customer/
    work-orders/         index (stat cards + filters), show (editable, attachments, inline invoice on completed)
    invoices/            show, print (standalone printable view)
  employee/
    calendar.blade.php   Day + week calendar views
    work-orders/show     Full detail + signature modal
database/migrations/     All schema migrations
database/seeders/        ServiceTypeSeeder, AdminUserSeeder, AdminSettingsSeeder
public/images/           logo.png (site logo)
storage/app/
  uploads/work-orders/{id}/   Customer-uploaded attachments (served via auth-gated routes)
  signatures/work-orders/     Employee-collected completion signature PNGs (wo-{id}-{time}.png)
  signatures/                 Invoice signature PNGs
  invoices/                   Generated invoice PDFs (future)
```

---

## Environment Variables (beyond Laravel defaults)

```dotenv
# Twilio SMS (leave blank to disable)
TWILIO_ENABLED=false
TWILIO_SID=
TWILIO_TOKEN=
TWILIO_FROM=

# Company branding (also stored in admin_settings table)
COMPANY_NAME="DataTel"
COMPANY_PHONE=
COMPANY_EMAIL=

# File upload limits
MAX_UPLOAD_SIZE_MB=20
MAX_UPLOAD_FILES=10
```

---

## Database Conventions

- All PKs: `id` (unsigned bigint, auto-increment)
- All timestamps: `created_at`, `updated_at` via `$table->timestamps()`
- Soft deletes (`deleted_at`) on: `users`, `work_orders`, `invoices`, `companies`
- Status columns use string constants defined on the model (e.g., `WorkOrder::STATUS_NEW`, `Invoice::STATUS_DRAFT`)
- The `admin_settings` table is a key-value store; access via `AdminSetting::get('key', $default)`
- `WorkOrderSignature` has `$timestamps = false` — uses only `signed_at` (datetime cast)
- `invoice_line_items.line_total` is a STORED generated column — do not set it manually
- Tax rate is stored as a decimal fraction (`0.0750`) not a percentage; multiply by 100 for display

## Important Patterns

### Flash messages
All three layouts (admin, portal, employee) handle `success`, `info`, and `error` session flash keys.
The shared CSS in `portal-styles.blade.php` defines `.alert`, `.alert-success`, `.alert-error`, `.alert-warn`, `.alert-info`.

### Attachment serving
All attachment serving goes through auth-gated routes in `routes/web.php`:
- `GET /attachments/{attachment}` → force download (`attachments.download`)
- `GET /attachments/{attachment}/view` → inline serve (`attachments.view`)
- Customers can only access attachments on their own work orders (403 otherwise)

### Signature images (completion)
Completion signature PNGs are **not** served through a route — they are embedded inline as base64 in Blade views:
```php
data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/signatures/work-orders/'.$sig->signature_path))) }}
```
Always check `file_exists($sigPath)` before embedding.

### WorkOrderHistory
Every meaningful change should write a history row. Pattern used throughout:
```php
WorkOrderHistory::create([
    'work_order_id' => $workOrder->id,
    'changed_by'    => auth()->id(),
    'field_name'    => 'status',
    'old_value'     => $old,
    'new_value'     => $new,
    'comment'       => 'Optional human-readable explanation.',
    'changed_at'    => now(),
]);
```

### Nulls-last sorting for scheduled_at
```php
->orderByRaw('scheduled_at IS NULL, scheduled_at ASC')
```
Works in both SQLite and MySQL.

### Status & urgency metadata (single source of truth)
`WorkOrder` holds the canonical maps so the three portals can't drift:
- `WorkOrder::STATUS_LABELS` + `statusLabel()` — canonical (admin-facing) labels. Customer views may relabel (e.g. "Submitted" for `new`).
- `WorkOrder::URGENCY_LABELS` / `URGENCY_COLORS` + `urgencyLabel()` / `urgencyColors()`.
- Render the urgency pill with the shared component: `<x-wo.urgency-badge :work-order="$workOrder" />` (used by the customer & employee detail headers). Status badges still use the `badge badge-{status}` CSS classes from `portal-styles.blade.php`.

### Service types toggle (never delete)
```php
$service->update(['is_active' => !$service->is_active]);
```
Inactive services are hidden from new work order forms but remain on historical records.
