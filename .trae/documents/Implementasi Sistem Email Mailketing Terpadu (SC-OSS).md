## Gambaran Saat Ini
- Sudah ada provider Mailketing & Brevo, transport custom, dan routing mailer per skenario: auth/order/marketing.
- Editor template dan riwayat versi tersedia; sebagian notifikasi sudah queued, sebagian mailables masih sinkron.
- Belum ada webhook inbound (delivery/open/click/bounce) dan analytics; attachments Mailketing belum lengkap.

## Tujuan Arsitektur
- Menjadikan Mailketing sebagai platform utama, dengan fallback Brevo/SMTP bila diperlukan.
- Modular: EmailService mengorkestrasi trigger, templating, routing, queue, logging, dan analytics.
- Dapat dikonfigurasi via dashboard admin (provider default, routing per tipe, template, A/B testing, segmentasi/bulk).

## Komponen & File (Baru/Diubah)
- Service layer:
  - app/Services/Email/EmailService.php (orchestrator)
  - app/Services/Email/TemplateEngine.php (merge tags, multi-language, sanitization, preview)
  - app/Services/Email/TrackingService.php (open/click pixel & link rewriting)
  - app/Services/Email/RateLimiterService.php (rate limit per user+email_type)
- Provider enhancement:
  - app/Services/Email/MailketingProvider.php (attachments, metadata, error mapping, retries)
  - app/Mail/Transport/MailketingTransport.php (base64 attachments & inline image jika API mendukung)
- Jobs & Queue:
  - app/Jobs/SendEmailJob.php (maxAttempts=3, backoff, high-priority untuk critical)
  - app/Jobs/ProcessWebhookEventJob.php
  - app/Jobs/RunEmailCampaignJob.php
- Webhook & Tracking:
  - app/Http/Controllers/Webhooks/MailketingWebhookController.php
  - app/Http/Controllers/EmailTrackingController.php (open png & click redirect)
- Admin UI:
  - resources/views/admin/integrations/email/*.blade.php (builder, analytics, routing, A/B)
  - app/Http/Controllers/Admin/EmailTemplateController.php (versi & A/B) [perluasan]
  - app/Http/Controllers/Admin/IntegrationController.php (konfigurasi provider & routing) [perluasan]
- User Preference & History:
  - app/Http/Controllers/API/UserEmailPreferenceController.php
  - app/Http/Controllers/API/UserEmailHistoryController.php
- Events & Listeners:
  - app/Listeners/EmailStatusUpdateListener.php (sinkronkan status dari webhook ke log)
  - Hook pada event OrderPaid/OrderStatusChanged/CommissionPaid

## Database & Indexing
- Migrations:
  - email_logs: user_id, distributor_id, type, template_id, subject, to, status (QUEUED/SENT/DELIVERED/OPENED/CLICKED/BOUNCED/FAILED), provider_message_id, queued_at, sent_at, delivered_at, opens_count, clicks_count, bounced_at, error, retry_count, metadata(json), created_at. Index: (user_id,type,created_at), provider_message_id, status.
  - email_preferences: user_id, type, enabled, updated_at. Index: (user_id,type).
  - email_campaigns: name, type, segment(json), schedule_at, status, stats(json). Index: (type,schedule_at,status).
  - email_clicks & email_opens (optional detail): log_id, link_id/url, user_agent, ip (masked), occurred_at. Index: (log_id,occurred_at).
  - email_templates: tambah language (ID/EN), type, is_active.
  - email_template_versions: tambah fields: variant (A/B), split_ratio (0–100), notes.

## Template Engine & Builder
- TemplateEngine: merge tags (${user_name}, ${order_id}, dll), multi-language, sanitization (allowlist tags), auto-inject tracking pixel & rewrite links.
- Admin builder:
  - Drag-and-drop (integrasi GrapesJS) dengan live preview; fallback editor Tailwind+Alpine dengan rich text.
  - Versioning & rollback, A/B variant per template, preview mobile/desktop, export/import.

## Trigger Email (Detail Implementasi)
- Reset Password:
  - Gunakan broker token Laravel (expiry 60 menit); rate limit 3/jam per email; validasi user aktif.
  - Link reset menggunakan signed URL dan token terenkripsi; template berisi greeting, link, instruksi keamanan, kontak support.
- Pendaftaran SilverChannel:
  - Email konfirmasi + welcome & panduan awal, link aktivasi akun; lampiran PDF T&C & privacy (dibuat via generator PDF).
- Order Produk Notifications:
  - Detail order, QR code tracking (PNG), CTA menuju dashboard user; kirim ke customer & cc distributor bila relevan.
- Admin Status Updates:
  - PAID (invoice terlampir), PACKING (ETA), SHIPPED (tracking number & kurir), DELIVERED (CTA review), CANCELLED (alasan+refund), RETURNED (instruksi & timeline), REFUNDED (detail transaksi & timeline).
- Komisi Payment:
  - Detail periode, jumlah, metode, lampiran bukti transfer; kirim ke silverchannel terkait.

## Queue, Prioritas & Retry
- Semua pengiriman via SendEmailJob (ShouldQueue). Critical email (reset password, status PAID/SHIPPED) pada queue high.
- Retry max 3 dengan exponential backoff; log error di email_logs & IntegrationLog; dead-letter untuk kegagalan permanen.

## Webhook & Audit Trail
- Endpoint webhook Mailketing (signed secret) untuk delivery/open/click/bounce; normalisasi payload dan update email_logs.
- Tracking pixel (1x1 PNG) dan click redirect merekam opens/clicks.
- Audit lengkap: setiap percobaan kirim, respons provider, perubahan status via webhook.

## Routing & Konfigurasi Provider
- Mailketing sebagai default melalui SystemSetting; EmailRoutingService tetap mendukung routing granular per tipe (auth/order/marketing).
- Konfigurasi di dashboard: API key/token, sender, default & fallback, rate limit per tipe, mapping template-type ke provider.

## Admin Analytics & A/B Testing
- Dashboard: open rate, click rate, bounce rate per template/varian; grafik waktu; filter by segment, status, date range.
- A/B: definisikan variant & split ratio; pelaporan komparatif otomatis; rollback ke varian unggul.

## Bulk Email & Segmentasi
- Segment builder berbasis query (role, status order, date range, distributor); jadwal kampanye; run via RunEmailCampaignJob.
- Throttling & rate limit global; batasi per provider bila ada kuota.

## Frontend Integrasi
- User dashboard: indikator status email terbaru (sent/delivered/opened/clicked/bounced) via polling ringan atau broadcast.
- Preference center: toggle jenis notifikasi; API endpoints CRUD.
- Unsubscribe: one-click dengan signed token; updating email_preferences.
- Email history viewer: daftar email yang pernah dikirim dengan status & timestamp.

## Security & Performance
- Sanitization konten email (strip script/iframe berbahaya); validasi merge tags.
- Rate limiting per user+email_type (RateLimiterService) & per endpoint.
- DKIM/SPF/DMARC: dokumentasi setup DNS; validasi sender domain.
- Target <30 detik untuk critical: queue high, provider default aktif, koneksi Redis.
- Indexing untuk query cepat (email_logs & preferences).

## Testing & Quality
- Unit test: trigger logic, template engine, rate limiter, QR generation, preferences.
- Integration test: Mailketing sandbox, webhook processing, attachments.
- Load test: skenario 10.000 email/jam; verifikasi throughput & retry.
- Rendering test: snapshot HTML dan uji klien populer (Gmail/Outlook/Yahoo) via tool eksternal.

## Rute & RBAC
- Admin: /admin/integrations/email (manage provider, routing, templates, A/B, analytics, campaigns).
- Webhook: /webhooks/mailketing (POST, signed).
- Tracking: /email/track/open/{id}.png, /email/track/click/{id}/{link}.
- API User: /api/user/email-preferences, /api/user/email-history (Sanctum auth).
- RBAC: hanya SUPER_ADMIN dapat mengelola integrasi, SILVERCHANNEL untuk preferences & history.

## Dokumentasi & Deployment
- Modul email service lengkap (README & guide): konfigurasi environment, DNS (DKIM/SPF/DMARC), queue Redis, Horizon opsional.
- Monitoring & alert: threshold bounce/open rendah → notifikasi admin; integrasi log dengan halaman admin.
- Panduan migrasi dari Brevo ke Mailketing & fallback.

## Catatan Implementasi pada Kode Saat Ini
- Perluas MailketingProvider & MailketingTransport untuk attachments & metadata.
- Uniform-kan semua notifikasi & mailables agar queued via SendEmailJob.
- Tambahkan webhook & tracking; perlu rute baru dan controller.
- Gunakan EmailRoutingService yang ada sebagai basis routing.

Konfirmasi rencana ini agar saya lanjutkan ke tahap implementasi sesuai spesifikasi di atas (tanpa mengubah data sensitif), termasuk pembuatan migrations, services, controllers, UI admin, API, dan pengujian.