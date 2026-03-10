# DNS Records for Secure Email - mygigguide.co.za

This document contains all DNS records required for secure email sending and receiving.

## Current DNS Records Status

### ✅ SPF (Sender Policy Framework) Record
**Type:** TXT  
**Name:** `mygigguide.co.za`  
**Value:** `v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all`

**⚠️ RECOMMENDED CHANGE FOR BETTER SECURITY:**
Change `~all` to `-all` for hard fail (reject emails not from authorized servers):
```
v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 -all
```

**What it does:**
- Authorizes mail.mygigguide.co.za to send emails
- Authorizes IP 41.61.20.39 to send emails
- `~all` = soft fail (mark as suspicious but don't reject)
- `-all` = hard fail (reject unauthorized emails)

---

### ✅ DKIM (DomainKeys Identified Mail) Record
**Type:** TXT  
**Name:** `default._domainkey.mygigguide.co.za`  
**Value:**
```
v=DKIM1; h=sha256; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAw2AgPlC2UHQ+qG/hf8UnXMaEc1/+utRv7EN/zXMgZcXmIfcJc4QAERA8D9en9daA6W0ueNZ/ffNUIMzG6IMQfBR6IBrmfTeh4q2ujALMTZezeea26q/i0DU2Y3omGOvYjFJKf4TxrEoJ8wUW2GAy5HXSZFUOqEHSJo74dkvx2y3BNNN+63petLDI/07oEYGhKyRe92S55geUPg0yiWqPN7uj2oel2bt6vQpXbr6ik889NPTUYWuXoBAOZhaf+Y5C48kOJmK6kYdQnrCpLJPUTtF3E6rNzGuo1GO89YyPCGL5Zr8K6r+wxUi3H2Uf2LgJOuPy6wW6X1pt/CSqzwF1aQIDAQAB
```

**What it does:**
- Signs outgoing emails with cryptographic signature
- Receiving servers verify the signature to ensure email authenticity
- Prevents email spoofing

**Status:** ✅ Configured and working

---

### ✅ DMARC (Domain-based Message Authentication, Reporting & Conformance) Record
**Type:** TXT  
**Name:** `_dmarc.mygigguide.co.za`  
**Current Value:** `v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za`

**RECOMMENDED OPTIMIZED VALUE:**
```
v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za; ruf=mailto:admin@mygigguide.co.za; pct=100; sp=quarantine; aspf=r; adkim=r; fo=1; rf=afrf; ri=86400
```

**Explanation:**
- `p=quarantine` - Quarantine emails that fail DMARC (move to spam)
- `rua=mailto:admin@mygigguide.co.za` - Send aggregate reports to this email
- `ruf=mailto:admin@mygigguide.co.za` - Send forensic reports (failed emails)
- `pct=100` - Apply policy to 100% of emails
- `sp=quarantine` - Policy for subdomains
- `aspf=r` - SPF alignment: relaxed
- `adkim=r` - DKIM alignment: relaxed
- `fo=1` - Generate reports for all failures
- `rf=afrf` - Report format: Authentication Failure Reporting Format
- `ri=86400` - Report interval: 1 day (86400 seconds)

**What it does:**
- Tells receiving servers what to do with emails that fail SPF/DKIM
- Provides email authentication reporting
- Protects against email spoofing and phishing

---

### ⚠️ PTR (Reverse DNS) Record - REQUIRED
**Type:** PTR  
**Name:** `39.20.61.41.in-addr.arpa` (for IP 41.61.20.39)  
**Value:** `mail.mygigguide.co.za`

**Status:** ❌ NOT CONFIGURED (Contact your hosting provider/ISP)

**What it does:**
- Allows reverse DNS lookup of your IP address
- Many mail servers require this for email delivery
- Currently causing email delivery failures to some providers that require reverse DNS

**Action Required:**
Contact your hosting provider or ISP that controls IP address 41.61.20.39 and request:
- PTR record for 41.61.20.39 pointing to `mail.mygigguide.co.za`

---

### ✅ MX (Mail Exchange) Record
**Type:** MX  
**Name:** `mygigguide.co.za`  
**Priority:** 10  
**Value:** `mail.mygigguide.co.za`

**Status:** ✅ Configured

**What it does:**
- Tells other mail servers where to deliver emails for @mygigguide.co.za

---

### ✅ A Record for Mail Server
**Type:** A  
**Name:** `mail.mygigguide.co.za`  
**Value:** `41.61.20.39`

**Status:** ✅ Configured

---

## Summary of Required DNS Records

| Type | Name | Value | Status |
|------|------|-------|--------|
| MX | mygigguide.co.za | 10 mail.mygigguide.co.za | ✅ |
| A | mail.mygigguide.co.za | 41.61.20.39 | ✅ |
| TXT | mygigguide.co.za | v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 -all | ⚠️ Update to -all |
| TXT | default._domainkey.mygigguide.co.za | (DKIM key) | ✅ |
| TXT | _dmarc.mygigguide.co.za | (DMARC policy) | ✅ |
| PTR | 39.20.61.41.in-addr.arpa | mail.mygigguide.co.za | ❌ Contact ISP |

---

## Security Checklist

- [x] SPF record configured
- [x] DKIM record configured and signing emails
- [x] DMARC record configured
- [ ] PTR record configured (contact ISP)
- [x] TLS/SSL certificates (Let's Encrypt)
- [x] Strong TLS ciphers configured
- [x] TLS 1.2+ only
- [x] SMTP authentication enabled (port 587)
- [x] IMAP/SMTP over TLS (ports 993/465)

---

## Testing Your DNS Records

### Test SPF:
```bash
dig TXT mygigguide.co.za +short
```

### Test DKIM:
```bash
dig TXT default._domainkey.mygigguide.co.za +short
```

### Test DMARC:
```bash
dig TXT _dmarc.mygigguide.co.za +short
```

### Test Reverse DNS:
```bash
dig -x 41.61.20.39 +short
# Should return: mail.mygigguide.co.za
```

### Test MX:
```bash
dig MX mygigguide.co.za +short
```

---

## Online Testing Tools

1. **MXToolbox** - https://mxtoolbox.com/
   - Enter your domain and test SPF, DKIM, DMARC, MX, etc.

2. **DMARC Analyzer** - https://www.dmarcanalyzer.com/
   - Test DMARC configuration

3. **Mail-Tester** - https://www.mail-tester.com/
   - Send a test email and get a spam score

---

## Mail Server Configuration

### SMTP Settings (Outgoing)
- **Server:** mail.mygigguide.co.za
- **Port:** 587 (STARTTLS) or 465 (SSL/TLS)
- **Security:** STARTTLS (port 587) or SSL/TLS (port 465)
- **Authentication:** Required (use full email address and password)

### IMAP Settings (Incoming)
- **Server:** mail.mygigguide.co.za
- **Port:** 143 (STARTTLS) or 993 (SSL/TLS)
- **Security:** STARTTLS (port 143) or SSL/TLS (port 993)
- **Authentication:** Required (use full email address and password)

---

## Notes

- DNS changes can take up to 48 hours to propagate globally
- PTR records must be set by your hosting provider/ISP
- Keep DKIM private key secure (stored in /etc/opendkim/keys/)
- Monitor DMARC reports to ensure legitimate emails are not being blocked
- Start with `p=quarantine` in DMARC, then move to `p=reject` after monitoring

---

**Last Updated:** 2026-01-13  
**Domain:** mygigguide.co.za  
**Mail Server:** mail.mygigguide.co.za (41.61.20.39)
