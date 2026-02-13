<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .right { text-align: right; }
    </style>
    </head>
<body>
    <div class="header">
        <h2>Invoice</h2>
        <p>Order: {{ $order->order_number }} | Tanggal: {{ optional($order->created_at)->format('d M Y') }}</p>
    </div>
    <p><strong>Penerima:</strong> {{ $order->user->name }}<br>
    <strong>Alamat:</strong> {{ $order->shipping_address }}</p>

    <table class="table">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="right">Qty</th>
                <th class="right">Harga</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p class="right">Subtotal: Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</p>
    <p class="right">Ongkir: Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</p>
    @if($order->insurance_amount)
    <p class="right">Asuransi: Rp {{ number_format((float) $order->insurance_amount, 0, ',', '.') }}</p>
    @endif
    <h3 class="right">Grand Total: Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</h3>
</body>
</html>
