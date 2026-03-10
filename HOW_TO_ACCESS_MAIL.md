# How to Access Your Mail Client (Roundcube Webmail)

## ✅ Setup Complete!

Roundcube webmail has been configured and is ready to use.

## 🌐 Access Methods

### Option 1: Via Subdomain (Recommended)
**URL**: `http://mail.mygigguide.co.za` (or `https://` once DNS is configured)

**Note**: You need to add a DNS A record first:
- **DNS Record**: `mail.mygigguide.co.za` → `YOUR_SERVER_IP`
- After DNS is set up, run: `sudo certbot --nginx -d mail.mygigguide.co.za` to enable HTTPS

### Option 2: Via IP Address (Temporary)
If DNS isn't set up yet, you can access via IP:
- **URL**: `http://YOUR_SERVER_IP` (using the mail.mygigguide.co.za hostname in your browser)
- Or add to your `/etc/hosts` file: `YOUR_SERVER_IP mail.mygigguide.co.za`

### Option 3: Add to Main Domain (Alternative)
You can also add Roundcube as a subdirectory on your main site at `/webmail`

## 🔐 Login Credentials

Use your email accounts:

### Account 1: dave@mygigguide.co.za
- **Email**: `dave@mygigguide.co.za`
- **Password**: `Dave123!`

### Account 2: noreply@mygigguide.co.za
- **Email**: `noreply@mygigguide.co.za`
- **Password**: `ew&G87bqxu!`

## 📧 Email Client Settings (for Outlook, Thunderbird, etc.)

If you want to use a desktop email client instead of webmail:

### IMAP Settings (Receiving Mail)
- **Server**: `mail.mygigguide.co.za` (or `localhost` if on server)
- **Port**: `143` (IMAP) or `993` (IMAPS/SSL)
- **Security**: STARTTLS (port 143) or SSL/TLS (port 993)
- **Username**: Your full email address (e.g., `dave@mygigguide.co.za`)
- **Password**: Your email password

### SMTP Settings (Sending Mail)
- **Server**: `mail.mygigguide.co.za` (or `localhost` if on server)
- **Port**: `587` (STARTTLS) or `465` (SSL)
- **Security**: STARTTLS (port 587) or SSL/TLS (port 465)
- **Authentication**: Required
- **Username**: Your full email address (e.g., `dave@mygigguide.co.za`)
- **Password**: Your email password

## ⚠️ Important Notes

1. **DNS Setup Required**: For `mail.mygigguide.co.za` to work, add:
   ```
   mail.mygigguide.co.za.  A  YOUR_SERVER_IP
   ```

2. **SSL Certificate**: After DNS is set up, run:
   ```bash
   sudo certbot --nginx -d mail.mygigguide.co.za
   ```

3. **Email Accounts**: Make sure the email accounts are created in the database first:
   ```bash
   sudo /var/www/mygigguide/scripts/setup-mail-database-and-accounts.sh
   ```

4. **Postfix & Dovecot**: These need to be fully configured for email to work. See `/var/www/mygigguide/EMAIL_SETUP_COMPLETE.md`

## 🔧 Troubleshooting

### Can't Access Webmail?
1. Check nginx is running: `sudo systemctl status nginx`
2. Check PHP-FPM is running: `sudo systemctl status php8.2-fpm`
3. Check nginx error logs: `sudo tail -f /var/log/nginx/mail-error.log`

### Can't Login?
1. Verify email accounts exist in database:
   ```bash
   mysql -u root -p mailserver -e "SELECT email FROM virtual_users;"
   ```
2. Check Dovecot is running: `sudo systemctl status dovecot`
3. Check Dovecot logs: `sudo tail -f /var/log/dovecot.log`

### Emails Not Sending/Receiving?
- Postfix and Dovecot need to be fully configured
- Check mail logs: `sudo tail -f /var/log/mail.log`
- Verify DNS MX records are set up

## 📝 Next Steps

1. **Set up DNS**: Add A record for `mail.mygigguide.co.za`
2. **Enable SSL**: Run certbot after DNS is set
3. **Complete Mail Server Config**: Configure Postfix and Dovecot (see EMAIL_SETUP_COMPLETE.md)
4. **Set up DNS Records**: MX, SPF, DKIM, DMARC records for email delivery

## 🎉 You're Ready!

Once DNS is configured, you can access your webmail at:
**https://mail.mygigguide.co.za**

Login with your email address and password!


