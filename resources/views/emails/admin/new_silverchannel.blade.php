<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>[SC-OSS] Pendaftaran Silverchannel Baru — {{ $user->name }}</title>
</head>
<body>
    <p>Pendaftaran Silverchannel baru.</p>
    <p>
        Nama: {{ $user->name }}<br>
        Email: {{ $user->email }}<br>
        Waktu: {{ now()->format('d M Y H:i') }}
    </p>
    <p>
        <a href="{{ url('/admin/users/' . $user->id) }}" style="display:inline-block;padding:10px 16px;background:#0ea5e9;color:#fff;text-decoration:none;border-radius:4px;">Lihat Detail User</a>
    </p>
</body>
</html>
