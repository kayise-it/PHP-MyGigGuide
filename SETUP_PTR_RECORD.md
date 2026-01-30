# How to Set Up PTR Record for 1 GRID VPS

## Your VPS Provider
- **Provider**: 1 GRID (PTY) LTD
- **IP Address**: 41.61.20.39
- **Hostname**: mel55-nix02

## Option 1: Check 1 GRID Control Panel (Recommended)

1. **Log in to your 1 GRID customer portal/control panel**
2. **Look for**:
   - "Reverse DNS" or "PTR Records"
   - "Network Settings"
   - "IP Management"
   - "DNS Management" (might have a reverse DNS section)

3. **Add PTR Record**:
   - IP Address: `41.61.20.39`
   - Hostname: `mail.mygigguide.co.za`
   - Save/Apply

## Option 2: Contact 1 GRID Support

If you can't find it in the control panel, contact 1 GRID support:

**Request Template:**
```
Subject: Request for PTR (Reverse DNS) Record Setup

Hello 1 GRID Support,

I need a PTR (reverse DNS) record configured for my VPS IP address 
to enable email delivery to Gmail and other email providers.

VPS IP Address: 41.61.20.39
Should point to: mail.mygigguide.co.za

This is required for email authentication. Please set this up.

Thank you!
```

**Contact Methods:**
- Support ticket through 1 GRID portal
- Email support (check your 1 GRID account for contact details)
- Phone support (if available)

## Option 3: Check if You Have API Access

Some VPS providers allow PTR record management via API. Check 1 GRID's documentation for:
- API access for DNS/network management
- Reverse DNS API endpoints

## Verify After Setup

Once the PTR record is added, verify with:

```bash
dig -x 41.61.20.39
```

Expected result:
```
41.61.20.39.in-addr.arpa. 3600 IN PTR mail.mygigguide.co.za.
```

Or:
```bash
host 41.61.20.39
```

Expected result:
```
39.20.61.41.in-addr.arpa domain name pointer mail.mygigguide.co.za.
```

## Important Notes

- **PTR records are NOT set on the VPS itself** - they're set at the network/DNS level by the provider
- **You cannot add PTR records in your domain's DNS management** (Domains.co.za)
- **Only 1 GRID can add the PTR record** for IP `41.61.20.39`
- **It usually takes 1-24 hours** to propagate after being set

## Why This Matters

Gmail and many email providers check PTR records to verify:
- The IP address is legitimate
- The server identity matches
- Email authentication is complete

Without PTR, Gmail will reject your emails.

---

**Next Steps**: Log into your 1 GRID control panel and look for reverse DNS/PTR record management, or contact their support.
