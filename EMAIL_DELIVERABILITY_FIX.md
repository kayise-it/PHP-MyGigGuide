# Email Deliverability Fix Guide

## Current Status

✅ **Working:**
- SPF record exists: `v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all`
- DKIM record exists and is valid
- DKIM signing is active (emails are being signed)
- DMARC record exists: `v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za`

❌ **Issues Causing Spam:**
1. **CRITICAL: Missing Reverse DNS (PTR) Record**
   - IP 41.61.20.39 has no reverse DNS
   - This is the #1 reason emails go to spam
   - **Action Required:** Contact 1-GRID to set PTR record

2. **SPF uses soft-fail (~all) instead of hard-fail (-all)**
   - Current: `~all` (soft-fail - emails may still be accepted)
   - Recommended: `-all` (hard-fail - better spam protection)
   - **Action Required:** Update DNS TXT record

3. **DMARC policy may be too strict**
   - Current: `p=quarantine` (emails go to spam if they fail)
   - If SPF/DKIM alignment isn't perfect, this causes spam
   - **Action Required:** Consider `p=none` for monitoring first, or ensure perfect alignment

## Fix Priority

### 1. URGENT: Set Reverse DNS (PTR) Record

**This is the most critical issue.** Without reverse DNS, many mail servers will reject or spam-filter your emails.

**Action:** Contact 1-GRID support and request:
- Set PTR record for IP 41.61.20.39 → `mail.mygigguide.co.za`
- Verify forward DNS: `mail.mygigguide.co.za` → `41.61.20.39` (already done)

**Message to send to 1-GRID:**
```
Subject: Urgent - Request Reverse DNS (PTR) Record for IP 41.61.20.39

Hello 1-GRID Support,

I need to configure a reverse DNS (PTR) record for my mail server IP address to improve email deliverability. Currently, emails are being marked as spam due to missing reverse DNS.

Server Details:
- IP Address: 41.61.20.39
- Domain: mygigguide.co.za
- Mail Server Hostname: mail.mygigguide.co.za

Request:
Please configure a PTR (reverse DNS) record so that reverse DNS lookup for IP address 41.61.20.39 returns "mail.mygigguide.co.za"

Current Status:
- Forward DNS (A record): ✅ Configured - mail.mygigguide.co.za → 41.61.20.39
- Reverse DNS (PTR record): ❌ Missing - 41.61.20.39 → (nothing)

Verification:
After configuration, this command should return "mail.mygigguide.co.za":
dig -x 41.61.20.39 +short

Please let me know when this is configured.

Thank you!
```

### 2. Update SPF Record (Recommended)

**Current SPF:**
```
v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all
```

**Recommended SPF (harder fail):**
```
v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 -all
```

**Change:** `~all` → `-all`

**Action:** Update the TXT record for `mygigguide.co.za` in your DNS provider (not 1-GRID, your domain DNS provider).

**Note:** Only change to `-all` if you're confident all legitimate senders are included. If unsure, keep `~all` for now.

### 3. Review DMARC Policy

**Current DMARC:**
```
v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za
```

**Options:**

**Option A: Monitor first (recommended if having issues)**
```
v=DMARC1; p=none; rua=mailto:admin@mygigguide.co.za; ruf=mailto:admin@mygigguide.co.za; pct=100
```
- `p=none`: Don't quarantine, just monitor
- `ruf=`: Add failure reports
- `pct=100`: Apply to 100% of emails

**Option B: Keep quarantine but add monitoring**
```
v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za; ruf=mailto:admin@mygigguide.co.za; pct=100; aspf=r; adkim=r
```
- `aspf=r`: Relaxed SPF alignment
- `adkim=r`: Relaxed DKIM alignment

**Action:** Update the TXT record for `_dmarc.mygigguide.co.za` in your DNS provider.

### 4. Verify DKIM Configuration

DKIM appears to be working, but verify:

**Test command:**
```bash
opendkim-testkey -d mygigguide.co.za -s default
```

**Check DNS:**
```bash
dig +short TXT default._domainkey.mygigguide.co.za
```

If DKIM test fails, we may need to regenerate keys.

## Testing After Changes

1. **Test SPF:**
   ```bash
   dig +short TXT mygigguide.co.za | grep spf
   ```

2. **Test DKIM:**
   ```bash
   dig +short TXT default._domainkey.mygigguide.co.za
   opendkim-testkey -d mygigguide.co.za -s default
   ```

3. **Test DMARC:**
   ```bash
   dig +short TXT _dmarc.mygigguide.co.za
   ```

4. **Test PTR (after 1-GRID configures it):**
   ```bash
   dig -x 41.61.20.39 +short
   # Should return: mail.mygigguide.co.za
   ```

5. **Online Tools:**
   - https://mxtoolbox.com/spf.aspx
   - https://mxtoolbox.com/dkim.aspx
   - https://mxtoolbox.com/dmarc.aspx
   - https://mxtoolbox.com/SuperTool.aspx?action=ptr%3a41.61.20.39

## Expected Results

After implementing all fixes:
- ✅ PTR record resolves correctly
- ✅ SPF passes with hard-fail
- ✅ DKIM signature valid
- ✅ DMARC alignment passes
- ✅ Emails deliver to inbox instead of spam

## Timeline

1. **Immediate:** Contact 1-GRID for PTR record (most critical)
2. **Within 24 hours:** Update SPF to `-all` (if confident)
3. **Within 48 hours:** Review and adjust DMARC policy
4. **After PTR is set:** Test all records and monitor deliverability

## Additional Tips

1. **Warm up your IP:** If this is a new server, send emails gradually (start with 50/day, increase slowly)
2. **Monitor reputation:** Check if your IP is on any blacklists (mxtoolbox.com)
3. **Keep engagement high:** Low open rates hurt deliverability
4. **Clean your lists:** Remove invalid/bouncing addresses
5. **Use proper email content:** Avoid spam trigger words, maintain good text-to-image ratio

## Support

If issues persist after implementing these fixes:
1. Check email headers for authentication results
2. Review DMARC aggregate reports (sent to admin@mygigguide.co.za)
3. Test with multiple email providers (Gmail, Outlook, Yahoo)
4. Monitor mail logs: `tail -f /var/log/mail.log`
