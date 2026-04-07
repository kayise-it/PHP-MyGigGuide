# Email delivery fix (applied 2026-03-18)

## Root cause

Outbound mail was **not DKIM-signed**. Gmail and others reject or spam-folder mail that fails authentication (per `DNS_EMAIL_RECORDS.md`: SPF/DKIM required).

OpenDKIM was installed and DNS had `default._domainkey.mygigguide.co.za`, but **Postfix was not connected to OpenDKIM** (`smtpd_milters` / `non_smtpd_milters` were empty).

## Change made

In `/etc/postfix/main.cf`:

```conf
milter_default_action = accept
milter_protocol = 6
smtpd_milters = unix:opendkim/opendkim.sock
non_smtpd_milters = unix:opendkim/opendkim.sock
```

Then: `sudo postfix check && sudo systemctl reload postfix`

## Verified

- Log line: `opendkim[...]: DKIM-Signature field added (s=default, d=mygigguide.co.za)`
- External test to `check-auth@verifier.port25.com`: `status=sent (250 2.6.0 message received)`

## Also adjusted

- **OpenDKIM `InternalHosts`**: added `41.61.20.39` so clients connecting to Postfix via the public mail hostname still get signing.
- **Removed invalid GoDaddy relay**: `/etc/postfix/sasl_passwd` pointed at `smtpout.secureserver.net` with `noreply@mygigguide.co.za`. That mailbox is **local** (Dovecot), not a GoDaddy-hosted account, so relay auth always failed (`535 Authentication Failed`). Direct SMTP from the VPS is correct once DKIM works.

## Still recommended

1. Keep SPF aligned with how mail actually sends: `v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all` (adjust if you move to a **relay** — see `SELF_MANAGED_VPS_NO_PTR.md`).
2. DMARC: monitor `rua` reports.
3. **PTR:** On self-managed VPS, the host often **won’t set PTR**. If you still see rDNS-related deferrals, use a **transactional SMTP relay** instead of waiting on the provider.

## Rollback (if needed)

```bash
sudo sed -i '/^milter_/,/^non_smtpd_milters/d' /etc/postfix/main.cf   # manual edit safer: remove the 4 milter lines
sudo systemctl reload postfix
```

Backup before edit: `/etc/postfix/main.cf.before-dkim-milter` (if present).
