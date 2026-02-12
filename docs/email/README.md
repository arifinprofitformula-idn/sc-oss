# Modul Email Terpadu (Mailketing)

- Provider utama: Mailketing (fallback Brevo/SMTP)
- Komponen: EmailService, TemplateEngine, TrackingService, Jobs, Webhook
- Fitur: templating multi-language, A/B, analytics, preferences, tracking open/click, attachments

## Konfigurasi
- SystemSetting: mailketing_api_token, mailketing_sender_email, mailketing_sender_name, email_provider
- Queue: Redis direkomendasikan, gunakan queue `high` untuk email kritikal

## Rute Penting
- Webhook: POST /webhooks/mailketing
- Tracking: GET /email/track/open/{id}.png, GET /email/track/click/{id}?u=URL
- API User: /api/user/email-preferences, /api/user/email-history

## Penggunaan
- Gunakan EmailService::send(type, user, data, language, attachments)
- Type contoh: reset_password, registration_confirm, order_notification, order_status_paid, commission_payment

## Keamanan
- Rate limit 3/jam per user+type
- Sanitasi konten HTML, jangan masukkan script/iframe

