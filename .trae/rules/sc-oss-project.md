---
alwaysApply: true
---
# Project - Silvergram OMS (MVP) - Laravel Stack

## MVP Scope
### Must Have
1) Auth & Security
   - Login, logout, forgot password (Breeze/Jetstream - Blade)
   - Laravel Sanctum for API tokens (for future mobile/integration)
   - Rate limiting on auth endpoints

2) RBAC (Role-Based Access Control)
   - Roles: SUPER_ADMIN, SILVERCHANNEL
   - Permissions managed using spatie/laravel-permission
   - Middleware guard for routes: web + api

3) Silverchannel Registration (centralized)
   - Registration form + optional referral code
   - Admin approval flow (approve/reject + reason)
   - Referral relationship locked after approval

4) Product Catalog
   - Admin CRUD product: SKU, name, weight/variant, stock, images
   - Pricing tier: price for Silverchannel + optional MSRP
   - Inventory stock adjustments + history log

5) Ordering
   - Silverchannel creates order from catalog
   - Order status state machine:
     DRAFT -> SUBMITTED -> WAITING_PAYMENT -> WAITING_VERIFICATION (manual)
     -> PAID -> PACKING -> SHIPPED -> DELIVERED
   - Exceptions:
     CANCELLED, REFUNDED, RETURN_REQUESTED, RETURNED

6) Payment (Manual Transfer)
   - Upload payment proof (file storage local/s3-compatible)
   - Admin verify / reject
   - Payment log for audit

7) Referral Dashboard (Silverchannel)
   - Show referral code / referral link
   - List referred Silverchannels & their statuses

8) Commission System (Ledger-based Wallet)
   - Commission types:
     - Registration commission (CPL)
     - Transaction commission (CPA / revenue share)
   - Ledger entries:
     PENDING -> AVAILABLE (after holding period) -> PAID
   - Holding period default 14 days (configurable)
   - Refund/return reverses pending commission

9) Payout Request
   - Silverchannel requests payout from AVAILABLE balance
   - Admin reviews and marks payout as PAID
   - Store payout bank info snapshot per payout (audit-safe)

10) Reporting
   - Admin reports: orders, commissions, payouts (export CSV)
   - Basic dashboard analytics

### Nice to Have (Post-MVP)
- Payment gateway integration
- Shipping API integration (rates + tracking)
- Buyer module (end customer ordering)
- Multi-level referral (2-3 levels)
- Promo codes & campaign tracking landing pages

---

## Tech Stack (Fixed)
- Backend: PHP 8.3+, Laravel 11.x
- Database: MySQL 8+ (InnoDB, utf8mb4_unicode_ci)
- Cache & Queue: Redis (queue for async tasks: email, notifications, report exports)
- Frontend: Blade + Tailwind CSS + Alpine.js
- Auth scaffolding: Breeze / Jetstream (Blade stack)
- API & Token: Laravel Sanctum
- RBAC: spatie/laravel-permission
- Logging: Laravel default logging (stack)

---

## Architecture Notes (Laravel Best Practice)
- Use Service layer for business logic:
  - OrderService, PaymentService, CommissionService, PayoutService
- Use Policies/Gates for authorization + Spatie permissions for role mapping
- Use Events & Listeners for commission triggers:
  - SilverchannelApproved
  - OrderPaid
  - OrderRefunded / OrderReturned
- Use database transactions for critical flows (order paid -> ledger write)

---

## Database Conventions
- Engine: InnoDB
- Charset/Collation: utf8mb4 / utf8mb4_unicode_ci
- Use soft deletes where appropriate (users, products), but ledger should be immutable
- Monetary values stored as BIGINT (in smallest currency unit) or DECIMAL(18,2) consistently

---

## Proposed Repository Structure (Laravel)
- app/
  - Domain/
    - Orders/
    - Payments/
    - Referrals/
    - Commissions/
    - Payouts/
  - Http/
    - Controllers/
    - Middleware/
    - Requests/
  - Models/
  - Policies/
  - Events/
  - Listeners/
- resources/views (Blade)
- resources/js (Alpine components)
- resources/css (Tailwind)
- routes/web.php, routes/api.php
- database/migrations, seeders, factories
- docs/context.md, docs/project.md

---

## Acceptance Criteria
- Super Admin can approve Silverchannel; referral link is locked after approval
- Silverchannel can place order and upload payment proof
- Admin can verify payment and ship order with tracking number
- Commission ledger entries are created on:
  - Silverchannel approval (registration commission)
  - Paid orders (transaction commission)
- Holding period enforced; payout only from AVAILABLE balance
- Refund/return reverses or cancels related pending commission
- All admin actions logged (audit trail)
