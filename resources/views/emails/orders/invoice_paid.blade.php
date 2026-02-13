<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pembayaran Diterima — Invoice {{ $order->order_number }} Lunas</title>
</head>
<body>
    <p>Halo {{ $order->user->name }},</p>
    <p>Pembayaran untuk pesanan <strong>#{{ $order->order_number }}</strong> telah diterima pada {{ optional($order->paid_at)->format('d M Y H:i') }}.</p>
    <p>Ringkasan pesanan:</p>
    <ul>
        <li>Total: Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</li>
        <li>Kurir: {{ $order->shipping_courier ?? '-' }} ({{ $order->shipping_service ?? '' }})</li>
    </ul>
    <p>
        <a href="{{ url('/silverchannel/orders/' . $order->id) }}" style="display:inline-block;padding:10px 16px;background:#0ea5e9;color:#fff;text-decoration:none;border-radius:4px;">Lihat Detail Pesanan</a>
    </p>
    <p>Invoice PDF terlampir.</p>
    <p>Terima kasih.</p>
</body>
</html>
