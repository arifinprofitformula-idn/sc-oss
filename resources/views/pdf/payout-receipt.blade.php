<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payout {{ $payout->payout_number }}</title>
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
        <h2>Kwitansi Pencairan</h2>
        <p>No: {{ $payout->payout_number }} | Tanggal: {{ optional($payout->processed_at)->format('d M Y') }}</p>
    </div>
    <p><strong>Nama:</strong> {{ $payout->user->name }}<br>
    <strong>Email:</strong> {{ $payout->user->email }}</p>

    <table class="table">
        <tbody>
            <tr>
                <td>Jumlah</td>
                <td class="right">Rp {{ number_format((float) $payout->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td class="right">Diproses</td>
            </tr>
        </tbody>
    </table>
    @if($payout->proof_file)
        <p class="text-sm">Bukti transfer: {{ $payout->proof_file }}</p>
    @endif
</body>
</html>
