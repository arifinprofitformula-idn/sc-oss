# Audit & Perbaikan Alur Forgot Password (Lupa Password)

**Tanggal:** 2026-02-12  
**Auditor:** Arva EPI OSS Builder

## 1. Ringkasan Eksekutif
Audit menyeluruh telah dilakukan pada fitur Forgot Password di sistem SC-OSS. Ditemukan bahwa **mekanisme pengiriman email saat ini mengalami kegagalan autentikasi SMTP**, yang menjadi penyebab utama pengguna tidak menerima email reset password.

Selain perbaikan konfigurasi, kode sumber telah ditingkatkan untuk menangani kegagalan dengan lebih elegan (graceful degradation), pencatatan log (logging) yang lebih detail, dan mekanisme percobaan ulang (retry mechanism) otomatis.

## 2. Temuan Utama (Findings)

### 2.1. Kegagalan Koneksi SMTP (Critical)
*   **Masalah:** Percobaan pengiriman email via SMTP Brevo gagal dengan error `535 5.7.8 Authentication failed`.
*   **Update Terkini (Investigasi Lanjutan - Tahap 2):** 
    *   **Identifikasi Username:** Berdasarkan screenshot user, ditemukan bahwa username SMTP yang benar adalah `9fc2f6001@smtp-brevo.com`, BUKAN `email.epiteam@gmail.com`.
    *   **Tes Ulang:** Database telah diupdate dengan username yang benar dan dicoba koneksi ulang menggunakan API Key v3 (`xkeysib-...`).
    *   **Hasil:** Tetap GAGAL (`535 5.7.8 Authentication failed`).
    *   **Analisis Akar Masalah (Final):**
        *   Username sudah benar (`9fc2f6001@smtp-brevo.com`).
        *   Password yang digunakan adalah **API Key v3** (`xkeysib-...`), namun screenshot menunjukkan adanya **SMTP Key** spesifik (berakhiran `...TxzS0p`).
        *   Brevo memisahkan API Key dan SMTP Key. Untuk login SMTP spesifik ini, **Password HARUS menggunakan nilai SMTP Key**, bukan API Key.
*   **Tindakan:** Integrasi Brevo kembali **dimatikan sementara** (`brevo_active=0`) agar sistem menggunakan driver `log` dan tidak crash.

### 2.2. Kurangnya Penanganan Error (Error Handling)
*   **Masalah:** Controller `PasswordResetLinkController` dan `NewPasswordController` sebelumnya tidak menangkap *exception* saat proses pengiriman email atau reset password.
*   **Dampak:** Jika SMTP error, pengguna mungkin melihat halaman error sistem ("Whoops, something went wrong") alih-alih pesan yang ramah pengguna.

### 2.3. Mekanisme Retry Tidak Aktif
*   **Masalah:** Notifikasi `ResetPasswordNotification` menggunakan antrian (`ShouldQueue`), tetapi tidak memiliki konfigurasi eksplisit untuk `tries` (jumlah percobaan) dan `backoff` (jeda waktu).
*   **Dampak:** Jika terjadi *network glitch* sesaat, email akan langsung gagal tanpa dicoba kembali secara otomatis.

### 2.4. Template Email Fallback
*   **Observasi:** Sistem memiliki logika untuk menggunakan template kustom dari database (`EmailTemplate`), namun logikanya perlu diperkuat agar jika template hilang/terhapus, sistem tetap bisa mengirim email default Laravel.

## 3. Solusi & Perbaikan yang Diimplementasikan

### 3.1. Peningkatan Kode Sumber

#### A. `App\Notifications\ResetPasswordNotification`
*   **Retry Mechanism:** Menambahkan konfigurasi `$tries = 3` dan `$backoff = 60` detik. Jika pengiriman gagal, sistem akan mencoba lagi hingga 3 kali dengan jeda 1 menit.
*   **Logging:** Menambahkan log `info` saat persiapan email, `warning` jika template tidak ditemukan, dan `error` jika terjadi *exception*.
*   **Failed Handler:** Menambahkan method `failed()` untuk mencatat log level `critical` jika semua percobaan gagal.
*   **Variable Substitution:** Memastikan variabel tambahan seperti `{{logo_url}}`, `{{support_email}}`, dan `{{year}}` tersedia untuk template.

#### B. `App\Http\Controllers\Auth\PasswordResetLinkController`
*   **Try-Catch Block:** Membungkus proses `Password::sendResetLink` dalam blok `try-catch`.
*   **User Feedback:** Jika terjadi error teknis, pengguna akan melihat pesan: *"Unable to send password reset link. Please verify your email configuration or try again later."* alih-alih error 500.

#### C. `App\Http\Controllers\Auth\NewPasswordController`
*   **Try-Catch Block:** Membungkus proses `Password::reset` untuk menangani kegagalan saat update database.
*   **Logging:** Mencatat error spesifik jika reset gagal.

### 3.2. Pengujian (Testing)
*   Dibuat file test baru: `tests/Feature/Auth/ForgotPasswordAuditTest.php`.
*   **Hasil Test:** PASS (Menggunakan driver `log`).
    1.  Verifikasi notifikasi terkirim dengan variabel yang benar.
    2.  Verifikasi penanganan error saat SMTP mati (simulasi exception).
    3.  Verifikasi validasi email tidak terdaftar.

## 4. Rekomendasi & Tindakan Selanjutnya (Action Items)

Agar fitur ini berfungsi 100% di production, Administrator/Developer **WAJIB** melakukan langkah berikut:

1.  **Dapatkan Password SMTP yang Benar**:
    *   Password saat ini (`xkeysib-...`) adalah API Key, sedangkan server membutuhkan **SMTP Key**.
    *   Sesuai screenshot Anda, ada SMTP Key bernama "Silvergram OSS" yang berakhir dengan `...TxzS0p`.
    *   **TIDAK MUNGKIN** melihat nilai penuh key tersebut di dashboard Brevo setelah dibuat. Anda harus **membuat key baru**.
    *   Klik tombol **"Generate a new SMTP key"** di pojok kanan atas halaman Brevo tersebut.
    *   Beri nama key (misal: "SC OSS SMTP V2").
    *   **COPY** nilai key yang muncul (hanya muncul sekali!). Ini adalah password Anda.

2.  **Update Pengaturan di SC-OSS**:
    *   Masuk ke **Admin > Integration > Brevo**.
    *   **SMTP Login Email:** Pastikan isinya `9fc2f6001@smtp-brevo.com` (sudah saya update di database, tapi cek lagi).
    *   **API Key (v3):** Masukkan **SMTP Key Baru** yang Anda copy tadi di field ini. (Meskipun labelnya API Key, sistem akan menggunakannya sebagai password SMTP).
    *   Klik **Save Changes** lalu **Test Connection**.

3.  **Pastikan Queue Worker Berjalan**:
    *   Jalankan: `php artisan queue:work`

---
**Status Audit:** COMPLETED (Code Logic Verified & Tests Passed).
**Status Integrasi:** PENDING (SMTP Authentication Failed - Wrong Password Type).
