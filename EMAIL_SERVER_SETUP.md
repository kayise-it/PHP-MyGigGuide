# Email Server Setup - Complete Guide

## Current Status

✅ **Installed:**
- Dovecot (IMAP/POP3 server) - Running
- Roundcube Webmail - Installed at `/usr/share/roundcube`
- MySQL support packages

⚠️ **Needs Configuration:**
- Postfix (SMTP server)
- Database setup
- Nginx configuration for webmail
- Email account creation

## Quick Setup Instructions

### Step 1: Complete Postfix Installation

```bash
DEBIAN_FRONTEND=noninteractive apt install -y postfix postfix-mysql
# When prompted, select "Internet Site" and enter: mygigguide.co.za
```

### Step 2: Set Up Mail Database

Run the configuration script:
```bash
sudo /var/www/mygigguide/scripts/configure-email-server.sh
```

Or manually:
```bash
mysql -u root -p
```

Then execute:
```sql
CREATE DATABASE mailserver;
CREATE USER 'mailuser'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON mailserver.* TO 'mailuser'@'localhost';
FLUSH PRIVILEGES;

USE mailserver;

CREATE TABLE virtual_domains (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE virtual_users (
  id INT NOT NULL AUTO_INCREMENT,
  domain_id INT NOT NULL,
  password VARCHAR(106) NOT NULL,
  email VARCHAR(120) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  FOREIGN KEY (domain_id) REFERENCES virtual_domains(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE virtual_aliases (
  id INT NOT NULL AUTO_INCREMENT,
  domain_id INT NOT NULL,
  source VARCHAR(100) NOT NULL,
  destination VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (domain_id) REFERENCES virtual_domains(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO virtual_domains (name) VALUES ('mygigguide.co.za');
```

### Step 3: Create Your First Email Account

Generate a password hash:
```bash
doveadm pw -s SHA512-CRYPT
# Enter your desired password when prompted
# Copy the output (starts with {SHA512-CRYPT}$6$...)
```

Create the email account:
```bash
mysql -u root -p mailserver
```

```sql
INSERT INTO virtual_users (domain_id, password, email) 
VALUES (1, '{SHA512-CRYPT}$6$YOUR_HASH_HERE', 'admin@mygigguide.co.za');
```

### Step 4: Configure Postfix

The Postfix configuration files need to be created. See detailed instructions below.

### Step 5: Configure Dovecot

Dovecot configuration needs to be updated to use MySQL authentication.

### Step 6: Configure Roundcube

Update `/etc/roundcube/config.inc.php` or create it from the template.

### Step 7: Set Up Nginx for Webmail

Create nginx configuration to serve Roundcube at `mail.mygigguide.co.za` or as a subdirectory.

## Access Information

Once configured:
- **Webmail URL**: https://mail.mygigguide.co.za (or https://mygigguide.co.za/webmail)
- **SMTP Server**: mail.mygigguide.co.za
- **SMTP Port**: 587 (TLS) or 465 (SSL)
- **IMAP Server**: mail.mygigguide.co.za
- **IMAP Port**: 993 (SSL)
- **POP3 Server**: mail.mygigguide.co.za  
- **POP3 Port**: 995 (SSL)

## DNS Requirements

Make sure these DNS records are set:
- **MX Record**: `mygigguide.co.za. MX 10 mail.mygigguide.co.za.`
- **A Record**: `mail.mygigguide.co.za. A YOUR_SERVER_IP`
- **SPF Record**: `mygigguide.co.za. TXT "v=spf1 mx a ~all"`
- **DKIM**: Set up DKIM signing (optional but recommended)
- **DMARC**: `_dmarc.mygigguide.co.za. TXT "v=DMARC1; p=none;"`

## Troubleshooting

### Check Services
```bash
systemctl status postfix
systemctl status dovecot
systemctl status nginx
```

### View Logs
```bash
tail -f /var/log/mail.log
tail -f /var/log/dovecot.log
```

### Test Email
```bash
# Send test email
echo "Test email" | mail -s "Test" your-email@example.com
```

## Alternative: Third-Party Email Services

If server setup is too complex, consider using:
- **SendGrid** - Free tier: 100 emails/day
- **Mailgun** - Free tier: 5,000 emails/month
- **Amazon SES** - Pay per email (very cheap)
- **Postmark** - Great for transactional emails

These integrate easily with Laravel and don't require server configuration.

