# Enhanced Password Reset System Documentation

## Overview
Sistem reset password yang baru dan lebih solid telah diimplementasikan untuk menggantikan alur yang saat ini bermasalah. Sistem ini mencakup validasi yang ketat, rate limiting, error handling yang komprehensif, dan keamanan yang ditingkatkan.

## API Endpoints

### 1. Request Password Reset
**Endpoint:** `POST /forgot-password/enhanced`
**Description:** Mengirim permintaan reset password dengan validasi email yang ketat dan rate limiting.

**Request Body:**
```json
{
    "email": "user@example.com"
}
```

**Response Success:**
- Redirect ke `/password-reset-confirmation` dengan pesan sukses
- Rate limit: 3 kali per jam per email
- Token kadaluarsa: 1 jam

**Response Error:**
- Rate limit exceeded: 422 dengan pesan "Terlalu banyak percobaan. Silakan coba lagi dalam X detik."
- Email invalid: 422 dengan validasi error
- Email service error: Redirect ke `/password-reset-error`

### 2. Reset Password Form
**Endpoint:** `GET /reset-password/enhanced/{token}`
**Description:** Menampilkan form reset password dengan validasi token.

**Parameters:**
- `token`: Reset password token (64 karakter)
- `email`: Email address (query parameter)

**Response:**
- Token valid: Form reset password dengan indikator kekuatan password
- Token invalid/expired: Redirect ke halaman error khusus

### 3. Submit New Password
**Endpoint:** `POST /reset-password/enhanced`
**Description:** Mengirim password baru dengan validasi kekuatan password.

**Request Body:**
```json
{
    "token": "64-character-token",
    "email": "user@example.com",
    "password": "NewStrongPass123!",
    "password_confirmation": "NewStrongPass123!"
}
```

**Password Requirements:**
- Minimal 8 karakter
- Mengandung huruf besar dan kecil
- Mengandung angka
- Mengandung karakter spesial (@$!%*?&)
- Password confirmation harus cocok

**Response Success:**
- Redirect ke login dengan pesan sukses
- Semua session diinvalidate
- Token dihapus dari database

## Konfigurasi .env

### Email Configuration
```env
# Email Provider Selection
EMAIL_PROVIDER=mailketing
EMAIL_ROUTE_AUTH=mailketing

# Mailketing API Configuration
MAILKETING_API_TOKEN=your_mailketing_api_token_here
MAILKETING_SENDER_EMAIL=email@bisnisemasperak.com
MAILKETING_SENDER_NAME="SC OSS"

# Queue Configuration
QUEUE_CONNECTION=database
QUEUE_DEFAULT=default
QUEUE_EMAILS=emails
```

### Rate Limiting Configuration
```env
# Rate Limiting
RATE_LIMIT_PASSWORD_RESET=3,60 # 3 attempts per hour
RATE_LIMIT_EMAIL_TEST=5,1 # 5 attempts per minute
```

### Password Reset Configuration
```env
# Password Reset Settings
PASSWORD_RESET_EXPIRE=60 # minutes (1 hour)
PASSWORD_RESET_TOKEN_LENGTH=64 # characters
PASSWORD_RESET_RETRY_ATTEMPTS=3
PASSWORD_RESET_RETRY_DELAY=5 # seconds (exponential backoff base)
```

## Security Features

### 1. Rate Limiting
- Maksimal 3 permintaan reset password per email per jam
- Implementasi menggunakan Laravel RateLimiter
- Pesan error yang informatif dengan countdown timer

### 2. Token Security
- Token 64 karakter alphanumeric
- Token di-hash sebelum disimpan di database
- Token kadaluarsa dalam 1 jam
- Token dihapus setelah digunakan

### 3. Password Validation
- Validasi kekuatan password real-time menggunakan Alpine.js
- Indikator kekuatan password dengan warna (merah → kuning → hijau)
- Validasi server-side dengan regex ketat
- Minimum 8 karakter dengan kombinasi karakter yang kompleks

### 4. Session Management
- Semua session diinvalidate setelah password berhasil direset
- Mencegah akses tidak sah dengan session lama
- Implementasi menggunakan database transaction

### 5. Error Handling
- Retry mechanism untuk email service (maksimal 3 kali)
- Exponential backoff delay (5s, 10s, 15s)
- User-friendly error messages
- Detailed logging untuk debugging

## Database Schema

### password_resets Table
```sql
CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_email_created_at (email, created_at)
);
```

## Email Templates

Sistem ini menggunakan template email `forgot_password` yang ada di database. Template ini harus:
- Mengandung variabel `{{name}}` untuk nama user
- Mengandung variabel `{{reset_url}}` untuk link reset password
- Mengandung variabel `{{count}}` untuk durasi kadaluarsa (60 menit)

## Testing

### Unit Tests
- `EnhancedPasswordResetLinkControllerTest.php`
- `EnhancedNewPasswordControllerTest.php`

### Integration Tests
- `EnhancedPasswordResetFlowTest.php`

### Test Coverage
- Validasi email ketat
- Rate limiting functionality
- Password strength validation
- Token generation and validation
- Error handling scenarios
- Session invalidation
- Email retry mechanism

## Monitoring dan Logging

### Success Logs
- Password reset request berhasil
- Email terkirim
- Password berhasil direset

### Error Logs
- Email service failures
- Invalid token attempts
- Rate limit violations
- Database transaction failures

## User Experience

### 1. Request Flow
1. User mengisi form email di `/forgot-password/enhanced`
2. Sistem validasi email dan rate limit
3. Email dikirim dengan retry mechanism
4. User diarahkan ke halaman konfirmasi
5. User menerima email dengan link reset

### 2. Reset Flow
1. User klik link di email
2. Sistem validasi token dan expiry
3. User mengisi form password baru dengan indikator kekuatan
4. Password diverifikasi (8 karakter, huruf besar/kecil, angka, spesial)
5. Password berhasil direset dan session diinvalidate

### 3. Error Handling
- Rate limit: Pesan dengan countdown timer
- Email invalid: Validasi real-time
- Token invalid: Halaman error khusus dengan penjelasan
- Email service error: Halaman error dengan saran troubleshooting

## Migration Requirements

1. Jalankan migration untuk tabel `password_resets`:
```bash
php artisan migrate
```

2. Pastikan konfigurasi .env sudah benar

3. Jalankan queue worker untuk email processing:
```bash
php artisan queue:work --queue=high,emails,default
```

4. Jalankan tests untuk memastikan semua fitur berfungsi:
```bash
php artisan test --filter=EnhancedPasswordReset
```

## Troubleshooting

### Email tidak terkirim
1. Cek konfigurasi Mailketing API token
2. Cek queue worker status
3. Cek log error di `storage/logs/laravel.log`
4. Verifikasi rate limit tidak terlampaui

### Token invalid
1. Cek apakah token masih dalam batas waktu (1 jam)
2. Cek apakah token sudah pernah digunakan
3. Cek format token (harus 64 karakter)

### Rate limit terlampaui
1. Tunggu sampai batas waktu habis (ditampilkan di error message)
2. Cek konfigurasi rate limit di .env
3. Gunakan email berbeda untuk testing

### Password tidak memenuhi requirement
1. Gunakan minimal 8 karakter
2. Sertakan huruf besar dan kecil
3. Tambahkan angka dan karakter spesial
4. Gunakan indikator kekuatan password untuk guidance