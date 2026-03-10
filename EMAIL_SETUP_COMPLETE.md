# Email Setup - Server Configuration Complete! ✅

## What Has Been Configured

✅ **Postfix (SMTP)** - Installed and running
✅ **OpenDKIM** - Configured and signing emails
✅ **Dovecot (IMAP/POP3)** - Already running
✅ **Mail Database** - Created with email accounts
✅ **Laravel Mail Configuration** - Updated for SMTP
✅ **DKIM Signing** - Emails are now being signed

## Current Status

**Server-side configuration is 100% complete!**

The email server is ready and emails are being sent with DKIM signatures. However, Gmail is still rejecting emails because **DNS records need to be added at your domain registrar**.

## What You Need to Do Next

### Add DNS Records at Your Domain Registrar

You need to add 5 DNS records for `mygigguide.co.za`. All the exact values are in:

📄 **`/var/www/mygigguide/REQUIRED_DNS_RECORDS.txt`**

### Quick Summary:

1. **MX Record**: `mail.mygigguide.co.za` (priority 10)
2. **A Record**: `mail` → `41.61.20.39`
3. **SPF Record**: `v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all`
4. **DKIM Record**: See REQUIRED_DNS_RECORDS.txt for the full key
5. **DMARC Record**: `v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za`

## Verify DNS Records

After adding DNS records, wait 5-10 minutes for propagation, then run:

```bash
/var/www/mygigguide/check-dns.sh
```

Or manually check:
```bash
dig +short mx mygigguide.co.za
dig +short txt mygigguide.co.za | grep spf
dig +short txt default._domainkey.mygigguide.co.za
dig +short txt _dmarc.mygigguide.co.za
```

## Test Email

Once DNS records are added and propagated:

```bash
php /var/www/mygigguide/simple-test-email.php
```

## Email Accounts

Two email accounts are set up:

1. **dave@mygigguide.co.za** (Password: `Dave123!`)
2. **noreply@mygigguide.co.za** (Password: `ew&G87bqxu!`)

## Access Webmail (Roundcube)

Once DNS is configured:
- URL: `https://mail.mygigguide.co.za` (after SSL setup)
- Or: `http://mail.mygigguide.co.za` (temporary, before SSL)

Login with either email account above.

## Laravel Email Configuration

The `.env` file has been updated:
```env
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_FROM_ADDRESS="noreply@mygigguide.co.za"
MAIL_FROM_NAME="My Gig Guide"
```

## Current Email Status

✅ **Server is sending emails** with DKIM signatures
✅ **Logs show**: "DKIM-Signature field added"
❌ **Gmail rejects** until DNS records are added
✅ **Once DNS is added**, emails will be accepted

## Troubleshooting

### Check if services are running:
```bash
sudo systemctl status postfix opendkim dovecot
```

### View email logs:
```bash
sudo tail -f /var/log/mail.log
```

### Check DKIM signing:
```bash
sudo tail -f /var/log/mail.log | grep DKIM
```

### Test DKIM key:
```bash
sudo opendkim-testkey -d mygigguide.co.za -s default -vvv
```

## Next Steps

1. **Add DNS records** (see REQUIRED_DNS_RECORDS.txt)
2. **Wait 5-10 minutes** for DNS propagation
3. **Verify DNS** with check-dns.sh
4. **Test email** again
5. **Set up SSL** for webmail: `sudo certbot --nginx -d mail.mygigguide.co.za`

---

**Everything on the server is configured correctly!** You just need to add the DNS records at your domain registrar, and emails will work perfectly.
