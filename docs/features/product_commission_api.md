# Product Commission & API Documentation

## Overview
This feature allows Super Admin to configure referral commissions on a per-product basis. Commissions are calculated when an order is paid (PAID status) and distributed to the buyer's referrer.

## Data Model Changes

### Products Table
Added columns:
- `commission_enabled` (boolean, default: false)
- `commission_type` (enum: 'percentage', 'fixed', default: 'percentage')
- `commission_value` (decimal: 15,2, default: 0)

### Commission Logs Table
New table `commission_logs` to track detailed commission breakdown per product in an order.
- `id` (bigint)
- `user_id` (bigint, FK to users) -> The referrer receiving the commission
- `order_id` (bigint, FK to orders)
- `product_id` (bigint, FK to products)
- `commission_amount` (decimal: 15,2)
- `created_at`, `updated_at`

## Commission Logic
1. **Trigger**: Event `OrderPaid` (when order status changes to PAID).
2. **Listener**: `App\Listeners\DistributeOrderCommission`.
3. **Flow**:
   - Check if buyer has a referrer.
   - Iterate through order items.
   - If product has `commission_enabled = true`:
     - Calculate commission based on `commission_type` ('percentage' of item total OR 'fixed' * quantity).
     - Log details to `commission_logs`.
   - Aggregate total commission.
   - Create a single **PENDING** transaction entry in `commission_ledgers` (Wallet) for the referrer.
   - **Note**: Silverchannel registration commission logic was replaced/removed in favor of this product-based transaction commission.

## API Endpoints

Base URL: `/api/admin/products`
Authentication: Bearer Token (Sanctum)
Role Required: `SUPER_ADMIN`

### 1. Create Product with Commission
**POST** `/api/admin/products`

**Body:**
```json
{
    "name": "Product A",
    "sku": "SKU-A",
    "price_silverchannel": 100000,
    "commission_enabled": true,
    "commission_type": "percentage",
    "commission_value": 10
    // ... other product fields
}
```

**Validation:**
- `commission_enabled`: boolean
- `commission_type`: required if enabled, in: 'percentage', 'fixed'
- `commission_value`: required if enabled, numeric, min: 0

### 2. Update Product Commission
**PUT** `/api/admin/products/{id}`

**Body:**
```json
{
    "commission_enabled": true,
    "commission_type": "fixed",
    "commission_value": 5000
}
```

### 3. Get Product Commission Details
**GET** `/api/admin/products/{id}/commission`

**Response:**
```json
{
    "data": {
        "commission_enabled": true,
        "commission_type": "fixed",
        "commission_value": 5000.00
    }
}
```

## Testing

### Unit/Feature Tests
Run the following tests to verify functionality:

```bash
php artisan test --filter "ProductCommissionApiTest|CommissionDistributionTest"
```

- `ProductCommissionApiTest`: Verifies API CRUD operations and validation.
- `CommissionDistributionTest`: Verifies the commission calculation logic and database entries upon OrderPaid event.
