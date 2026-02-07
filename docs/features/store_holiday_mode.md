# Fitur Keterangan Penutupan Toko (Holiday Mode Note)

## Deskripsi
Fitur ini memungkinkan administrator untuk memberikan keterangan spesifik ketika toko sedang ditutup menggunakan "Mode Libur". Keterangan ini akan ditampilkan kepada pengguna (Silverchannel) di halaman katalog produk, memberikan informasi yang lebih jelas mengenai alasan penutupan toko (misalnya: Hari Raya, Stock Opname, Libur Bersama).

## Cara Penggunaan (Admin)

1.  **Akses Menu Pengaturan**:
    Masuk ke Panel Admin, lalu navigasi ke menu **Settings** -> **Global Store Settings**.

2.  **Pilih Tab Jam Operasional**:
    Klik pada tab **Jam Operasional**.

3.  **Aktifkan Mode Libur**:
    Temukan toggle **Mode Libur (Holiday)** dan aktifkan (switch ke posisi ON).

4.  **Isi Keterangan**:
    Setelah Mode Libur aktif, akan muncul field input baru bertuliskan **Keterangan Penutupan (Optional)**.
    -   Isi dengan alasan penutupan, contoh: `HARI RAYA IDUL FITRI 1447 H` atau `LIBUR TAHUN BARU`.
    -   Maksimal karakter yang diperbolehkan adalah **100 karakter**.
    -   Jika dikosongkan, sistem akan menampilkan pesan default.

5.  **Simpan Pengaturan**:
    Klik tombol **UPDATE SETTINGS** atau **Simpan Jam Operasional** di bagian bawah halaman.

## Tampilan di Frontend (Silverchannel)

Di halaman **Product Catalog**, status toko akan ditampilkan di bagian atas daftar produk:

*   **Jika Keterangan Diisi**:
    Akan muncul banner merah dengan teks:
    > **TOKO TUTUP: [KETERANGAN YANG DIINPUT]**
    > *Contoh: TOKO TUTUP: HARI RAYA IDUL FITRI 1447 H*

*   **Jika Keterangan Kosong**:
    Akan muncul banner merah dengan teks default:
    > **TOKO TUTUP HARI MINGGU / LIBUR NASIONAL**

## Catatan Teknis

*   **Database**: Data disimpan di kolom `holiday_note` (VARCHAR 100, nullable) pada tabel `stores`.
*   **Keamanan**: Input divalidasi untuk mencegah karakter berbahaya (XSS prevention) dan dibatasi maksimal 100 karakter.
*   **Cache**: Status toko (termasuk keterangan libur) di-cache selama 1 jam. Perubahan di admin panel akan otomatis memperbarui cache tersebut.
