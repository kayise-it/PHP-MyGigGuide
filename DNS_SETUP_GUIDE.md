# DNS Records Setup Guide for mygigguide.co.za

## Your Server Information

**Hostname:** mel55-nix02  
**Primary IP:** 41.61.20.39  
**Nameservers:** ns1, ns2 (1-GRID nameservers)

---

## DNS Records You Need to Configure

### 1. A Record (Points domain to your server)

**Type:** A  
**Name:** mail.mygigguide.co.za  
**Value:** 41.61.20.39  
**TTL:** 3600 (or default)

**Status:** ✅ Already configured

---

### 2. MX Record (Mail Exchange - where emails go)

**Type:** MX  
**Name:** mygigguide.co.za  
**Priority:** 10  
**Value:** mail.mygigguide.co.za  
**TTL:** 3600 (or default)

**Status:** ✅ Already configured

---

### 3. SPF Record (Email authentication)

**Type:** TXT  
**Name:** mygigguide.co.za  
**Value:** `v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 -all`

**Current Value:** `v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all`

**⚠️ Recommended:** Change `~all` to `-all` for better security

**Status:** ✅ Configured (but should update `~all` to `-all`)

---

### 4. DKIM Record (Email signing)

**Type:** TXT  
**Name:** default._domainkey.mygigguide.co.za  
**Value:** (Already configured - see below)

**Status:** ✅ Already configured

**Current Value:**
```
v=DKIM1; h=sha256; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAw2AgPlC2UHQ+qG/hf8UnXMaEc1/+utRv7EN/zXMgZcXmIfcJc4QAERA8D9en9daA6W0ueNZ/ffNUIMzG6IMQfBR6IBrmfTeh4q2ujALMTZezeea26q/i0DU2Y3omGOvYjFJKf4TxrEoJ8wUW2GAy5HXSZFUOqEHSJo74dkvx2y3BNNN+63petLDI/07oEYGhKyRe92S55geUPg0yiWqPN7uj2oel2bt6vQpXbr6ik889NPTUYWuXoBAOZhaf+Y5C48kOJmK6kYdQnrCpLJPUTtF3E6rNzGuo1GO89YyPCGL5Zr8K6r+wxUi3H2Uf2LgJOuPy6wW6X1pt/CSqzwF1aQIDAQAB
```

---

### 5. DMARC Record (Email policy)

**Type:** TXT  
**Name:** _dmarc.mygigguide.co.za  
**Value:** `v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za`

**Status:** ✅ Already configured

---

### 6. Reverse DNS (PTR Record) - **CRITICAL FOR EMAIL**

**Type:** PTR  
**IP Address:** 41.61.20.39  
**Value:** mail.mygigguide.co.za

**Status:** ❌ **NOT CONFIGURED** - This is why emails can't be sent!

**Where to Configure:**
- **NOT in your domain DNS** - This must be configured by 1-GRID
- Go to 1-GRID control panel → Server Management → Configurable Options
- Or contact 1-GRID support

---

## Where to Configure DNS Records

### For Records 1-5 (A, MX, SPF, DKIM, DMARC):

These are configured at your **domain registrar** or **DNS provider** (where you manage mygigguide.co.za domain).

**Common DNS Providers:**
- Your domain registrar's control panel
- Cloudflare (if using)
- Route 53 (if using AWS)
- Your hosting provider's DNS management

**Steps:**
1. Log into your domain registrar/DNS provider
2. Find "DNS Management" or "DNS Settings"
3. Add/edit the records listed above
4. Save changes
5. Wait for propagation (5-60 minutes, up to 48 hours)

### For Record 6 (Reverse DNS/PTR):

**This MUST be configured by 1-GRID** (your hosting provider).

**Option 1: Check "Configurable Options" Tab**

1. In your 1-GRID control panel (where you see Server Information)
2. Click the **"Configurable Options"** tab
3. Look for:
   - "Reverse DNS"
   - "PTR Record"
   - "rDNS"
   - "IP Management"
4. Find IP: 41.61.20.39
5. Set reverse DNS to: mail.mygigguide.co.za
6. Save

**Option 2: Contact 1-GRID Support**

If you can't find it in Configurable Options, open a support ticket with this message:

```
Subject: Request Reverse DNS (PTR Record) Configuration

Hello 1-GRID Support,

I need to configure reverse DNS for my server's IP address.

Server: mel55-nix02
IP Address: 41.61.20.39
Domain: mygigguide.co.za

Request: Please configure PTR record so that 41.61.20.39 resolves to mail.mygigguide.co.za

This is urgent for email delivery.

Thank you!
```

---

## Step-by-Step: Setting Up DNS Records

### Step 1: Log Into Your Domain DNS Provider

1. Go to your domain registrar's website
2. Log into your account
3. Find "DNS Management" or "DNS Settings"
4. Select domain: mygigguide.co.za

### Step 2: Add/Update Records

#### A Record (if not exists):
- **Type:** A
- **Name:** mail
- **Value:** 41.61.20.39
- **TTL:** 3600
- **Save**

#### MX Record (if not exists):
- **Type:** MX
- **Name:** @ (or mygigguide.co.za)
- **Priority:** 10
- **Value:** mail.mygigguide.co.za
- **TTL:** 3600
- **Save**

#### SPF Record (Update if needed):
- **Type:** TXT
- **Name:** @ (or mygigguide.co.za)
- **Value:** `v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 -all`
- **TTL:** 3600
- **Save**

#### DKIM Record (if not exists):
- **Type:** TXT
- **Name:** default._domainkey
- **Value:** (Use the full DKIM key from above)
- **TTL:** 3600
- **Save**

#### DMARC Record (if not exists):
- **Type:** TXT
- **Name:** _dmarc
- **Value:** `v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za`
- **TTL:** 3600
- **Save**

### Step 3: Configure Reverse DNS (PTR)

1. **In 1-GRID Control Panel:**
   - Go to Server Information page
   - Click **"Configurable Options"** tab
   - Look for Reverse DNS/PTR settings
   - Set: 41.61.20.39 → mail.mygigguide.co.za

2. **Or Contact 1-GRID Support** (use message above)

---

## Verify Your DNS Records

After configuring, verify with these commands:

```bash
# A Record
dig A mail.mygigguide.co.za +short
# Should return: 41.61.20.39

# MX Record
dig MX mygigguide.co.za +short
# Should return: 10 mail.mygigguide.co.za.

# SPF Record
dig TXT mygigguide.co.za +short | grep spf
# Should show your SPF record

# DKIM Record
dig TXT default._domainkey.mygigguide.co.za +short
# Should show your DKIM key

# DMARC Record
dig TXT _dmarc.mygigguide.co.za +short
# Should show your DMARC policy

# Reverse DNS (PTR) - MOST IMPORTANT
dig -x 41.61.20.39 +short
# Should return: mail.mygigguide.co.za
```

---

## Current Status Summary

| Record | Status | Action Needed |
|--------|--------|---------------|
| A Record | ✅ Configured | None |
| MX Record | ✅ Configured | None |
| SPF Record | ✅ Configured | Update `~all` to `-all` (optional) |
| DKIM Record | ✅ Configured | None |
| DMARC Record | ✅ Configured | None |
| **Reverse DNS (PTR)** | ❌ **NOT CONFIGURED** | **URGENT - Configure via 1-GRID** |

---

## Important Notes

1. **Forward DNS Records (A, MX, SPF, DKIM, DMARC):**
   - Configured at your domain DNS provider
   - You can manage these yourself

2. **Reverse DNS (PTR):**
   - **MUST be configured by 1-GRID**
   - You cannot configure this yourself
   - This is the main issue preventing email delivery

3. **DNS Propagation:**
   - Changes can take 5-60 minutes
   - Can take up to 48 hours globally
   - Reverse DNS may take 24-48 hours after 1-GRID configures it

---

## Quick Action Items

### Immediate (To Fix Email Sending):

1. ✅ **Check "Configurable Options" tab** in 1-GRID control panel
2. ✅ **Look for Reverse DNS/PTR settings**
3. ✅ **Set: 41.61.20.39 → mail.mygigguide.co.za**
4. ✅ **If not found, contact 1-GRID support** (use message above)

### Optional (For Better Security):

1. Update SPF record: Change `~all` to `-all` in your DNS provider

---

**Last Updated:** 2026-01-13  
**Server:** mel55-nix02 (41.61.20.39)  
**Domain:** mygigguide.co.za
