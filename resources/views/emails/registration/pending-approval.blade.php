<x-mail::message>
# Halo, {{ $user->name }}!

Terima kasih telah mendaftar sebagai Silverchannel di **Emas Perak Indonesia**.

Pendaftaran Anda telah kami terima bersama dengan bukti pembayaran.
Saat ini tim Admin kami sedang melakukan verifikasi data dan pembayaran Anda.

**Detail Pendaftaran:**
- **Nama:** {{ $user->name }}
- **ID Sementara:** {{ $user->silver_channel_id }}
- **No. Order:** {{ $order->order_number }}
- **Status:** Menunggu Verifikasi

**Rincian Biaya:**
- **Paket & Produk:** Rp {{ number_format($order->subtotal, 0, ',', '.') }}
- **Ongkos Kirim:** Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
@if($order->insurance_amount > 0)
- **Asuransi Pengiriman (LM):** Rp {{ number_format($order->insurance_amount, 0, ',', '.') }}
@endif
- **Total:** Rp {{ number_format($order->total_amount, 0, ',', '.') }}

Kami akan segera memberitahu Anda melalui email atau WhatsApp setelah akun Anda aktif.
Proses verifikasi biasanya memakan waktu 1x24 jam kerja.

Terima Kasih,<br>
{{ config('app.name') }}
</x-mail::message>
