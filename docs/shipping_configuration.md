# Shipping Configuration Guide

## Overview
The shipping configuration system allows administrators to manage active shipping couriers globally and configure specific couriers for each Silverchannel store. This ensures that Silverchannel owners can only offer shipping options that are supported by the system and authorized by the Super Admin.

## Database Schema

### `stores` Table
A new JSON column `shipping_couriers` has been added to the `stores` table.
- **Column**: `shipping_couriers` (JSON, nullable)
- **Description**: Stores an array of courier codes (e.g., `['jne', 'sicepat']`) that are active for the specific store.
- **Behavior**: If `null`, the store uses the global default configuration. If empty array `[]`, no couriers are active.

### `system_settings` Table
Global settings are stored in the `system_settings` table (managed via `IntegrationService`).
- **Key**: `shipping_active_couriers`
- **Value**: JSON string of globally active courier codes.

## Features

### 1. Global Shipping Configuration
- **Location**: Admin Dashboard > Integrations > Shipping
- **Function**: Allows Super Admin to select which couriers are available system-wide (based on API provider support).
- **Storage**: Saved as `shipping_active_couriers` in system settings.

### 2. Silverchannel Store Configuration
- **Location**: Admin Dashboard > Integrations > Shipping > Silverchannel Configuration
- **Function**: Allows Super Admin to override or subset the available couriers for a specific Silverchannel store.
- **Validation**: When updating a store's couriers, the system validates that the selected couriers are present in the Global Active Couriers list.
- **UI**: A table listing all stores with an "Edit" button that opens a modal to select couriers.

### 3. Checkout Implementation
The checkout process (`CheckoutController`) has been updated to respect these configurations:

1.  **Retrieve Global Couriers**: Fetches `shipping_active_couriers`. Fallbacks to `rajaongkir_couriers` or default list if not set.
2.  **Retrieve Store Config**: Fetches the authenticated user's store configuration.
3.  **Intersect**: 
    - If the store has specific `shipping_couriers` set, the available couriers are the **intersection** of Store Couriers and Global Couriers.
    - This ensures that if a courier is disabled globally, it is automatically disabled for all stores, even if specifically enabled in their config.
4.  **Validation**: The `calculateShipping` endpoint validates the requested courier against this computed allowed list.

## Technical Implementation Details

### Models
- **Store**: Added `shipping_couriers` to `$fillable` and `$casts` (as `array`).

### Controllers
- **Admin\IntegrationController**:
    - `shipping()`: Passes `$activeCouriers` (global) and `$stores` to the view.
    - `updateStoreShipping()`: Handles store-specific updates with validation against global couriers.
- **Silverchannel\CheckoutController**:
    - `index()`: Filters available couriers for the checkout view based on store config.
    - `calculateShipping()`: Validates the selected courier before calling the API.

### Frontend
- **Alpine.js**: Used for the modal interaction in the Admin Shipping page.
- **Blade**: Renders the configuration forms and tables.

## Future Improvements
- Add a dedicated settings page for Silverchannel owners to manage their own couriers (limited to the subset allowed by Admin).
- Add support for courier service levels (e.g., enable JNE REG but disable JNE YES).
