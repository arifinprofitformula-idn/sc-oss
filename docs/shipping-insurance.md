# Shipping Insurance Feature Documentation

## Overview
Fitur Shipping Insurance memungkinkan Super Admin untuk menetapkan biaya asuransi pengiriman yang **wajib** dibayarkan oleh Silverchannel (distributor) saat melakukan checkout, jika fitur ini diaktifkan. Biaya asuransi dihitung sebagai persentase dari total subtotal produk.

## Configuration (Admin)
Pengaturan asuransi dapat diakses dan diubah oleh Super Admin melalui:
**Menu:** Integration System > Shipping  
**URL:** `/admin/integrations/shipping`

### Fields
1.  **Enable Shipping Insurance**: Toggle untuk mengaktifkan/menonaktifkan fitur secara global. Jika aktif, semua checkout **wajib** membayar asuransi.
2.  **Insurance Percentage (%)**: Persentase biaya asuransi dari total harga barang (contoh: 0.2 untuk 0.2%).
3.  **Insurance Description**: Deskripsi singkat yang akan muncul di halaman checkout (contoh: "Layanan Asuransi Wajib (LM)").

### Audit Log
Setiap perubahan pada konfigurasi ini akan dicatat dalam `system_logs` dengan detail:
-   **User**: Admin yang melakukan perubahan.
-   **Action**: `UPDATE_SHIPPING_INSURANCE`.
-   **Data**: Nilai lama dan nilai baru.

---

## Frontend Implementation (Checkout)
Fitur ini diimplementasikan di halaman checkout (`resources/views/silverchannel/checkout/index.blade.php`) menggunakan **Alpine.js** untuk kalkulasi real-time.

### Logic
1.  **Initialization**: Data pengaturan dikirim dari backend via variabel `checkoutBootstrap`.
    ```javascript
    insuranceSettings: {
        active: true/false,
        percentage: 0.5, // float
        description: "..."
    }
    ```
2.  **Calculation**:
    ```javascript
    // Dihitung otomatis jika active = true
    insuranceCost = subtotal * (percentage / 100)
    ```
3.  **Grand Total**:
    ```javascript
    grandTotal = subtotal + shippingCost + uniqueCode + insuranceCost
    ```

### UI Components
-   **Informasi Wajib**: Jika fitur aktif, muncul panel informasi "Biaya Asuransi Pengiriman (LM)" yang tidak bisa di-uncheck.
-   **Summary**: Baris tambahan "Biaya Asuransi (LM)" muncul di ringkasan pembayaran.

---

## Backend Implementation

### Database
-   **Table**: `orders`
-   **Column**: `insurance_amount` (DECIMAL 18,2, default: 0)
-   **Migration**: `2026_01_22_100000_add_insurance_amount_to_orders_table.php`

### Service Layer (`OrderService`)
Logika penyimpanan order menangani perhitungan ulang di sisi server untuk keamanan.

```php
// CheckoutController Logic
$insuranceActive = (bool) $integrationService->get('shipping_insurance_active', 0);
if ($insuranceActive) {
    $percentage = IntegrationService::get('shipping_insurance_percentage');
    $insuranceAmount = $subtotal * ($percentage / 100);
}
// Total Amount disimpan termasuk insuranceAmount
```

### Controller (`CheckoutController`)
Memastikan biaya asuransi selalu ditambahkan jika fitur diaktifkan di pengaturan global, mengabaikan input opsional dari user.

---

## Testing
Fitur ini telah diuji dengan cakupan berikut:
1.  **Unit Test** (`OrderServiceInsuranceTest`): Verifikasi logika matematika perhitungan asuransi.
2.  **Feature Test** (`ShippingIntegrationTest`): Verifikasi validasi input admin dan penyimpanan konfigurasi.
3.  **View Test** (`CheckoutViewTest`): Verifikasi data settings ter-passing dengan benar ke Blade view.
