<x-mail::message>
# Pendaftaran Silverchannel Baru

Ada pendaftaran Silverchannel baru yang perlu diverifikasi.

**Data Pendaftar:**
- **Nama:** {{ $user->name }}
- **Email:** {{ $user->email }}
- **WhatsApp:** {{ $user->whatsapp }}
- **ID:** {{ $user->silver_channel_id }}

**Detail Pembayaran:**
- **No. Order:** {{ $order->order_number }}
- **Paket & Produk:** Rp {{ number_format($order->subtotal, 0, ',', '.') }}
- **Ongkos Kirim:** Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
@if($order->insurance_amount > 0)
- **Asuransi Pengiriman (LM):** Rp {{ number_format($order->insurance_amount, 0, ',', '.') }}
@endif
- **Total:** Rp {{ number_format($order->total_amount, 0, ',', '.') }}
- **Metode:** Manual Transfer
- **Status:** {{ $order->status }}

Silakan login ke dashboard admin untuk memverifikasi pembayaran dan mengaktifkan akun ini.

<x-mail::button :url="url('/admin/silverchannels')">
Verifikasi Pendaftaran
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
