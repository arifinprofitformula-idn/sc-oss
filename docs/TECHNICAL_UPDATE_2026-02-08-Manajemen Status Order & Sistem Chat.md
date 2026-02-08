# Update Teknis: Manajemen Status Order, Sistem Chat & Status Tiket
**Tanggal:** 08 Februari 2026
**Penulis:** Arva EPI OSS Builder

## 1. Ringkasan
Update ini menghadirkan alur **Manajemen Status Order** yang lebih kuat, **Sistem Chat/Pengaduan** real-time, serta **Manajemen Status Tiket** yang komprehensif untuk pelacakan isu yang lebih baik.

## 2. Fitur Baru

### A. Manajemen Status Order
- **Transisi "Delivered" (Diterima)**: 
  - Silverchannel hanya dapat menandai order sebagai `DELIVERED` jika status saat ini adalah `SHIPPED` (Dikirim).
  - **Syarat**: Wajib mengunggah foto "Bukti Penerimaan" (Proof of Delivery).
- **Bukti Penerimaan**:
  - Penyimpanan: `storage/app/public/proofs`
  - Validasi: Hanya gambar (jpg/png), Maksimal 5MB.
- **Audit Log**: Semua perubahan status tercatat di tabel `order_logs`.

### B. Sistem Chat (Pengaduan)
- **Cakupan**: Komunikasi langsung antara Silverchannel dan Admin terkait Order spesifik.
- **Akses**:
  - Silverchannel: `/silverchannel/orders/{id}/chat`
  - Admin: `/admin/orders/{id}/chat`
- **Implementasi**: 
  - **Database**: Tabel `chat_messages` terhubung ke `orders` dan `users`.
  - **Real-time**: Menggunakan teknik **HTTP Polling (Interval 5 detik)** via Alpine.js.

### C. Manajemen Status Tiket (Support Issue Management)
Sistem pelacakan status tiket bantuan dengan 7 status yang terdefinisi.

1.  **Definisi Status**:
    -   **Open**: Tiket baru masuk (Default).
    -   **Pending**: Menunggu respon pelanggan.
    -   **On Progress**: Sedang ditangani tim.
    -   **Escalated**: Dinaikkan ke level support lebih tinggi.
    -   **Resolved**: Solusi diberikan, menunggu konfirmasi.
    -   **Closed**: Tiket selesai. **User tidak bisa mengirim pesan baru**.
    -   **Reopened**: Tiket dibuka kembali dari status Closed.

2.  **Fitur UI/UX**:
    -   **Admin**: 
        -   Dropdown status dengan **Auto-Scroll Reset** dan tampilan maksimal (60vh) untuk kemudahan navigasi.
        -   **Mandatory Comment**: Wajib memberikan alasan/komentar saat mengubah status menjadi `Closed`.
    -   **Silverchannel**:
        -   Visual badge status dengan warna dan deskripsi jelas.
        -   Blokir input pesan saat status `Closed`.

### D. Periode Holding Komisi (Commission Holding Period)
- Dapat dikonfigurasi via Admin Panel: `Store Settings > Payment`.
- Setting dinamis tersimpan di `system_settings` (key: `commission_holding_period`).
- Default: 7 hari. Range: 0-90 hari.

---

## 3. Arsitektur Teknis

### Perubahan Skema Database
```sql
-- Tabel Baru: chat_messages
CREATE TABLE `chat_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `message` text,
  `attachment_path` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- Tabel Baru: support_status_histories (Audit Trail Status Tiket)
CREATE TABLE `support_status_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL, -- Admin yang mengubah
  `old_status` varchar(50) NOT NULL,
  `new_status` varchar(50) NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- Modifikasi Tabel: orders
ALTER TABLE `orders` 
ADD `proof_of_delivery` VARCHAR(255) NULL AFTER `shipping_tracking_number`,
ADD `support_status` VARCHAR(50) DEFAULT 'open',
ADD `support_closed_at` TIMESTAMP NULL;
```

### Class Penting
- **Controllers**:
  - `App\Http\Controllers\Silverchannel\ChatController`: Menangani chat sisi SC & validasi blokir status Closed.
  - `App\Http\Controllers\Admin\ChatManagementController`: Menangani chat admin, update status tiket (`updateStatus`), dan riwayat.
  - `App\Http\Controllers\Silverchannel\OrderController`: Menangani `markAsDelivered`.
- **Services**:
  - `App\Services\OrderService`: Logika update status order.
- **Tests**:
  - `tests/Feature/SupportSystemTest.php`: Validasi flow status tiket dan permission.

---

## 4. Roadmap Scaling (Panduan Developer Masa Depan)

Ketika volume pengguna meningkat (1000+ user bersamaan), mekanisme **Polling** saat ini akan memberatkan server. Ikuti roadmap ini untuk upgrade:

### Fase 1: Migrasi ke WebSocket (Laravel Reverb)
1.  **Install Reverb**: `php artisan install:broadcasting`
2.  **Buat Event**: `App\Events\MessageSent` yang mengimplementasikan `ShouldBroadcast`.
3.  **Update Controller**: Ganti return save DB langsung dengan `broadcast(new MessageSent($message))->toOthers();`.
4.  **Update Frontend**: Ganti polling `setInterval` dengan Laravel Echo:
    ```javascript
    Echo.private(`order.${orderId}`)
        .listen('MessageSent', (e) => { this.messages.push(e); });
    ```

### Fase 2: Optimasi Infrastruktur
1.  **Queue Driver**: Ubah dari `sync` ke `redis` di `.env`.
2.  **Database**:
    - Pastikan index pada `chat_messages(order_id, created_at)`.
    - Implementasikan partitioning untuk `chat_messages` jika baris melebihi 5 juta.

### Fase 3: Strategi Arsip
- Buat scheduled job (`php artisan schedule:work`) untuk memindahkan riwayat chat order yang sudah selesai (>3 bulan) ke tabel `chat_archives` (Cold Storage).

---

## 5. Troubleshooting

### Error SMTP (Email)
Jika menemukan `TransportException` atau `Authentication failed` saat update nomor resi:
- **Penanganan Otomatis**: Sistem didesain untuk **menangkap dan mencatat (log)** error ini tanpa menghentikan request user. Cek `storage/logs/laravel.log`.
- **Lingkungan Dev**: Set `MAIL_MAILER=log` di `.env` untuk mematikan pengiriman email asli.

### Chat Tidak Update
- Cek console browser untuk error JavaScript.
- Pastikan interval polling di `chat.blade.php` aktif.
- Verifikasi parameter `last_id` terkirim dengan benar di request API.

### Status Tiket Tidak Berubah
- Pastikan user memiliki role `SUPER_ADMIN` atau permission yang sesuai.
- Cek validasi komentar jika mengubah status ke `Closed`.
