# Email delivery — status and actions

## Update (2026-03-18)

**Outbound delivery (Gmail, etc.)** was fixed by enabling **DKIM signing in Postfix** (milter to OpenDKIM). See **`EMAIL_DELIVERY_FIX_2026-03-18.md`** for details.

Previously, mail left the server without a valid DKIM signature even though DNS had a DKIM record — Gmail reported SPF/DKIM authentication failures.

---

## Self-managed VPS: PTR usually not available

1-GRID (and similar) often **cannot or will not set custom PTR** on **self-managed** VPS. You cannot set PTR yourself.

**You do not need PTR** for most delivery if **DKIM + SPF** are correct (see fix above). If some recipients still reject for **reverse DNS**, use a **transactional SMTP relay** — see **`SELF_MANAGED_VPS_NO_PTR.md`**.

Check current PTR (informational only):

```bash
dig -x 41.61.20.39 +short
```

---

## Health checks

```bash
systemctl is-active postfix opendkim dovecot
postqueue -p
tail -n 50 /var/log/mail.log | grep -i dkim
```

## Do not use GoDaddy `smtpout.secureserver.net` for this server

Unless you have a **GoDaddy-hosted** mailbox and password for that account, authenticated relay to `smtpout.secureserver.net` will fail. Local `@mygigguide.co.za` mailboxes are on this server (Dovecot), not on GoDaddy.

---

**Last updated:** 2026-03-18
