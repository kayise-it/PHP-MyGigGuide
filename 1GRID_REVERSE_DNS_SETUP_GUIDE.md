# 1-GRID Reverse DNS Setup Guide

## Login Information

**URL:** https://1-grid.com/client/index.php?rp=/login  
**Username:** mwelmans@gmail.com  
**Password:** Poiu)987

---

## Step-by-Step Instructions

### Step 1: Log Into 1-GRID Client Area

1. Go to: https://1-grid.com/client/index.php?rp=/login
2. Enter your email: `mwelmans@gmail.com`
3. Enter your password: `Poiu)987`
4. Click "Login"

### Step 2: Find Your Server/Service

Once logged in, look for:
- **"Services"** or **"My Services"** menu
- **"Servers"** or **"VPS"** section
- **"Dedicated Servers"** if applicable
- Your server: **mel55-nix02** (hostname)

### Step 3: Locate Reverse DNS/PTR Settings

Look for one of these sections:

#### Option A: IP Management
- Navigate to: **Services** → **Your Server** → **IP Addresses** or **Network Settings**
- Find IP address: **41.61.20.39**
- Look for: **"Reverse DNS"**, **"PTR Record"**, or **"rDNS"** option

#### Option B: Server Management
- Navigate to: **Services** → **Your Server** → **Management** or **Settings**
- Look for: **"Network"**, **"IP Configuration"**, or **"DNS Settings"**
- Find: **Reverse DNS** or **PTR Record** section

#### Option C: Support Ticket
If you can't find the option:
1. Go to **"Support"** → **"Open Ticket"**
2. Select category: **"Technical Support"** or **"Server Management"**
3. Use the email template below

### Step 4: Configure Reverse DNS

**What to Set:**
- **IP Address:** 41.61.20.39
- **Reverse DNS (PTR):** mail.mygigguide.co.za
- **Hostname:** mail.mygigguide.co.za

**Important:** The PTR record should point FROM 41.61.20.39 TO mail.mygigguide.co.za

---

## Email Template for Support Ticket

**Subject:** Request Reverse DNS (PTR Record) Configuration for IP 41.61.20.39

**Message:**

Hello 1-GRID Support,

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

## What to Look For in Control Panel

### Common Locations:

1. **Services Menu:**
   - Services → My Services → [Your Server]
   - Look for: IP Addresses, Network Settings, DNS Management

2. **Server Management:**
   - Servers → mel55-nix02 → Management
   - Look for: Network, IP Configuration, Reverse DNS

3. **Network Settings:**
   - Network → IP Addresses → 41.61.20.39
   - Look for: Edit, Configure, Reverse DNS, PTR Record

4. **Advanced Settings:**
   - Settings → Advanced → Network
   - Look for: Reverse DNS, PTR Records

### What the Interface Might Look Like:

```
IP Address: 41.61.20.39
Reverse DNS: [text field] ← Enter: mail.mygigguide.co.za
[Save] [Update] [Apply]
```

Or:

```
IP Address Management
┌─────────────────────────────────┐
│ IP: 41.61.20.39                  │
│ Reverse DNS: [mail.mygigguide...]│
│ [Edit] [Configure PTR]           │
└─────────────────────────────────┘
```

---

## After Configuration

### 1. Verify PTR Record:
```bash
dig -x 41.61.20.39 +short
# Should return: mail.mygigguide.co.za
```

### 2. Wait for Propagation:
- PTR records can take 24-48 hours to propagate globally
- Some DNS servers update faster than others

### 3. Test Email Delivery:
```bash
# Check mail queue
postqueue -p

# Force immediate retry
postqueue -f

# Monitor logs
tail -f /var/log/mail.log | grep -i "sent\|delivered"
```

### 4. Check Online:
- **MXToolbox Reverse DNS:** https://mxtoolbox.com/ReverseLookup.aspx
  - Enter: 41.61.20.39
  - Should show: mail.mygigguide.co.za

---

## Troubleshooting

### If You Can't Find the Option:

1. **Check Different Sections:**
   - Look in "Advanced Settings"
   - Check "Network" or "IP Management"
   - Try "Server Configuration"

2. **Contact Support:**
   - Use the email template above
   - Mention you need PTR record configuration
   - Provide IP: 41.61.20.39

3. **Check Documentation:**
   - Look for "Reverse DNS" in 1-GRID help center
   - Search for "PTR record" in knowledge base

### If Configuration Doesn't Work:

1. **Verify Forward DNS First:**
   ```bash
   dig A mail.mygigguide.co.za +short
   # Should return: 41.61.20.39
   ```

2. **Check PTR Record:**
   ```bash
   dig -x 41.61.20.39 +short
   # Should return: mail.mygigguide.co.za
   ```

3. **Wait for Propagation:**
   - Can take up to 48 hours
   - Different DNS servers update at different times

---

## Quick Reference

**Your Server:**
- Hostname: mel55-nix02
- IP: 41.61.20.39
- Domain: mygigguide.co.za

**What to Configure:**
- PTR Record: 41.61.20.39 → mail.mygigguide.co.za

**1-GRID Login:**
- URL: https://1-grid.com/client/index.php?rp=/login
- Username: mwelmans@gmail.com
- Password: Poiu)987

---

**Last Updated:** 2026-01-13
