# Email Setup Guide for MyGigGuide

## Current Status

✅ **What's Working:**
- Roundcube webmail is installed and configured
- Dovecot (IMAP/POP3) is running - can receive emails
- Nginx is configured for `mail.mygigguide.co.za`
- Email accounts are defined in scripts

❌ **What Needs Setup:**
- Postfix (SMTP) is not installed - **cannot send emails yet**
- Mail database needs to be created
- DNS records (MX, A record) need to be configured
- Laravel mail configuration needs SMTP settings

## Step-by-Step Setup

### 1. Install Postfix (SMTP Server)

```bash
sudo apt-get update
sudo apt-get install -y postfix postfix-mysql
```

During installation, select:
- **Internet Site**
- **System mail name**: `mygigguide.co.za`

### 2. Set Up Mail Database and Accounts

```bash
sudo /var/www/mygigguide/scripts/setup-mail-database-and-accounts.sh
```

This will create:
- `mailserver` database
- Email accounts:
  - `dave@mygigguide.co.za` (Password: `Dave123!`)
  - `noreply@mygigguide.co.za` (Password: `ew&G87bqxu!`)

### 3. Configure Postfix

Run the configuration script:
```bash
sudo /var/www/mygigguide/scripts/configure-email-server.sh
```

Or manually configure Postfix to use MySQL authentication (see Postfix configuration files).

### 4. Configure DNS Records

Add these DNS records at your domain registrar:

#### A Record (for webmail access):
```
mail.mygigguide.co.za.  A  YOUR_SERVER_IP
```

#### MX Record (for receiving emails):
```
mygigguide.co.za.  MX  10  mail.mygigguide.co.za.
```

#### SPF Record (prevents spam):
```
mygigguide.co.za.  TXT  "v=spf1 mx a:mail.mygigguide.co.za ~all"
```

#### DKIM Record (email authentication):
After generating DKIM keys, add:
```
default._domainkey.mygigguide.co.za.  TXT  "v=DKIM1; k=rsa; p=YOUR_PUBLIC_KEY"
```

#### DMARC Record (email policy):
```
_dmarc.mygigguide.co.za.  TXT  "v=DMARC1; p=quarantine; rua=mailto:admin@mygigguide.co.za"
```

### 5. Update Laravel Mail Configuration

Edit `/var/www/mygigguide/.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=587
MAIL_USERNAME=noreply@mygigguide.co.za
MAIL_PASSWORD=ew&G87bqxu!
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@mygigguide.co.za"
MAIL_FROM_NAME="My Gig Guide"
```

Then clear Laravel cache:
```bash
cd /var/www/mygigguide
php artisan config:cache
```

### 6. Enable SSL for Webmail

After DNS is configured:
```bash
sudo certbot --nginx -d mail.mygigguide.co.za
```

### 7. Test Email Functionality

#### Test sending from Laravel:
```bash
cd /var/www/mygigguide
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('dave@mygigguide.co.za')->subject('Test'); });
```

#### Test receiving via Roundcube:
1. Visit: `https://mail.mygigguide.co.za` (after DNS/SSL setup)
2. Login with: `dave@mygigguide.co.za` / `Dave123!`
3. Check if you can see received emails

## Accessing Webmail (Roundcube)

**Note:** You mentioned "HORD" - the system uses **Roundcube**, not Horde. Both are webmail clients, but Roundcube is what's installed.

### Access URLs:
- **Via subdomain**: `https://mail.mygigguide.co.za` (after DNS setup)
- **Via IP** (temporary): Add to `/etc/hosts`: `YOUR_SERVER_IP mail.mygigguide.co.za`

### Login Credentials:
- **Email**: `dave@mygigguide.co.za`
- **Password**: `Dave123!`

Or:
- **Email**: `noreply@mygigguide.co.za`
- **Password**: `ew&G87bqxu!`

## Email Client Settings (Outlook, Thunderbird, etc.)

### IMAP (Receiving):
- **Server**: `mail.mygigguide.co.za` (or `localhost` if on server)
- **Port**: `143` (STARTTLS) or `993` (SSL/TLS)
- **Username**: Full email address (e.g., `dave@mygigguide.co.za`)
- **Password**: Your email password

### SMTP (Sending):
- **Server**: `mail.mygigguide.co.za` (or `localhost` if on server)
- **Port**: `587` (STARTTLS) or `465` (SSL)
- **Authentication**: Required
- **Username**: Full email address
- **Password**: Your email password

## Troubleshooting

### Can't send emails?
1. Check Postfix is running: `sudo systemctl status postfix`
2. Check mail logs: `sudo tail -f /var/log/mail.log`
3. Verify DNS MX records are set up
4. Check firewall allows ports 25, 587, 465

### Can't receive emails?
1. Check Dovecot is running: `sudo systemctl status dovecot`
2. Verify DNS MX records point to your server
3. Check mail logs: `sudo tail -f /var/log/mail.log`
4. Verify email accounts exist: `mysql -u root -p mailserver -e "SELECT email FROM virtual_users;"`

### Can't access webmail?
1. Check nginx: `sudo systemctl status nginx`
2. Check PHP-FPM: `sudo systemctl status php8.2-fpm`
3. Check nginx error logs: `sudo tail -f /var/log/nginx/mail-error.log`
4. Verify DNS A record for `mail.mygigguide.co.za`

## Summary

**To answer your question: "Will emails work? Will I be able to send and receive emails using Horde/Roundcube?"**

**Current Status:**
- ✅ **Receiving**: Will work once Postfix is configured and DNS MX records are set
- ❌ **Sending**: Will NOT work until Postfix is installed and configured
- ✅ **Webmail Access**: Roundcube is ready, but needs DNS/SSL setup

**After completing the steps above:**
- ✅ You'll be able to **send** emails from Laravel and email clients
- ✅ You'll be able to **receive** emails via IMAP/POP3
- ✅ You'll be able to access **Roundcube webmail** (not Horde, but similar functionality)

The system uses **Roundcube**, not Horde, but both provide webmail functionality for sending and receiving emails.
