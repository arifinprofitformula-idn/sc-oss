# Context - Silvergram Order Management System (OMS)

## Business Goal
Build a web-based Order Management System for Silvergram business to manage:
1) centralized registration & verification of Silverchannel partners,
2) ordering flow from Silverchannel to Main Distributor (Super Admin),
3) referral program where a Silverchannel can refer new Silverchannels (Promotor),
4) commission calculation and payout with audit-ready ledger.

## Roles
### 1) SUPER_ADMIN (Distributor Utama)
- Manage products, inventory, pricing tiers, promotions
- Approve/reject Silverchannel registration
- Verify payments (if manual transfer)
- Fulfill orders, shipping & tracking
- Configure commission rules, approve payouts
- View analytics dashboards, export reports
- Full audit trail access

### 2) SILVERCHANNEL
- Register via centralized form
- Optional referral code on registration
- Browse product catalog, create orders to Super Admin
- Track orders, payment status, shipments
- Access referral dashboard: referred Silverchannels + earned commissions (wallet)
- Request payout when balance is available

## Registration Workflow
- Silverchannel registers -> status PENDING_REVIEW
- Super Admin approves -> status ACTIVE, referral linkage locked
- Reject -> keep record + reason

## Order Workflow (High-level)
Statuses:
- DRAFT -> SUBMITTED -> WAITING_PAYMENT -> WAITING_VERIFICATION (manual)
- PAID -> PACKING -> SHIPPED -> DELIVERED
Exceptions:
- CANCELLED, REFUNDED, RETURN_REQUESTED, RETURNED

## Referral & Commission Rules
Commission types:
1) Registration commission (CPL)
   - Trigger: referred Silverchannel is approved and meets requirement
2) Transaction commission (CPA / Revenue share)
   - Trigger: referred Silverchannel order is PAID (or DELIVERED depending config)

Ledger-based wallet:
- Every commission is recorded in CommissionLedger
- Status: PENDING -> AVAILABLE (after holding period) -> PAID
- Holding period default: 14 days (configurable)
- If refund/return happens before AVAILABLE, commissions should be cancelled/reversed.

## Non-functional Requirements
- Audit-ready: immutable ledger entries + admin actions logged
- Secure: role-based access control, rate limiting for auth endpoints
- Scalable: modular architecture, clear separation between domain modules
- UX: simple and fast for non-technical users

## Assumptions
- Initial version supports manual bank transfer + upload proof
- Later can integrate payment gateway and shipping API
- Multi-level referral can be added later, but MVP is single-level referral (Promotor only)
