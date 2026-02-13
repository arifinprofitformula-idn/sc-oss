<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Selamat Datang di Silverchannel — Akun Anda Aktif</title>
</head>
<body>
    <p>Halo {{ $user->name }},</p>
    <p>Selamat datang di Silverchannel. Anda kini dapat mengakses katalog dan dashboard.</p>
    <p>
        <a href="{{ url('/silverchannel') }}" style="display:inline-block;padding:10px 16px;background:#0ea5e9;color:#fff;text-decoration:none;border-radius:4px;">Masuk Dashboard</a>
    </p>
    <p>Jika butuh bantuan, hubungi tim support.</p>
</body>
</html>
