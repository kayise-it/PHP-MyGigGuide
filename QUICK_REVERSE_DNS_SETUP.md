# Quick Reverse DNS Setup for mail.mygigguide.co.za

## ⚠️ Important: Reverse DNS Cannot Be Configured from mail.mygigguide.co.za

**Reverse DNS (PTR records) must be configured by your hosting provider (1-GRID), not from your mail server.**

---

## Current Status

**IP Address:** 41.61.20.39  
**Should Resolve To:** mail.mygigguide.co.za  
**Current Status:** ❌ NOT CONFIGURED

---

## How to Configure Reverse DNS

### Option 1: 1-GRID Control Panel (Self-Service)

1. **Log into 1-GRID:**
   - URL: https://1-grid.com/client/index.php?rp=/login
   - Username: mwelmans@gmail.com
   - Password: Poiu)987

2. **Navigate to Server Management:**
   - Go to: **Services** → **My Services** → **[Your Server: mel55-nix02]**
   - Or: **Servers** → **mel55-nix02** → **Management**

3. **Find IP Address Settings:**
   - Look for: **"IP Addresses"**, **"Network Settings"**, or **"IP Management"**
   - Find IP: **41.61.20.39**

4. **Configure Reverse DNS:**
   - Look for: **"Reverse DNS"**, **"PTR Record"**, or **"rDNS"**
   - Enter: **mail.mygigguide.co.za**
   - Click: **"Save"** or **"Update"**

### Option 2: Contact 1-GRID Support

If you can't find the option in the control panel:

1. **Open Support Ticket:**
   - Go to: **Support** → **Open Ticket**
   - Category: **Technical Support** or **Server Management**

2. **Use This Request:**

```
Subject: Request Reverse DNS (PTR Record) Configuration

Hello 1-GRID Support,

I need to configure a reverse DNS (PTR) record for my server's IP address.

Server Details:
- Server: mel55-nix02
- IP Address: 41.61.20.39
- Domain: mygigguide.co.za

Request:
Please configure a PTR record so that reverse DNS lookup for 41.61.20.39 returns "mail.mygigguide.co.za"

This is needed for email delivery to work properly.

Thank you!
```

---

## Verification After Configuration

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

# Monitor logs
tail -f /var/log/mail.log | grep -i "sent\|delivered"
```

### Online Test:
- **MXToolbox:** https://mxtoolbox.com/ReverseLookup.aspx
  - Enter: 41.61.20.39
  - Should show: mail.mygigguide.co.za

---

## Why This Is Needed

**Current Problem:**
- Some email providers require reverse DNS for security
- Without it, emails are rejected with: "Reverse DNS for 41.61.20.39 failed"
- Emails to some recipients cannot be delivered

**After Configuration:**
- ✅ Reverse DNS verified
- ✅ All email providers accept emails
- ✅ All queued emails will deliver
- ✅ Future emails deliver immediately

---

## Timeline

- **Configuration:** 1-2 business days (depends on 1-GRID response)
- **DNS Propagation:** 24-48 hours after configuration
- **Email Delivery:** Immediate after propagation

---

## Quick Reference

**Your Server:**
- Hostname: mel55-nix02
- IP: 41.61.20.39
- Domain: mygigguide.co.za

**What to Configure:**
- PTR Record: 41.61.20.39 → mail.mygigguide.co.za

**Where:**
- 1-GRID Control Panel: https://1-grid.com/client/index.php?rp=/login
- Or: Contact 1-GRID Support

---

**Note:** You cannot configure reverse DNS from mail.mygigguide.co.za. It must be done through 1-GRID (your hosting provider) as they control the IP address.

---

**Last Updated:** 2026-01-13
