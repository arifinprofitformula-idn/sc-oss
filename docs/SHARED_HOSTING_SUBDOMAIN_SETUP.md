# Panduan Implementasi Subdomain Super Admin di Shared Hosting (cPanel)

Panduan ini menjelaskan langkah-langkah untuk mengaktifkan akses Super Admin melalui `https://oss.silvergram.store/` pada lingkungan Shared Hosting.

## 1. Konsep Utama
Aplikasi ini menggunakan **Single Codebase**. Artinya, domain utama (`silvergram.store`) dan subdomain admin (`oss.silvergram.store`) harus membaca file dari **satu folder project Laravel yang sama**.

**JANGAN** membuat folder project terpisah atau mengupload ulang kodingan untuk subdomain ini.

---

## 2. Langkah-langkah di cPanel

### A. Buat Subdomain
1. Login ke cPanel hosting Anda.
2. Cari menu **Domains** atau **Subdomains**.
3. Klik **Create A New Domain**.
4. Isi konfigurasi berikut:
   - **Domain**: `oss.silvergram.store`
   - **Document Root (PENTING)**: Hapus isian default yang biasanya `home/user/public_html/oss.silvergram.store`.
     > **PERINGATAN**: Jika Anda membiarkan ini default (`home/bisnisem/oss.silvergram.store`), maka subdomain akan membuka folder kosong dan **aplikasi tidak akan jalan**.
   - Ubah **Document Root** agar mengarah **PERSIS SAMA** dengan domain utama Anda.
     - Cari tahu di mana domain utama `silvergram.store` diarahkan (biasanya `/public_html` atau `/public_html/public`).
     - Contoh: Jika domain utama di `home/bisnisem/public_html`, maka subdomain `oss` **HARUS** di `home/bisnisem/public_html` juga.
   
   > **Catatan:** Pastikan keduanya mengarah ke folder `public` di dalam struktur Laravel.

### C. Jika Document Root Terlanjur Salah (Misal: `home/bisnisem/oss.silvergram.store`)
Jika subdomain sudah terlanjur dibuat dengan folder terpisah, aplikasi **TIDAK AKAN JALAN** karena folder tersebut kosong dan tidak terhubung dengan kode Laravel utama.

**Solusi 1 (Ubah Path - Recommended):**
1. Di cPanel, buka menu **Domains**.
2. Klik **Manage** pada baris `oss.silvergram.store`.
3. Ubah kolom **Document Root** menjadi path folder project utama Anda (biasanya `/public_html` atau `/public_html/public`).
4. Klik **Update**.

**Solusi 2 (Hapus & Buat Ulang):**
1. Hapus subdomain `oss.silvergram.store`.
2. Buat ulang, dan **JANGAN KLIK SUBMIT** sebelum mengubah kolom **Document Root** agar mengarah ke folder project utama.

### D. Konfigurasi DNS (Otomatis/Manual)
Biasanya cPanel akan otomatis membuat A Record. Jika tidak:
1. Masuk ke **Zone Editor** di cPanel.
2. Pastikan ada **A Record** untuk `oss.silvergram.store` yang mengarah ke **IP Address** server yang sama dengan domain utama.

---

## 3. Konfigurasi Environment (.env)

Edit file `.env` yang ada di root folder project Laravel Anda (biasanya satu level di atas `public_html` atau di dalam folder project jika struktur folder Anda berbeda).

Pastikan variabel berikut diset dengan benar:

```env
# URL Domain Utama (Public)
APP_URL=https://silvergram.store

# URL Domain Khusus Admin
ADMIN_DOMAIN=oss.silvergram.store

# Konfigurasi Session (PENTING agar login tidak putus)
# Tambahkan titik (.) di depan domain agar cookie bisa dibaca sub-domain
SESSION_DOMAIN=.silvergram.store
```

---

## 4. Verifikasi & Troubleshooting

### Cara Tes:
1. Buka `https://silvergram.store/login` dan login sebagai Super Admin.
2. Setelah login, coba akses `https://oss.silvergram.store/admin/dashboard` (atau menu admin lainnya).
3. Anda seharusnya **tetap login** dan bisa mengakses halaman tersebut.
4. Coba akses `https://silvergram.store/admin/dashboard`. Anda seharusnya mendapatkan error **404 Not Found** (Ini benar, karena admin diproteksi hanya untuk domain `oss`).

### Masalah Umum:
1. **404 Not Found di `oss.silvergram.store`**:
   - Cek **Document Root** subdomain. Pastikan mengarah ke folder `public` Laravel, bukan folder kosong baru.
   
2. **Ter-logout saat pindah ke `oss`**:
   - Cek `SESSION_DOMAIN` di `.env`. Pastikan diawali titik: `.silvergram.store`.
   - Clear browser cache/cookies dan coba login ulang.

3. **500 Server Error**:
   - Cek permission folder `storage` dan `bootstrap/cache` (harus 775 atau 755).
   - Cek log error di `storage/logs/laravel.log`.

### Masalah DNS (Penting!)
Jika muncul error **`DNS_PROBE_FINISHED_NXDOMAIN`** atau **`This site can’t be reached`**:
- **Penyebab**: Domain `oss.silvergram.store` belum terdaftar di sistem DNS global.
- **Solusi**:
  1. Buka cPanel > **Zone Editor**.
  2. Klik **Manage** pada domain `silvergram.store`.
  3. Klik **+ Add Record**.
  4. Masukkan data berikut:
     - **Name**: `oss.silvergram.store.` (akhiri dengan titik jika diminta)
     - **Type**: `A`
     - **Record/Value**: Masukkan **IP Address** hosting Anda (sama dengan IP `silvergram.store`).
  5. Tunggu propagasi DNS (bisa 10 menit hingga 24 jam).

