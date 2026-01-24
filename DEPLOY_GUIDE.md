# Panduan Deployment EPI-OSS ke Shared Hosting (cPanel)

Panduan ini menjelaskan cara memindahkan aplikasi dari Localhost ke Server Shared Hosting (cPanel) menggunakan alur kerja GitHub (Dev & Production).

## Prasyarat
1.  **Akun GitHub**: Repositori proyek sudah di-push ke GitHub.
2.  **Akses cPanel**: Username dan Password cPanel.
3.  **Akses SSH**: Pastikan paket hosting Anda mendukung akses SSH (Terminal).
4.  **Domain/Subdomain**: Sudah diarahkan ke hosting.

---

## 1. Persiapan di Localhost

### A. Penanganan Aset Frontend (Vite/Tailwind)
Secara default, Laravel mengabaikan folder hasil build (`public/build`) di `.gitignore`.

Anda memiliki dua pilihan strategi:

#### Opsi A: Build di Local (Tanpa Node.js di Server) - *Direkomendasikan untuk Shared Hosting Biasa*
Pilih ini jika server Anda tidak mendukung Node.js atau Anda ingin cara termudah.
1. Buka file `.gitignore`.
2. Hapus atau beri komentar pada baris `/public/build`.
   ```gitignore
   # /public/build  <-- Beri komentar seperti ini
   ```
3. Jalankan build di local sebelum push:
   ```bash
   npm run build
   ```
4. Commit dan Push perubahan:
   ```bash
   git add .
   git commit -m "Update build assets for production"
   git push origin main
   ```

#### Opsi B: Build di Server (Membutuhkan Node.js di Server)
Pilih ini jika hosting Anda mendukung Node.js (via CloudLinux / Setup Node.js App) dan Anda ingin alur CI/CD yang lebih bersih.
1. **Jangan ubah .gitignore**. Biarkan `/public/build` diabaikan.
2. Pastikan Node.js terinstall di server (lihat panduan provider hosting Anda).
3. Anda akan menjalankan `npm install && npm run build` di server setiap kali deploy.

### B. Pastikan Konfigurasi Aman
Pastikan file `.env` tidak ter-upload ke GitHub (sudah default di Laravel).

---

## 2. Persiapan di cPanel

### A. Setup Database
1. Login ke **cPanel**.
2. Buka menu **MySQL® Database Wizard**.
3. Buat Database baru (misal: `u12345_epi_oss`).
4. Buat User Database baru (misal: `u12345_epi_user`) dan password yang kuat.
5. Berikan hak akses **ALL PRIVILEGES** user tersebut ke database yang dibuat.
6. Catat Nama Database, User, dan Password.

### B. Setup SSH Key (Agar bisa git pull tanpa password)
1. Di cPanel, cari menu **Terminal** (jika tidak ada, gunakan aplikasi SSH seperti Putty/Termius).
2. Generate SSH Key di server (jika belum ada):
   ```bash
   ssh-keygen -t ed25519 -C "server-cpanel"
   # Tekan Enter terus sampai selesai
   ```
3. Lihat public key:
   ```bash
   cat ~/.ssh/id_ed25519.pub
   ```
4. Copy output-nya.
5. Buka **GitHub Repo > Settings > Deploy Keys > Add deploy key**.
6. Paste key tersebut, beri judul "cPanel Server", dan centang **Allow write access** (opsional, tapi read-only cukup).

---

## 3. Proses Deployment (Pertama Kali)

Kita akan menaruh kode aplikasi di folder terpisah dari `public_html` agar aman, lalu menggunakan *symlink*.

**Struktur Folder yang akan dibuat:**
- `/home/user/apps/epi-oss` (Source code aplikasi)
- `/home/user/public_html` (Hanya link ke folder public aplikasi)

### Langkah-langkah di Terminal cPanel:

1. **Masuk ke folder home dan buat folder aplikasi:**
   ```bash
   cd ~
   mkdir -p apps
   cd apps
   ```

2. **Clone Repository:**
   ```bash
   git clone git@github.com:USERNAME/REPO-NAME.git epi-oss
   ```
   *(Ganti USERNAME dan REPO-NAME sesuai repo Anda)*

3. **Masuk ke folder proyek:**
   ```bash
   cd epi-oss
   ```

4. **Install PHP Dependencies (Composer):**
   Pastikan menggunakan PHP versi yang sesuai (8.2/8.3).
   ```bash
   # Contoh jika path php 8.3 spesifik (tanya provider hosting jika ragu)
   /usr/local/bin/php83 /usr/local/bin/composer install --optimize-autoloader --no-dev
   
   # Atau coba perintah standar jika default php sudah 8.3
   composer install --optimize-autoloader --no-dev
   ```

5. **Install Node.js Dependencies & Build (Hanya untuk Opsi B):**
   Jika Anda memilih Opsi B, jalankan perintah ini:
   ```bash
   npm install
   npm run build
   ```

6. **Setup File Environment (.env):**
   ```bash
   cp .env.example .env
   nano .env
   ```
   **Ubah konfigurasi berikut:**
   - `APP_NAME="EPI OSS"`
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://domain-anda.com`
   - `DB_DATABASE=u12345_epi_oss`
   - `DB_USERNAME=u12345_epi_user`
   - `DB_PASSWORD=password_db_anda`
   - `QUEUE_CONNECTION=database` (atau `redis` jika tersedia)
   
   Simpan dengan `Ctrl+O`, `Enter`, lalu `Ctrl+X`.

7. **Generate App Key:**
   ```bash
   php artisan key:generate
   ```

8. **Jalankan Migrasi Database:**
   ```bash
   php artisan migrate --seed --force
   ```

9. **Setup Storage Link:**
   ```bash
   php artisan storage:link
   ```

### Menghubungkan ke Public HTML (Symlink)
Agar website bisa diakses, kita harus menghubungkan folder `public` aplikasi ke `public_html`.

1. **Backup folder public_html lama (jika ada):**
   ```bash
   cd ~
   mv public_html public_html_backup
   ```
   *(Catatan: Jika ini subdomain, sesuaikan path ke folder subdomain)*

2. **Buat Symlink:**
   ```bash
   ln -s /home/USERNAME/apps/epi-oss/public /home/USERNAME/public_html
   ```
   *(Ganti USERNAME dengan username cPanel Anda)*

---

## 4. Alur Update (Dev ke Production)

Setiap kali Anda selesai mengembangkan fitur di Localhost:

1. **Local:** Commit dan Push ke GitHub.
   ```bash
   npm run build
   git add .
   git commit -m "Fitur baru selesai"
   git push origin main
   ```

2. **Server (cPanel):** Login via SSH/Terminal.
   ```bash
   cd ~/apps/epi-oss
   
   # 1. Ambil kode terbaru
   git pull origin main
   
   # 2. Update dependency (jika ada perubahan di composer.json)
   composer install --optimize-autoloader --no-dev
   
   # 3. Build Assets (Hanya jika Opsi B / Node.js tersedia)
   npm install
   npm run build

   # 4. Update database (jika ada migration baru)
   php artisan migrate --force
   
   # 5. Bersihkan cache
   php artisan optimize
   php artisan view:clear
   php artisan config:clear
   ```

---

## 5. Troubleshooting Umum

**A. Error 500 / Blank Page**
- Cek log error di `storage/logs/laravel.log`.
- Pastikan permission folder `storage` dan `bootstrap/cache` bisa ditulis (775 atau 755).
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```

**B. Gambar tidak muncul**
- Pastikan symlink storage sudah dibuat (`php artisan storage:link`).
- Jika symlink error di shared hosting, coba hapus folder `public/storage` lalu jalankan command lagi.

**C. Versi PHP Tidak Sesuai**
- Cek versi php di terminal: `php -v`.
- Jika default php lama, gunakan full path (misal `/usr/local/bin/php83`) saat menjalankan artisan atau composer.

**D. Vite Manifest not found**
- Ini berarti Anda lupa menjalankan `npm run build` di local atau lupa menghapus `/public/build` dari `.gitignore` sebelum push.
