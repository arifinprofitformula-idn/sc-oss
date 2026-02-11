# Dokumentasi Validasi Import Silverchannel

Dokumen ini menjelaskan alur validasi dan eksekusi pada proses import data Silverchannel, khususnya terkait kewajiban menyertakan Referrer ID (Upline).

## Prinsip Utama
1.  **Mandatory Referrer**: Setiap data Silverchannel baru WAJIB memiliki Referrer ID yang valid (sudah terdaftar di sistem).
2.  **All-or-Nothing**: Proses import bersifat atomik pada level file. Jika terdapat **satu saja** baris yang gagal validasi (misal Referrer tidak ditemukan), maka **SELURUH** proses import dibatalkan. Tidak ada data parsial yang masuk.
3.  **Fail-Fast Feedback**: Sistem akan mengumpulkan semua error validasi dari semua baris terlebih dahulu sebelum memutuskan untuk membatalkan proses, memberikan laporan error yang komprehensif kepada pengguna.

## Alur Proses (Flow)

### 1. Pre-Processing Phase
*   Sistem membaca file CSV.
*   Sistem mengumpulkan seluruh `id_silverchannel` dan `referrer_id` dari file.
*   Sistem melakukan *bulk lookup* ke database untuk mengambil data user yang sudah ada dan calon referrer. Ini dilakukan untuk menghindari query berulang (N+1 problem) dan meningkatkan performa.

### 2. Validation Phase (Global)
Sistem melakukan iterasi pada setiap baris data di memori:
*   **Format Check**: Memastikan kolom wajib terisi (ID, Nama, Email, Tanggal Bergabung, Referrer ID).
*   **Referrer Existence Check**: Memastikan `referrer_id` yang diinputkan benar-benar ada di database (bisa berupa `silver_channel_id` atau `referral_code`).
*   **Error Collection**: Jika ditemukan kesalahan, error dicatat dengan detail nomor baris dan pesan kesalahan.

**Keputusan:**
*   Jika `jumlah_error > 0`: Proses **BERHENTI**. Sistem mengembalikan daftar error ke pengguna. Database tidak disentuh (tidak ada insert/update).
*   Jika `jumlah_error == 0`: Lanjut ke fase eksekusi.

### 3. Execution Phase (Atomic Transaction)
Sistem membuka Database Transaction (`DB::beginTransaction()`).
*   Melakukan iterasi ulang pada data yang sudah tervalidasi.
*   Melakukan `INSERT` (untuk data baru) atau `UPDATE` (untuk data lama).
*   Mencatat Audit Log.
*   Jika terjadi *System Error* (misal koneksi putus) di tengah jalan, sistem melakukan `DB::rollBack()`.
*   Jika semua sukses, sistem melakukan `DB::commit()`.

## Kriteria Keberhasilan & Gegagalan

### Kriteria Gagal (Import Dibatalkan)
Proses import akan gagal total jika:
1.  Kolom `referrer_id` kosong pada salah satu baris.
2.  Nilai `referrer_id` tidak ditemukan di database (Referrer belum terdaftar).
3.  Format data salah (misal email tidak valid, tanggal salah format).
4.  Terjadi duplikasi data yang melanggar constraint unik (misal NIK atau Email sudah dipakai user lain di luar file import ini).

### Kriteria Berhasil
Proses import dinyatakan sukses jika:
1.  Semua baris memiliki format data yang valid.
2.  Semua baris memiliki Referrer ID yang valid dan terdaftar.
3.  Transaksi database berhasil di-commit sepenuhnya.

## Pesan Error
User akan menerima pesan:
> "Import dibatalkan karena terdapat error validasi (termasuk validasi referrer ID)."

Detail error per baris akan ditampilkan di tabel hasil import, contoh:
*   *Row 5: Referrer ID 'UNKNOWN01' tidak ditemukan di sistem. Pastikan referrer sudah terdaftar.*
*   *Row 10: The referrer id field is required.*
