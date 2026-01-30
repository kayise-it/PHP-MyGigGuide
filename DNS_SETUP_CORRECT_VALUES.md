# Correct DNS Record Values for Domains.co.za Interface

## ⚠️ IMPORTANT: What You're Currently Doing Wrong

In your screenshot, I can see:
- **Host**: `@.mygigguide.co.za` 
- **Type**: `A`
- **Value**: `mail.mygigguide.co.za` ❌ **WRONG!**

**Problem**: For an A record, the Value field MUST be an IP address, not a hostname!

---

## ✅ Correct DNS Records to Add (In Order)

### Record 1: A Record for Mail Subdomain
**This is what you're trying to add, but with correct values:**

- **Host**: `mail` (NOT `@.mygigguide.co.za`)
- **TTL**: `3600`
- **Type**: `A`
- **Priority**: `0` (or leave blank - not used for A records)
- **Value**: `41.61.20.39` ✅ (This is the IP address, NOT a hostname!)
- Click **"Add"**

---

### Record 2: MX Record (Mail Exchange)
- **Host**: `@` (or leave blank, or just `mygigguide.co.za`)
- **TTL**: `3600`
- **Type**: `MX` (Change from A to MX in dropdown)
- **Priority**: `10` ✅ (This is important for MX records!)
- **Value**: `mail.mygigguide.co.za`
- Click **"Add"**

---

### Record 3: SPF Record (TXT)
- **Host**: `@` (or leave blank)
- **TTL**: `3600`
- **Type**: `TXT` (Change from A to TXT in dropdown)
- **Priority**: `0` (or leave blank - not used for TXT)
- **Value**: `v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all`
- Click **"Add"**

---

### Record 4: DKIM Record (TXT)
- **Host**: `default._domainkey`
- **TTL**: `3600`
- **Type**: `TXT`
- **Priority**: `0` (or leave blank)
- **Value**: `v=DKIM1; h=sha256; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAw2AgPlC2UHQ+qG/hf8UnXMaEc1/+utRv7EN/zXMgZcXmIfcJc4QAERA8D9en9daA6W0ueNZ/ffNUIMzG6IMQfBR6IBrmfTeh4q2ujALMTZezeea26q/i0DU2Y3omGOvYjFJKf4TxrEoJ8wUW2GAy5HXSZFUOqEHSJo74dkvx2y3BNNN+63petLDI/07oEYGhKyRe92S55geUPg0yiWqPN7uj2oel2bt6vQpXbr6ik889NPTUYWuXoBAOZhaf+Y5C48kOJmK6kYdQnrCpLJPUTtF3E6rNzGuo1GO89YyPCGL5Zr8K6r+wxUi3H2Uf2LgJOuPy6wW6X1pt/CSqzwF1aQIDAQAB`
- Click **"Add"**

**Note**: The DKIM value is very long - make sure to copy the ENTIRE value!

---

### Record 5: DMARC Record (TXT)
- **Host**: `_dmarc`
- **TTL**: `3600`
- **Type**: `TXT`
- **Priority**: `0` (or leave blank)
- **Value**: `v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za`
- Click **"Add"**

---

## 📋 Quick Checklist

- [ ] A record: Host=`mail`, Type=`A`, Value=`41.61.20.39`
- [ ] MX record: Host=`@`, Type=`MX`, Priority=`10`, Value=`mail.mygigguide.co.za`
- [ ] SPF record: Host=`@`, Type=`TXT`, Value=`v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all`
- [ ] DKIM record: Host=`default._domainkey`, Type=`TXT`, Value=(long key above)
- [ ] DMARC record: Host=`_dmarc`, Type=`TXT`, Value=`v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za`

---

## 🔍 Key Differences from What You Had

1. **A Record Host**: Should be `mail` (not `@.mygigguide.co.za`)
2. **A Record Value**: Must be IP address `41.61.20.39` (not `mail.mygigguide.co.za`)
3. **MX Record**: Separate record needed with Type=`MX`, Priority=`10`
4. **TXT Records**: Need 3 separate TXT records (SPF, DKIM, DMARC)

---

## ✅ After Adding All Records

1. Wait 5-10 minutes for DNS propagation
2. Verify with: `/var/www/mygigguide/check-dns.sh`
3. Test email: `php /var/www/mygigguide/simple-test-email.php`
