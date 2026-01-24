# Technical Update: Store Settings Menu Restructuring
Date: 2026-01-13

## Overview
Restructured the "Store Settings" menu access to provide better control during development and rollout. The menu is now hidden by default for Silverchannel users and can be toggled by Super Admin.
Additionally, the Super Admin "Store Settings" page now includes a comprehensive form to manage the **Platform/Center Store** details (Identity, Contact, Hours, Payment, Shipping, Extra Features) alongside the access toggle.

## Changes

### 1. Access Control
- **Silverchannel**: The "Store Settings" menu is **hidden by default**.
- **Super Admin**: Added a new "Store Settings" menu in the dashboard to manage global store configuration.

### 2. Global Store Settings (Admin)
- **Route**: `/admin/settings/store`
- **Controller**: `App\Http\Controllers\Admin\GlobalStoreSettingController`
- **Features**:
  - **Toggle**: "Aktifkan Menu Store Settings untuk Silverchannel".
  - **Store Configuration**: Full form to manage Platform Store Identity, Contact (RajaOngkir integrated), Hours, Payment, Shipping, and Extra Features.
  - **Audit Log**: All changes to store settings and toggle are logged in `audit_logs`.

### 3. Implementation Details
- **Database**: 
  - Uses `system_settings` table (via `IntegrationService`) to store the toggle state (`silverchannel_store_menu_active`).
  - Uses `stores` table to store the Platform Store details (linked to Super Admin user).
  - Uses `audit_logs` table to track changes.
- **Middleware/Logic**:
  - `StoreSettingController@edit` and `update` methods check the system setting before allowing access for Silverchannel.
  - `navigation.blade.php` conditionally renders the menu link based on the setting.
  - Admin form uses `PATCH` method and validates all required store fields.

## Verification Steps

### Admin Side
1. Login as Super Admin.
2. Navigate to "Store Settings" in the main menu.
3. Verify the **Toggle** is present at the top.
4. Verify the **Tabs** (Identitas, Kontak, Jam Operasional, Pembayaran, Pengiriman, Fitur Tambahan) are working.
5. Fill in the form and Save.
6. Verify validation errors appear if required fields are missing.
7. Verify changes are saved and Audit Log is created.

### Silverchannel Side
1. Login as Silverchannel (User must have complete profile > 70%).
2. **If Disabled**: The "Store Settings" menu should NOT appear in the top navigation or dropdown. Accessing `/silverchannel/store/settings` manually should return 403.
3. **If Enabled**: The "Store Settings" menu should appear. Access should be granted.

## Testing
Run the feature tests:
```bash
php artisan test tests/Feature/StoreSettingMenuTest.php
php artisan test tests/Feature/AdminGlobalStoreSettingTest.php
```
