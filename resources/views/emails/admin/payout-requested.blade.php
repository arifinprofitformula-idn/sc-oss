<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>[SC-OSS] Withdraw Requested — {{ $payout->payout_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827;">
    <h2 style="margin-bottom: 8px;">Permintaan Withdraw Komisi</h2>
    <p style="margin: 0 0 16px 0;">Seorang Silverchannel mengajukan pencairan komisi dan menunggu proses admin.</p>

    <table cellpadding="6" cellspacing="0" style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px;">
        <tr>
            <td><strong>No. Payout</strong></td>
            <td>{{ $payout->payout_number }}</td>
        </tr>
        <tr>
            <td><strong>Nama</strong></td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <td><strong>Jumlah</strong></td>
            <td>Rp {{ number_format((float) $payout->amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            <td>{{ $payout->status }}</td>
        </tr>
        <tr>
            <td><strong>Bank</strong></td>
            <td>
                {{ $user->bank_name ?? '-' }} —
                {{ $user->bank_account_no ?? '-' }} a.n {{ $user->bank_account_name ?? '-' }}
            </td>
        </tr>
        <tr>
            <td><strong>Diajukan</strong></td>
            <td>{{ $payout->created_at->format('d M Y H:i') }}</td>
        </tr>
    </table>

    <p style="margin-top: 20px;">
        <a href="{{ url('/admin/payouts') }}" style="display:inline-block;padding:10px 16px;background:#2563EB;color:#fff;text-decoration:none;border-radius:6px;">
            Buka Daftar Payout
        </a>
    </p>

    <p style="font-size: 12px; color:#6B7280; margin-top: 24px;">
        {{ config('app.name') }} &middot; Notifikasi Sistem
    </p>
</body>
</html>
