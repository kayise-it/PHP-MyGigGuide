# MyGigGuide Mail Operations Runbook

This runbook documents the production mail flow and repeatable checks for MyGigGuide.

## 1) Current Architecture

- Mailbox hosting/auth/storage: `Dovecot` + `Postfix virtual users` + `MariaDB (mailserver)`
- Webmail: `RainLoop` at `https://mail.mygigguide.co.za`
- Admin mailbox management: `/admin/mail-accounts` (Laravel)
- Outbound internet mail: currently direct from server IP (subject to recipient anti-spam policy)

## 2) Self-managed VPS / PTR

**PTR (reverse DNS)** for the VPS IP is often **not offered** on self-managed products; the provider may not change it.

- **First line:** **DKIM + SPF** (Postfix → OpenDKIM on this server). That fixes most Gmail-style authentication failures.
- **If deferrals cite reverse DNS (e.g. IB108):** route outbound through an **authenticated SMTP relay** (Brevo, SendGrid, SES, etc.) and update SPF/DKIM per provider. See **`SELF_MANAGED_VPS_NO_PTR.md`**.

## 3) Standard Health Check

Run:

```bash
systemctl is-active nginx php8.2-fpm mariadb dovecot postfix
postqueue -p
tail -n 120 /var/log/mail.log
```

Expected:

- All services `active`
- Queue small/empty during normal operation
- External deliveries show `status=sent`

## 4) Mailbox Provisioning Validation

After creating/updating mailbox in admin:

1. Login at `https://mail.mygigguide.co.za`
2. Send to self (`user@mygigguide.co.za`) and confirm received
3. Verify auth path:

```bash
doveadm auth test user@mygigguide.co.za '<password>'
```

Expected: `passdb: ... auth succeeded`

## 5) External Delivery Validation

Send test:

```bash
printf 'Subject: MyGigGuide delivery test\nFrom: dave@mygigguide.co.za\nTo: info@kayiseit.com\n\nExternal delivery test.\n' | sendmail -v -f dave@mygigguide.co.za info@kayiseit.com
```

Check outcome:

```bash
postqueue -p
tail -n 200 /var/log/mail.log | sed -n '/info@kayiseit.com/p'
```

Expected for success:

- `status=sent`
- queue entry removed

## 6) Relayhost Cutover (when credentials are available)

Required values:

- relay host
- port
- username
- password/API key
- TLS mode (`STARTTLS` or `SSL`)

Postfix settings to apply:

- `relayhost = [host]:port`
- `smtp_sasl_auth_enable = yes`
- `smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd`
- `smtp_sasl_security_options = noanonymous`
- `smtp_tls_security_level = encrypt` (recommended with STARTTLS)
- `smtp_tls_CAfile = /etc/ssl/certs/ca-certificates.crt`

Credential file:

- `/etc/postfix/sasl_passwd` with strict permissions (`0600`)
- compile with `postmap /etc/postfix/sasl_passwd`

Reload and flush:

```bash
systemctl reload postfix
postqueue -f
```

## 7) Incident Triage Quick Map

- `Auth failed` in webmail:
  - verify mailbox password hash format and Dovecot auth
- `Can't send message` in webmail:
  - check `/var/log/mail.log` and queue first
- Message in Sent but not delivered externally:
  - check remote reject/defer reason (most often PTR/rDNS or sender policy)

## 8) Backup / Rollback Reference

Latest backup snapshot location:

- `/var/backups/mygigguide-mail-20260221_112727`

Contains Postfix, Dovecot, RainLoop domain config, and app `.env` backup for rollback.
