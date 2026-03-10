# DNS Records Update Instructions

## Current Status

✅ **Email sent successfully** - Authentication working  
⚠️ **Delivery deferred** - Reverse DNS not configured  
📧 **Email queued** - Will retry automatically once DNS is fixed

---

## Required DNS Updates

### 1. Update SPF Record (Recommended for Better Security)

**Current Value:**
```
v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all
```

**New Value (Hard Fail):**
```
v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 -all
```

**What Changed:** `~all` → `-all` (soft fail to hard fail)

**How to Update:**
1. Log into your DNS provider's control panel (where you manage mygigguide.co.za)
2. Find the TXT record for `mygigguide.co.za`
3. Change `~all` to `-all`
4. Save the changes
5. Wait for DNS propagation (usually 5-60 minutes, can take up to 48 hours)

**Why:** Hard fail (`-all`) provides better security by rejecting unauthorized emails instead of just marking them as suspicious.

---

### 2. Configure Reverse DNS (PTR Record) - **CRITICAL**

**Status:** ❌ NOT CONFIGURED - This is causing email delivery failures

**What You Need:**
- IP Address: `41.61.20.39`
- Should resolve to: `mail.mygigguide.co.za`

**Action Required:**
Contact your hosting provider or ISP that controls IP address `41.61.20.39` and request:

> "Please configure a PTR (reverse DNS) record for IP address 41.61.20.39 to point to mail.mygigguide.co.za"

**Who to Contact:**
- Your hosting provider (if you have a VPS/dedicated server)
- Your ISP (if it's a static IP)
- Check your server/hosting account for support contact information

**How to Verify After Update:**
```bash
dig -x 41.61.20.39 +short
# Should return: mail.mygigguide.co.za
```

**Why It's Critical:**
- Many mail servers require reverse DNS for security
- Without it, emails are rejected or marked as spam
- Currently causing delivery failures to thando@kayiseit.com

---

## Current DNS Records Status

| Record Type | Name | Current Value | Status |
|-------------|------|---------------|--------|
| MX | mygigguide.co.za | 10 mail.mygigguide.co.za | ✅ OK |
| A | mail.mygigguide.co.za | 41.61.20.39 | ✅ OK |
| TXT (SPF) | mygigguide.co.za | v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all | ⚠️ Update to -all |
| TXT (DKIM) | default._domainkey.mygigguide.co.za | (DKIM key) | ✅ OK |
| TXT (DMARC) | _dmarc.mygigguide.co.za | v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za | ✅ OK |
| PTR | 39.20.61.41.in-addr.arpa | mail.mygigguide.co.za | ❌ **NOT CONFIGURED** |

---

## Testing After Updates

### Test SPF:
```bash
dig TXT mygigguide.co.za +short
# Should show: "v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 -all"
```

### Test Reverse DNS:
```bash
dig -x 41.61.20.39 +short
# Should return: mail.mygigguide.co.za
```

### Test Email Delivery:
After DNS updates, test sending again:
```bash
python3 << 'EOF'
import smtplib
from email.mime.text import MIMEText
server = smtplib.SMTP('localhost', 587)
server.starttls()
server.login('dave@mygigguide.co.za', 'Dave123!')
msg = MIMEText('Test email after DNS updates')
msg['From'] = 'dave@mygigguide.co.za'
msg['To'] = 'thando@kayiseit.com'
msg['Subject'] = 'Test Email'
server.sendmail('dave@mygigguide.co.za', 'thando@kayiseit.com', msg.as_string())
server.quit()
print("Email sent!")
EOF
```

### Check Mail Queue:
```bash
postqueue -p
# Should be empty after successful delivery
```

### Check Mail Logs:
```bash
tail -f /var/log/mail.log
# Watch for successful delivery messages
```

---

## Online DNS Testing Tools

1. **MXToolbox** - https://mxtoolbox.com/
   - Enter your domain: mygigguide.co.za
   - Test SPF, DKIM, DMARC, MX, Reverse DNS

2. **DNS Checker** - https://dnschecker.org/
   - Check DNS propagation globally

3. **Mail-Tester** - https://www.mail-tester.com/
   - Send a test email and get a spam score

---

## Email Queue Status

The test email to thando@kayiseit.com is currently in the queue and will:
- ✅ Retry automatically every few hours
- ✅ Deliver once reverse DNS is configured
- ✅ Be removed from queue after successful delivery

**Check queue:**
```bash
postqueue -p
```

**Force immediate retry:**
```bash
postqueue -f
```

---

## Summary

**What's Working:**
- ✅ SMTP authentication
- ✅ Email queuing
- ✅ SPF, DKIM, DMARC configured
- ✅ TLS/SSL encryption

**What Needs Action:**
1. ⚠️ Update SPF record: Change `~all` to `-all` (recommended)
2. ❌ **Configure Reverse DNS (PTR)** - **CRITICAL** - Contact your hosting provider

**Once DNS is updated:**
- Emails will deliver successfully
- Better email reputation
- Reduced spam filtering

---

**Last Updated:** 2026-01-13  
**Test Email Sent To:** thando@kayiseit.com  
**Status:** Queued, waiting for reverse DNS configuration
