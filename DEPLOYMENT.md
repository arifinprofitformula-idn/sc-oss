# SC OSS - Panduan Deployment cPanel (Secure Git)

Dokumen ini berisi panduan lengkap langkah-demi-langkah untuk melakukan deployment aplikasi **SC OSS** ke hosting berbasis cPanel menggunakan GitHub Repository secara aman.

---

## Prasyarat
1. **Akun GitHub**: Memiliki akses ke repository project (disarankan Private Repository).
2. **Akun cPanel**: Memiliki akses login ke cPanel dan fitur **Git Version Control** serta **Terminal/SSH**.
3. **SSH Access**: Disarankan untuk menjalankan perintah Composer dan Artisan.

---

## Tahap 1: Setup GitHub Repository (Aman)

Agar cPanel bisa mengambil kode dari Private Repository, kita perlu menyiapkan **Deploy Key**.

1. **Login ke cPanel** -> Buka menu **Terminal**.
2. Generate SSH Key khusus untuk deployment:
   ```bash
   ssh-keygen -t ed25519 -C "cpanel-deploy-key"
   # Tekan Enter untuk file location (default)
   # Biarkan passphrase kosong (tekan Enter 2x) agar auto-deploy berjalan lancar
   ```
3. Lihat dan copy Public Key:
   ```bash
   cat ~/.ssh/id_ed25519.pub
   ```
   *(Copy output yang muncul, dimulai dengan `ssh-ed25519 ...`)*

4. **Buka GitHub Repository** Anda:
   - Masuk ke **Settings** -> **Deploy keys**.
   - Klik **Add deploy key**.
   - **Title**: `cPanel Hosting`
   - **Key**: Paste public key yang tadi dicopy.
   - **Allow write access**: JANGAN dicentang (biarkan Read-only untuk keamanan).
   - Klik **Add key**.

---

## Tahap 2: Struktur Branching

Gunakan struktur branch berikut untuk manajemen deployment yang rapi:
- **`main` / `develop`**: Branch untuk pengembangan aktif.
- **`production`**: Branch khusus untuk deployment ke cPanel. Kode di sini harus stabil.

**Workflow Deployment:**
1. Developer coding di `develop` / `feature-branch`.
2. Merge ke `main` setelah testing.
3. Saat siap rilis, merge `main` ke `production`.
4. Build assets (CSS/JS) di lokal, lalu commit hasilnya ke `production` (karena shared hosting biasanya tidak punya Node.js/NPM yang memadai).

**Cara Build Assets Lokal & Push ke Production:**
```bash
# Di komputer lokal developer
git checkout production
git merge main
npm install
npm run build
# Hapus /public/build dari .gitignore jika ada, atau gunakan perintah git add -f
git add public/build -f
git commit -m "Build assets for release v1.0"
git push origin production
```

---

## Tahap 3: Setup cPanel Git Version Control

Kami merekomendasikan struktur folder yang **AMAN**: Kode aplikasi ditaruh DI LUAR `public_html`, hanya folder `public` yang diekspos.

1. **Login cPanel** -> Buka **Git Version Control**.
2. Klik **Create**.
3. **Clone URL**: Masukkan SSH URL repository GitHub (misal: `git@github.com:username/sc-oss.git`).
   - *Catatan: Jika cPanel gagal clone via SSH, pastikan key SSH sudah benar terdaftar di cPanel SSH Access.*
4. **Repository Path**: Masukkan path di luar public_html, misal: `repositories/sc-oss`.
   - *JANGAN gunakan `public_html` langsung.*
5. **Branch Name**: `production`.
6. Klik **Create**.

---

## Tahap 4: Konfigurasi Environment & Database

1. **Database**:
   - Buka **MySQL Database Wizard** di cPanel.
   - Buat Database baru (misal: `u12345_scoss`).
   - Buat User baru (misal: `u12345_scoss_user`) dan password yang KUAT.
   - Beri akses **All Privileges** user ke database tersebut.

2. **Environment File (.env)**:
   - Buka **File Manager** cPanel.
   - Masuk ke folder repository: `repositories/sc-oss`.
   - Copy file `.env.example` menjadi `.env`.
   - Edit `.env` dan sesuaikan:
     ```env
     APP_NAME="SC OSS"
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://domain-anda.com

     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=u12345_scoss
     DB_USERNAME=u12345_scoss_user
     DB_PASSWORD=password_database_anda

     QUEUE_CONNECTION=database
     SESSION_DRIVER=file
     ```
   - **PENTING**: Pastikan `APP_DEBUG=false` untuk keamanan production!

---

## Tahap 5: Instalasi & Symlink (Via Terminal cPanel)

Buka **Terminal** di cPanel dan jalankan perintah berikut satu per satu:

1. **Masuk ke folder project**:
   ```bash
   cd repositories/sc-oss
   ```

2. **Install Dependency PHP**:
   ```bash
   # Gunakan path php yang sesuai jika perlu, atau cukup 'composer'
   /usr/local/bin/composer install --optimize-autoloader --no-dev
   ```

3. **Generate Key Aplikasi** (hanya pertama kali):
   ```bash
   php artisan key:generate
   ```

4. **Migrasi Database**:
   ```bash
   php artisan migrate --force
   ```

5. **Storage Link**:
   ```bash
   php artisan storage:link
   ```

6. **Menghubungkan ke Public HTML (Symlink)**:
   Ini langkah krusial agar website bisa diakses. Kita akan me-link folder `public` di repo ke `public_html`.
   
   **Opsi A: Domain Utama (public_html)**
   ```bash
   # Backup public_html lama jika ada
   mv ~/public_html ~/public_html_backup
   
   # Buat symlink
   ln -s ~/repositories/sc-oss/public ~/public_html
   ```

   **Opsi B: Subdomain (misal: app.domain.com folder rootnya app)**
   ```bash
   # Hapus folder subdomain kosong yang dibuat cPanel
   rm -rf ~/app
   
   # Buat symlink
   ln -s ~/repositories/sc-oss/public ~/app
   ```

---

## Tahap 6: Setup Queue & Scheduler (Cron Jobs)

Karena shared hosting jarang memiliki Supervisor, kita gunakan Cron Job.

1. **Buka menu Cron Jobs** di cPanel.
2. **Scheduler (Setiap Menit)**:
   - Common Settings: `Once Per Minute (* * * * *)`
   - Command:
     ```bash
     /usr/local/bin/php /home/username/repositories/sc-oss/artisan schedule:run >> /dev/null 2>&1
     ```
     *(Ganti `/usr/local/bin/php` dengan path PHP 8.3 anda, dan `/home/username` dengan path user cPanel anda)*.

3. **Queue Worker (Setiap Menit)**:
   - Common Settings: `Once Per Minute (* * * * *)`
   - Command:
     ```bash
     /usr/local/bin/php /home/username/repositories/sc-oss/artisan queue:work --stop-when-empty --tries=3 --timeout=90 >> /dev/null 2>&1
     ```
   - *Penjelasan*: `--stop-when-empty` memastikan proses berhenti jika tidak ada antrian, sehingga tidak membebani RAM server terus menerus (friendly untuk shared hosting). Cron akan menjalankannya lagi menit berikutnya.

---

## Tahap 7: Update Aplikasi (Redeploy)

Setiap kali ada update di GitHub branch `production`:

1. **Via cPanel Git Version Control**:
   - Buka menu Git Version Control.
   - Klik **Manage** pada repository.
   - Tab **Pull or Deploy** -> Klik **Update from Remote**.

2. **Jalankan Perintah Post-Deploy (Via Terminal)**:
   Anda bisa membuat file `deploy.sh` (lihat file terlampir) untuk mempermudah.
   ```bash
   cd repositories/sc-oss
   sh deploy.sh
   ```

---

## Checklist Keamanan Production

- [ ] **APP_DEBUG=false**: Wajib di file `.env`.
- [ ] **Folder Permission**:
  - `storage` dan `bootstrap/cache` harus writable (775 atau 755).
  - File lain sebaiknya 644, Folder 755.
- [ ] **Backup**: Aktifkan fitur backup otomatis cPanel atau gunakan plugin backup Laravel (Spatie Backup).
- [ ] **API Keys**: Pastikan key payment gateway (Midtrans/Xendit) menggunakan mode Production di `.env`.

---

## Troubleshooting

**Q: Error 500 saat akses web?**
A: Cek log error di `repositories/sc-oss/storage/logs/laravel.log`. Kemungkinan permission folder `storage` belum writable atau `.env` salah konfigurasi.

**Q: `git pull` gagal di cPanel?**
A: Pastikan tidak ada perubahan file lokal di cPanel. Jalankan `git reset --hard` di terminal cPanel jika Anda yakin ingin menimpa perubahan lokal dengan versi GitHub.

**Q: Gambar produk tidak muncul?**
A: Pastikan symlink storage sudah dibuat (`php artisan storage:link`) dan folder `public/storage` ada.
