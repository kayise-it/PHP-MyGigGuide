# Contact 1 GRID for Reverse DNS Configuration

## Hosting Provider Information

**Provider:** 1 GRID (PTY) LTD  
**ASN:** AS36943  
**Location:** Cape Town, South Africa  
**IP Address:** 41.61.20.39  
**Server Hostname:** mel55-nix02

---

## Contact Information

### Website
- **1 GRID:** https://www.1grid.com

### Contact Methods
1. **Support Portal:** Log into your 1 GRID client area/control panel
2. **Support Email:** Check your 1 GRID account emails or billing emails
3. **Phone:** Check your 1 GRID account dashboard
4. **Support Ticket:** Create a ticket in your 1 GRID client area

---

## Email Template to Send

**Subject:** Request Reverse DNS (PTR Record) Configuration for IP 41.61.20.39

**Message:**

Hello 1 GRID Support Team,

I need to configure a reverse DNS (PTR) record for my server's IP address to enable proper email delivery.

**Server Details:**
- **Server Hostname:** mel55-nix02
- **IP Address:** 41.61.20.39
- **Domain:** mygigguide.co.za
- **Mail Server Hostname:** mail.mygigguide.co.za

**Request:**
Please configure a PTR (reverse DNS) record so that reverse DNS lookup for IP address 41.61.20.39 returns "mail.mygigguide.co.za"

**Why This Is Needed:**
Many mail servers require reverse DNS for email delivery. Without it, emails are being rejected with the error:
"Reverse DNS for 41.61.20.39 failed"

**Current Status:**
- Forward DNS (A record): ✅ Configured - mail.mygigguide.co.za → 41.61.20.39
- Reverse DNS (PTR record): ❌ Missing - 41.61.20.39 → (nothing)

**Verification:**
After configuration, this command should return "mail.mygigguide.co.za":
```bash
dig -x 41.61.20.39 +short
```

Currently it returns nothing, which is causing email delivery failures.

**Impact:**
- Emails to some providers are being rejected
- Email queue is building up
- Need this configured urgently for business email delivery

Please let me know:
1. If you can configure this PTR record
2. Estimated time for configuration
3. If there's a self-service option in the control panel

Thank you for your assistance!

---

## Alternative: Check 1 GRID Control Panel

1. **Log into your 1 GRID client area**
   - Usually at: https://client.1grid.com or similar
   - Check your account emails for the login URL

2. **Look for:**
   - "Reverse DNS" section
   - "PTR Records" 
   - "IP Management"
   - "Network Settings"
   - "DNS Management"
   - "Server Management"

3. **Find your IP address:** 41.61.20.39

4. **Set reverse DNS to:** `mail.mygigguide.co.za`

---

## Verification Steps

After 1 GRID configures the PTR record (may take 24-48 hours):

### Test Reverse DNS:
```bash
dig -x 41.61.20.39 +short
# Should return: mail.mygigguide.co.za
```

### Test Email Delivery:
```bash
# Check mail queue
postqueue -p

# Force immediate retry
postqueue -f

# Monitor logs
tail -f /var/log/mail.log | grep -i "sent\|delivered"
```

### Online Testing:
- **MXToolbox Reverse DNS:** https://mxtoolbox.com/ReverseLookup.aspx
  - Enter: 41.61.20.39
  - Should show: mail.mygigguide.co.za

---

## Current Email Status

**Emails in Queue:**
- Test email to thando@kayiseit.com
- Will retry automatically every few hours
- Will deliver once PTR record is configured

**Check Queue:**
```bash
postqueue -p
```

---

## Important Notes

- ⏱️ **PTR record changes can take 24-48 hours to propagate globally**
- 🔒 **Only 1 GRID can set this** - They control the IP address
- 📧 **Emails will retry automatically** - No need to resend
- ✅ **Once configured, future emails will deliver immediately**

---

## Quick Reference

**Provider:** 1 GRID (PTY) LTD  
**IP Address:** 41.61.20.39  
**PTR Record Should Point To:** mail.mygigguide.co.za  
**Current Status:** ❌ Not configured  
**Action:** Contact 1 GRID support

---

**Last Updated:** 2026-01-13
