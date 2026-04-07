# Self-managed VPS — no PTR from 1-GRID

On many **self-managed** VPS products the host **does not offer custom reverse DNS (PTR)** for your IP, or support will not change it. You **cannot** fix PTR yourself: only whoever owns the IP block can set it.

That is **not a blocker** for most mail if you do the rest correctly.

---

## What works without PTR

| Layer | Status / action |
|--------|------------------|
| **DKIM** | **Required.** Postfix must sign with OpenDKIM (already configured on this server). |
| **SPF** | Publish TXT for `mygigguide.co.za` including the IP that **actually sends** mail (`ip4:41.61.20.39` if sending direct from VPS). |
| **DMARC** | Optional tightening after SPF/DKIM are stable. |
| **PTR** | Nice-to-have. Some strict filters still mention rDNS; without PTR, use a **relay** (below) if you see deferrals. |

---

## If you still see “Reverse DNS failed” / IB108 / deferrals

Route **all outbound internet mail** through a **transactional SMTP provider** (they have clean IPs + PTR). Examples: **Brevo**, **SendGrid**, **Mailgun**, **Amazon SES**.

1. Sign up, verify domain `mygigguide.co.za`, create SMTP credentials.
2. Set Postfix (example pattern — use **your** provider’s host, port, user, password):

   ```conf
   relayhost = [smtp-relay.brevo.com]:587
   smtp_sasl_auth_enable = yes
   smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd
   smtp_sasl_security_options = noanonymous
   smtp_tls_security_level = encrypt
   smtp_tls_CAfile = /etc/ssl/certs/ca-certificates.crt
   ```

3. `/etc/postfix/sasl_passwd` (one line, then `sudo postmap /etc/postfix/sasl_passwd`):

   ```text
   [smtp-relay.brevo.com]:587    YOUR_SMTP_LOGIN:YOUR_SMTP_KEY
   ```

4. **Update SPF** at your DNS registrar: add the provider’s `include:` (e.g. Brevo/SendGrid docs give the exact SPF snippet). Remove or adjust `ip4:41.61.20.39` if you no longer send from the VPS IP for that stream.
5. **DKIM**: use the provider’s domain signing **or** keep local OpenDKIM only if the relay does not break alignment (follow provider docs).

6. `sudo postfix check && sudo systemctl reload postfix && postqueue -f`

**Inbound mail** (others sending to `@mygigguide.co.za`) is unchanged: still MX → your VPS.

---

## Summary

- **1-GRID not helping with PTR** on self-managed VPS is common.
- **Primary fix** for Gmail-style rejection: **DKIM + SPF** (DKIM milter is already on this server).
- **If PTR-sensitive recipients still defer:** use an **authenticated SMTP relay** and align **SPF/DKIM** with that path.
