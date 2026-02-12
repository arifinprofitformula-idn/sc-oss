# Deployment Guide (Email)

## Prasyarat
- Domain pengirim dengan DNS: SPF, DKIM, DMARC
- Kredensial Mailketing API Token
- Redis untuk queue

## Langkah
1. Set SystemSetting: `email_provider=mailketing`, `mailketing_api_token`, `mailketing_sender_*`
2. Pastikan queue berjalan dan gunakan prioritas `high` untuk email kritikal
3. Konfigurasi rute webhook di reverse proxy dengan secret/signature
4. Tambahkan record DNS:
   - SPF: include provider
   - DKIM: kunci publik dari provider
   - DMARC: policy p=quarantine/reject sesuai kebutuhan

## Monitoring
- Pantau email_logs untuk status sent/delivered/open/click/bounce
- Tambahkan alert jika bounce rate > ambang batas

