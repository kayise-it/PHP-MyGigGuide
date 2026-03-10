# Request Reverse DNS (PTR Record) Configuration

## Current Situation

**IP Address:** 41.61.20.39  
**Should Resolve To:** mail.mygigguide.co.za  
**Status:** ❌ NOT CONFIGURED  
**Impact:** Email delivery failures to some providers that require reverse DNS

---

## What is Reverse DNS (PTR Record)?

Reverse DNS (PTR record) is the opposite of normal DNS:
- **Normal DNS:** mail.mygigguide.co.za → 41.61.20.39
- **Reverse DNS:** 41.61.20.39 → mail.mygigguide.co.za

Many mail servers check reverse DNS to verify that the sending server is legitimate and not a spammer.

---

## Who Can Configure Reverse DNS?

**Only the IP address owner can configure PTR records.** This is typically:
- Your hosting provider
- Your ISP (Internet Service Provider)
- Your VPS/dedicated server provider
- The organization that owns the IP address range

**You cannot configure it yourself** - it must be done by whoever controls the IP address.

---

## How to Request Reverse DNS Configuration

### Step 1: Identify Your Hosting Provider

Based on server information:
- **Server Hostname:** mel55-nix02
- **IP Address:** 41.61.20.39
- **Location:** Check your hosting account, billing emails, or server management panel

### Step 2: Contact Your Hosting Provider

Send them this request:

---

**Subject:** Request Reverse DNS (PTR Record) Configuration

**Message:**

Hello,

I need to configure a reverse DNS (PTR) record for my server's IP address to enable proper email delivery.

**Details:**
- **IP Address:** 41.61.20.39
- **Hostname:** mail.mygigguide.co.za
- **Domain:** mygigguide.co.za

**Request:**
Please configure a PTR record so that reverse DNS lookup for 41.61.20.39 returns "mail.mygigguide.co.za"

**Why:**
Many mail servers require reverse DNS for email delivery. Without it, emails are being rejected with the error:
"Reverse DNS for 41.61.20.39 failed"

**Verification:**
After configuration, this command should return "mail.mygigguide.co.za":
```
dig -x 41.61.20.39 +short
```

Please let me know when this has been configured.

Thank you!

---

### Step 3: Alternative - Check Your Hosting Control Panel

Some hosting providers allow you to configure reverse DNS yourself:

1. **Log into your hosting control panel** (cPanel, Plesk, custom panel, etc.)
2. **Look for sections like:**
   - "Reverse DNS"
   - "PTR Records"
   - "IP Management"
   - "Network Settings"
   - "DNS Management"
3. **Find your IP address** (41.61.20.39)
4. **Set reverse DNS** to: `mail.mygigguide.co.za`

---

## Common Hosting Providers

### If you're using:
- **cPanel:** Look in "WHM" → "IP Functions" → "Configure Reverse DNS"
- **Plesk:** Look in "Tools & Settings" → "IP Addresses" → "Reverse DNS"
- **AWS:** Use Route 53 or EC2 Elastic IP reverse DNS
- **DigitalOcean:** Use Networking → Reserved IPs → Edit → Reverse DNS
- **Linode:** Use Networking → IPs → Edit → Reverse DNS
- **Vultr:** Use Settings → IP Addresses → Edit → Reverse DNS
- **Hetzner:** Use IPs → Edit → Reverse DNS
- **OVH:** Use IP → Reverse DNS

---

## Verification

After your hosting provider configures the PTR record:

### Test Reverse DNS:
```bash
dig -x 41.61.20.39 +short
# Should return: mail.mygigguide.co.za
```

### Test Email Delivery:
```bash
# Check mail queue
postqueue -p

# Force retry
postqueue -f

# Check logs
tail -f /var/log/mail.log
```

### Online Testing:
- **MXToolbox:** https://mxtoolbox.com/ReverseLookup.aspx
  - Enter IP: 41.61.20.39
  - Should show: mail.mygigguide.co.za

---

## Current Email Queue Status

Your emails are currently queued and will retry automatically. Once reverse DNS is configured:
- ✅ Emails will deliver successfully
- ✅ Better email reputation
- ✅ Reduced spam filtering

**Check queue:**
```bash
postqueue -p
```

---

## If You Don't Know Your Hosting Provider

1. **Check billing emails** - Look for hosting invoices
2. **Check server hostname** - "mel55-nix02" might indicate the provider
3. **Check IP ownership:**
   ```bash
   whois 41.61.20.39
   ```
4. **Check server documentation** - Look for setup notes or credentials
5. **Contact your system administrator** - If someone else set up the server

---

## Important Notes

- ⏱️ **PTR record changes can take 24-48 hours to propagate**
- 🔒 **Only the IP owner can set PTR records** - You cannot do this yourself
- 📧 **Emails will retry automatically** - No need to resend
- ✅ **Once configured, future emails will deliver immediately**

---

## Quick Reference

**IP Address:** 41.61.20.39  
**PTR Record Should Point To:** mail.mygigguide.co.za  
**Current Status:** ❌ Not configured  
**Action Required:** Contact hosting provider

---

**Last Updated:** 2026-01-13
