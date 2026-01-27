# Package Management Feature Documentation

## Overview
This feature allows administrators to manage registration packages with enhanced visual presentation and pricing strategies. It includes package photo uploads with aspect ratio validation (1:1 or 3:4) and "Normal Price" (MSRP) support for promotional displays.

## Database Schema Changes
**Table:** `packages`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `image` | `string` | Yes | Path to the uploaded package image (stored in `public/packages`). |
| `original_price` | `decimal(18,2)` | Yes | The "Normal Price" or MSRP. If set and higher than `price`, it will be shown as crossed out. |
| `price` | `decimal(18,2)` | No | The actual selling price (transaction amount). |

## API / Controller Modifications

### Admin Package Controller (`App\Http\Controllers\Admin\PackageController`)
- **Store (`POST /admin/packages`)**:
    - Handles file upload for `image`.
    - Validates aspect ratio (1:1 or 3:4).
    - Validates max size (2MB) and format (JPG, PNG, WEBP).
- **Update (`PUT /admin/packages/{package}`)**:
    - Handles file upload (replaces existing image).
    - Updates `original_price`.

### Validation Logic
A custom validation closure is used for the image aspect ratio:
```php
function ($attribute, $value, $fail) {
    if ($value) {
        $image = getimagesize($value->getRealPath());
        if ($image) {
            $width = $image[0];
            $height = $image[1];
            $ratio = $width / $height;
            $epsilon = 0.01;
            
            $is1x1 = abs($ratio - 1) < $epsilon;
            $is3x4 = abs($ratio - 0.75) < $epsilon;

            if (!$is1x1 && !$is3x4) {
                $fail('Rasio aspek gambar harus 1:1 atau 3:4.');
            }
        }
    }
}
```

## Usage Guide for Administrators

### 1. Uploading Package Photo
- Go to **Admin Dashboard > Manage Packages**.
- Click **Create New** or **Edit** an existing package.
- In the "Foto Paket" field, upload an image.
- **Requirements**:
    - Format: JPG, PNG, or WebP.
    - Size: Max 2MB.
    - Ratio: Must be Square (1:1) or Portrait (3:4).
- The system will automatically preview the image or show an error if the ratio is incorrect.

### 2. Setting Promotional Prices
- **Harga Normal (Rp)**: Enter the original price (e.g., 750.000). This is optional.
- **Harga Jual / Promo (Rp)**: Enter the actual price the user pays (e.g., 500.000).
- **Display Logic**:
    - If "Harga Normal" is set and is higher than "Harga Jual", the Registration Page will show:
        - A "PROMO" badge.
        - The Normal Price crossed out.
        - The Selling Price as the main price.
        - A "Hemat Rp X" badge.
    - If "Harga Normal" is empty or lower, only the Selling Price is shown.

## Registration Page Display
- The package card on `/registrasi` now features the uploaded image.
- If no image is uploaded, a default placeholder is shown.
- The layout is responsive, ensuring the image looks good on mobile and desktop.
