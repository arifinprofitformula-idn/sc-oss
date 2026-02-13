<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pesanan {{ $order->order_number }} Sedang Dikirim</title>
</head>
<body>
    <p>Halo {{ $order->user->name }},</p>
    <p>Pesanan <strong>#{{ $order->order_number }}</strong> telah dikirim.</p>
    @if(!empty($order->shipping_tracking_number))
    <p>Resi: <strong>{{ $order->shipping_tracking_number }}</strong></p>
    @endif
    @if(!empty($order->shipping_courier))
    <p>Kurir: <strong>{{ $order->shipping_courier }}</strong></p>
    @endif
    <p>
        <a href="{{ url('/silverchannel/orders/' . $order->id) }}" style="display:inline-block;padding:10px 16px;background:#0ea5e9;color:#fff;text-decoration:none;border-radius:4px;">Cek Status Pesanan</a>
    </p>
    <p>Terima kasih.</p>
</body>
</html>
