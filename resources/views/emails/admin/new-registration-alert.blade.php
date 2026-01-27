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
- **Total:** Rp {{ number_format($order->grand_total, 0, ',', '.') }}
- **Metode:** Manual Transfer
- **Status:** {{ $order->payment_status }}

Silakan login ke dashboard admin untuk memverifikasi pembayaran dan mengaktifkan akun ini.

<x-mail::button :url="url('/admin/silverchannels')">
Verifikasi Pendaftaran
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
