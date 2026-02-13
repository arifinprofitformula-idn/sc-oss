<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }} — Menunggu Pembayaran</title>
</head>
<body>
    <p>Halo {{ $order->user->name }},</p>
    <p>Berikut ringkasan invoice untuk pesanan Anda:</p>
    <p>
        Nomor Order: <strong>#{{ $order->order_number }}</strong><br>
        Tanggal: {{ optional($order->created_at)->format('d M Y H:i') }}<br>
        Total: Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
    </p>
    <p>Silakan lanjutkan pembayaran melalui tombol berikut:</p>
    <p>
        <a href="{{ route('silverchannel.checkout.order-received', ['order' => $order->id, 'key' => $order->order_key]) }}" style="display:inline-block;padding:10px 16px;background:#0ea5e9;color:#fff;text-decoration:none;border-radius:4px;">Lanjutkan Pembayaran</a>
    </p>
    <p>Instruksi pembayaran: transfer sesuai metode yang dipilih. Jika butuh bantuan, hubungi support.</p>
    <p>Terima kasih.</p>
</body>
</html>
