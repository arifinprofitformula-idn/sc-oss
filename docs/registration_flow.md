# Silverchannel Registration System Documentation

## Overview
The Silverchannel Registration System allows users to sign up as Silverchannels (distributors). **Registration is now exclusive and invitation-only.** Users must possess a valid referral link or code to access the registration page. Public registration is disabled. The system includes referral tracking, package selection, payment proof upload, and an admin verification workflow.

## Technical Architecture

### 1. Affiliate Tracking & Access Control
- **Middleware**: `App\Http\Middleware\ValidateReferral` (Blocking) & `App\Http\Middleware\TrackReferral` (Tracking)
- **Mechanism**: 
  - Captures `?ref=CODE` from URL or `referral_code` from Cookie.
  - **Validation**: Checks if the code exists in the `users` table.
  - **Enforcement**: 
    - If code is missing or invalid: Returns **403 Forbidden**.
    - If code is valid: Allows access and queues a cookie (30 days).
- **Usage**: Automatically populates and locks the referral code field (hidden) in the registration form.

### 2. Registration Flow
The flow consists of 3 steps managed by `SilverChannelRegistrationController`:

1.  **Step 1: Data Entry** (`GET /register`, `POST /register`)
    - **Pre-requisite**: Valid `ref` parameter or cookie.
    - Collects user data (Name, NIK, Email, WhatsApp, Address).
    - Validates inputs (Unique Email/NIK, Valid Referral Code).
    - Stores data in Session (`silver_registration_data`).

2.  **Step 2: Checkout** (`GET /register-silver/checkout/{token}`)
    - Displays selected package and order summary.
    - Shows payment instructions (Bank Transfer).

3.  **Step 3: Payment & Submission** (`POST /register-silver/payment/{token}`)
    - Uploads payment proof (Image).
    - **Database Transaction**:
        - Creates `User` (Status: `WAITING_VERIFICATION`).
        - Assigns Role: `SILVERCHANNEL`.
        - Creates `Order` (Status: `WAITING_VERIFICATION`, Payment Status: `PAID` pending verify).
        - Creates `Payment` (Status: `PENDING_VERIFICATION`).
        - Logs activity to `AuditLog`.
    - **Notifications**:
        - Sends `RegistrationPendingApproval` email to User.
        - Sends `NewRegistrationAlert` email to Super Admins.

### 3. Database Schema Updates
- **Users**: Added `silver_channel_id`, `referral_code`, `referrer_id`, `status`.
- **Orders**: Linked to User.
- **OrderItems**: Made `product_id` nullable to support Package-only orders.
- **Packages**: Stores registration package details (Price, Benefits).

## Admin Guide

### Monitoring Registrations
1.  Go to **Admin Dashboard > Manage Silverchannels**.
2.  Use the **Status Filter** dropdown to select **Waiting Verification**.
3.  You will see a list of new registrations.

### Verification Process
1.  **Verify Payment**:
    - Go to **Payment Management** (or check the email alert for details).
    - Verify the uploaded proof against bank mutation.
    - Mark Payment as **Verified**. This updates Order to **PAID**.
2.  **Approve User**:
    - Go back to **Manage Silverchannels**.
    - Click **Approve** on the user row.
    - This sets User Status to **ACTIVE**.
    - Triggers `SilverchannelApproved` event (Awards Commission to Referrer).

## Affiliate Instructions

### How to Refer
1.  **Get your Code**: Your Referral Code is your **Silverchannel ID** (e.g., `EPISCAB1234`).
2.  **Share Link**: Append `?ref=YOURCODE` to the registration URL.
    - Example: `https://sc-oss.test/register?ref=EPISCAB1234`
3.  **Tracking**:
    - When someone clicks your link, the system validates the code immediately.
    - If valid, a cookie is saved for 30 days.
    - The user can proceed to register.
4.  **Commission**:
    - You receive a Registration Commission (CPL) when the admin approves the new Silverchannel.
    - You receive Transaction Commissions (CPA) on their future orders.
