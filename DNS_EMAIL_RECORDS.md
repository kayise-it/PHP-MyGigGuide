# DNS Records Required for Email Authentication

## Current Status

✅ **Email was sent successfully from the server**
❌ **Gmail rejected it** because SPF and DKIM records are missing

## Required DNS Records

Add these DNS records at your domain registrar (where you manage mygigguide.co.za):

### 1. MX Record (Mail Exchange)
```
Type: MX
Name: @ (or mygigguide.co.za)
Priority: 10
Value: mail.mygigguide.co.za
```

### 2. A Record (for mail subdomain)
```
Type: A
Name: mail
Value: 41.61.20.39 (your server IP)
```

### 3. SPF Record (Sender Policy Framework)
```
Type: TXT
Name: @ (or mygigguide.co.za)
Value: v=spf1 mx a:mail.mygigguide.co.za ip4:41.61.20.39 ~all
```

### 4. DKIM Record (DomainKeys Identified Mail)

After generating DKIM keys (see below), add:
```
Type: TXT
Name: default._domainkey (or selector._domainkey)
Value: v=DKIM1; k=rsa; p=YOUR_PUBLIC_KEY_HERE
```

### 5. DMARC Record (Domain-based Message Authentication)
```
Type: TXT
Name: _dmarc
Value: v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za
```

## Generating DKIM Keys

Run these commands on your server:

```bash
# Install OpenDKIM if not already installed
sudo apt-get install -y opendkim opendkim-tools

# Generate DKIM key
sudo opendkim-genkey -b 2048 -d mygigguide.co.za -s default

# View the public key (to add to DNS)
sudo cat /etc/opendkim/keys/default.txt
```

The output will show the TXT record value to add to DNS.

## Quick Setup Script

After adding DNS records, wait 5-10 minutes for DNS propagation, then test again:

```bash
php /var/www/mygigguide/simple-test-email.php
```

## Alternative: Use Authenticated SMTP Service

If you want to send emails immediately without setting up DNS records, you can use an external SMTP service:

### Option 1: Gmail SMTP (requires App Password)
Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

### Option 2: SendGrid, Mailgun, or similar service
These services handle authentication and deliverability.

## Testing DNS Records

After adding records, verify with:

```bash
# Check MX record
dig +short mx mygigguide.co.za

# Check SPF record
dig +short txt mygigguide.co.za | grep spf

# Check DKIM record
dig +short txt default._domainkey.mygigguide.co.za

# Check DMARC record
dig +short txt _dmarc.mygigguide.co.za
```

## Current Error from Gmail

The email was rejected with this message:
```
550-5.7.26 Your email has been blocked because the sender is unauthenticated.
550-5.7.26 Gmail requires all senders to authenticate with either SPF or DKIM.
550-5.7.26 Authentication results:
550-5.7.26  DKIM = did not pass
550-5.7.26  SPF [mygigguide.co.za] with ip: [41.61.20.39] = did not pass
```

This will be resolved once SPF and DKIM records are added to DNS.
