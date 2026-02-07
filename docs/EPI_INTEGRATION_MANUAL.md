# Manual Integrasi & Operasional: Update Harga Otomatis EPI APE

## 1. Ikhtisar Sistem
Sistem ini mengotomatisasi pembaruan harga "Distributor Price (Silverchannel)" pada aplikasi EPI-OSS dengan mengambil data dari API EPI APE. Sistem berjalan secara terjadwal (background job) untuk memastikan harga selalu terupdate.

### Fitur Utama
- **Update Otomatis**: Mengambil data harga secara berkala.
- **Validasi Data**: Memastikan harga valid (angka positif).
- **Versioning**: Mencegah update harga lama menimpa harga baru.
- **Audit Trail**: Mencatat riwayat perubahan harga di database.
- **Notifikasi**: Memberikan alert jika perubahan harga > 10%.
- **Retry Mechanism**: Mencoba ulang otomatis jika terjadi kegagalan jaringan/database.

## 2. Alur Kerja Data (Data Flow)
1. **Scheduler** menjalankan perintah `app:fetch-epi-prices` (misal: setiap 5 menit).
2. **Command** memanggil `EpiApePriceService` untuk mengambil data dari API EPI.
3. **Service** mem-parse data dan membuat `UpdateProductPriceJob` untuk setiap SKU.
4. **Job** diproses oleh antrian (Queue Worker):
   - Validasi harga.
   - Cek SKU di database.
   - Cek timestamp (versioning).
   - Update harga di tabel `products`.
   - Simpan log di tabel `product_price_histories`.
   - Log alert jika perubahan harga signifikan.

## 3. Panduan Operasional

### A. Menjalankan Scheduler
Pastikan cron job server aktif. Command yang dijalankan oleh scheduler adalah:
```bash
php artisan app:fetch-epi-prices
```
Untuk testing manual, Anda bisa menjalankan perintah tersebut di terminal.

### B. Monitoring Status Update
1. **Cek Log Laravel**:
   Buka file `storage/logs/laravel.log`. Cari keyword "FetchEpiPricesCommand" atau "Job started for SKU".
   - Sukses: "Price updated for SKU: X"
   - Gagal: "Job failed for SKU: X" atau "Failed to update price..."
   
2. **Cek Database**:
   - Tabel `products`: Kolom `price_silverchannel`, `last_price_update_at`, `price_source`.
   - Tabel `product_price_histories`: Riwayat perubahan harga lengkap.

### C. Troubleshooting
- **Harga Tidak Berubah**:
  - Cek apakah SKU di API EPI sama persis dengan di database EPI-OSS.
  - Cek timestamp data dari API. Jika timestamp lebih lama dari `last_price_update_at` di database, update akan ditolak (skipped).
- **Job Gagal Terus**:
  - Cek koneksi database.
  - Sistem akan mencoba ulang (retry) 3 kali. Jika tetap gagal, job akan masuk ke `failed_jobs`. Cek tabel `failed_jobs` untuk detail error.

## 4. Struktur Kode (Untuk Developer)
- **Service**: `App\Services\EpiApePriceService.php` (Core Logic)
- **Job**: `App\Jobs\UpdateProductPriceJob.php` (Queue & Retry)
- **Command**: `App\Console\Commands\FetchEpiPricesCommand.php` (Entry Point)
- **Model**: `App\Models\ProductPriceHistory.php` (Audit Log)
- **Migration**: 
  - `create_product_price_histories_table`
  - `add_price_versioning_to_products_table`

## 5. API Mocking
Saat ini `EpiApePriceService` menggunakan data mock. Untuk menghubungkan ke API nyata:
1. Buka `App\Services\EpiApePriceService.php`.
2. Edit method `fetchPrices()`.
3. Ganti array statis dengan `Http::get('url-api-anda')->json()`.
