<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>[SC-OSS] Order Baru #{{ $order->order_number }} — {{ $order->user->name }}</title>
</head>
<body>
    <p>Order baru diterima.</p>
    <p>
        Customer: {{ $order->user->name }} ({{ $order->user->email }})<br>
        Total: Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}<br>
        Jumlah item: {{ $order->items->count() }}
    </p>
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Qty</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p>
        <a href="{{ route('admin.orders.show', $order) }}" style="display:inline-block;padding:10px 16px;background:#0ea5e9;color:#fff;text-decoration:none;border-radius:4px;">Lihat Detail Order</a>
    </p>
    <p>Auto-notification by system.</p>
</body>
</html>
