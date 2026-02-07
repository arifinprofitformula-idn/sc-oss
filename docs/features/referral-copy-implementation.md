# Dokumentasi Implementasi Fitur Copy Referral Link

## Overview
Fitur ini memungkinkan pengguna (Silverchannel) untuk menyalin link referral mereka ke clipboard dengan satu klik. Implementasi ini dirancang untuk keandalan tinggi, feedback visual instan, dan kompatibilitas lintas browser.

## Spesifikasi Teknis

### 1. Logic Copy (Alpine.js)
Logika diimplementasikan menggunakan Alpine.js `x-data` component langsung di dalam view Blade.

- **Primary Method**: `navigator.clipboard.writeText(text)`
  - Digunakan pada browser modern yang mendukung Clipboard API.
  - Asynchronous (Promise-based).

- **Fallback Method**: `document.execCommand('copy')`
  - Digunakan jika Clipboard API tidak tersedia atau gagal.
  - Membuat elemen `<textarea>` tersembunyi (`fixed`, `left: -9999px`), mengisi value, select, dan execute command copy.
  - Memastikan kompatibilitas dengan browser lama atau konteks non-secure (HTTP).

### 2. State Management
Komponen memiliki 4 state utama:
- `idle`: State awal, tombol siap diklik (Warna: Indigo).
- `loading`: Proses copy sedang berjalan (Warna: Gray, Cursor: Not Allowed).
- `success`: Copy berhasil (Warna: Green, Teks: "Tersalin!").
- `error`: Copy gagal (Warna: Red, Teks: "Gagal").

### 3. User Experience (UX)
- **Debouncing/Throttling**: Mencegah spam klik dengan mengecek state (`if state === 'loading' return`).
- **Loading Indicator**: Delay artifisial 300ms (jika proses terlalu cepat) agar user sempat melihat indikator loading.
- **Feedback**: Tooltip pesan sukses/error muncul selama 2-3 detik sebelum kembali ke state `idle`.
- **Visual**: Perubahan warna tombol dan icon sesuai state.

## Testing Coverage

Unit/Feature test terletak di `tests/Feature/DashboardAccessTest.php`.

### Skenario Test:
1. **Rendering Komponen**: Memastikan elemen `x-data` dan tombol copy dirender.
2. **Ketersediaan Script**: Memastikan script mencakup logika `navigator.clipboard` dan `document.execCommand`.
3. **Binding Data**: Memastikan link referral yang benar ter-bind ke variabel Alpine.js.

## Cara Penggunaan
Fitur ini otomatis aktif di halaman `/dashboard` untuk user dengan role `SILVERCHANNEL`.

```html
<!-- Struktur Komponen -->
<div x-data="{ ... }">
    <button @click="copy()">...</button>
</div>
```

## Troubleshooting
Jika user melaporkan gagal copy:
1. Pastikan browser mengizinkan akses clipboard.
2. Cek console browser untuk error JS.
3. Fallback method akan menangani sebagian besar kasus block permission pada API modern.
