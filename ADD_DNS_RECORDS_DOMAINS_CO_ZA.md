# How to Add DNS Records in Domains.co.za

## Step-by-Step Instructions

### 1. Log in to Domains.co.za
- Go to: https://www.domains.co.za/client/services/domains/manage/432171
- Sign in to your account

### 2. Navigate to DNS Management
Look for one of these options:
- **"DNS Management"** or **"DNS Records"** tab/button
- **"Manage DNS"** or **"DNS Settings"**
- **"Nameservers"** or **"DNS Zone"**

### 3. Add the Following DNS Records

#### Record 1: MX Record (Mail Exchange)
- **Type**: MX
- **Name/Host**: `@` (or leave blank for root domain)
- **Priority**: `10`
- **Value/Target**: `mail.mygigguide.co.za`
- **TTL**: `3600` (or default)

#### Record 2: A Record (for mail subdomain)
- **Type**: A
- **Name/Host**: `mail`
- **Value/Target**: `41.61.20.39`
- **TTL**: `3600` (or default)

#### Record 3: SPF Record (TXT)
- **Type**: TXT
- **Name/Host**: `@` (or leave blank for root domain)
- **Value**: `v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all`
- **TTL**: `3600` (or default)

#### Record 4: DKIM Record (TXT)
- **Type**: TXT
- **Name/Host**: `default._domainkey`
- **Value**: `v=DKIM1; h=sha256; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAw2AgPlC2UHQ+qG/hf8UnXMaEc1/+utRv7EN/zXMgZcXmIfcJc4QAERA8D9en9daA6W0ueNZ/ffNUIMzG6IMQfBR6IBrmfTeh4q2ujALMTZezeea26q/i0DU2Y3omGOvYjFJKf4TxrEoJ8wUW2GAy5HXSZFUOqEHSJo74dkvx2y3BNNN+63petLDI/07oEYGhKyRe92S55geUPg0yiWqPN7uj2oel2bt6vQpXbr6ik889NPTUYWuXoBAOZhaf+Y5C48kOJmK6kYdQnrCpLJPUTtF3E6rNzGuo1GO89YyPCGL5Zr8K6r+wxUi3H2Uf2LgJOuPy6wW6X1pt/CSqzwF1aQIDAQAB`
- **TTL**: `3600` (or default)

**Important**: The DKIM value is very long. Make sure to copy the ENTIRE value including everything after `p=`

#### Record 5: DMARC Record (TXT)
- **Type**: TXT
- **Name/Host**: `_dmarc`
- **Value**: `v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za`
- **TTL**: `3600` (or default)

### 4. Save All Records
- Click **"Save"** or **"Update"** after adding each record
- Some interfaces allow adding multiple records at once

### 5. Verify Records
After adding, wait 5-10 minutes, then verify with:

```bash
/var/www/mygigguide/check-dns.sh
```

Or manually:
```bash
dig +short mx mygigguide.co.za
dig +short txt mygigguide.co.za | grep spf
dig +short txt default._domainkey.mygigguide.co.za
dig +short txt _dmarc.mygigguide.co.za
```

## Common Interface Locations

In Domains.co.za, DNS records are typically found under:
- **Domain Management** → **DNS Settings**
- **Domain Management** → **DNS Records**
- **Domain Management** → **Nameservers** → **DNS Zone Editor**

## Notes

- **TTL (Time To Live)**: 3600 seconds (1 hour) is standard, but you can use the default
- **Priority**: Only needed for MX records (lower number = higher priority)
- **Name/Host field**: 
  - Use `@` or leave blank for root domain records
  - Use `mail` for the mail subdomain A record
  - Use `default._domainkey` for DKIM
  - Use `_dmarc` for DMARC

## Troubleshooting

If you can't find DNS management:
1. Check if you're logged into the correct account
2. Look for "Advanced Settings" or "Domain Settings"
3. Contact Domains.co.za support if DNS management isn't available in your plan

## After Adding Records

Once all records are added:
1. Wait 5-10 minutes for DNS propagation
2. Run the verification script: `/var/www/mygigguide/check-dns.sh`
3. Test email: `php /var/www/mygigguide/simple-test-email.php`

---

**All DNS record values are also in**: `/var/www/mygigguide/REQUIRED_DNS_RECORDS.txt`
