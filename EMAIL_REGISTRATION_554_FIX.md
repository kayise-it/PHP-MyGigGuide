# Fix: 554 Recipient address rejected: Access denied

## What was fixed (code)

Registration no longer returns HTTP 500 when the mail server rejects the verification email. The app now:

- Completes registration and creates the account
- Shows a warning that the verification email could not be sent
- Lets the user request a new verification email from the verification notice page

## Root cause (mail config)

The 554 "Recipient address rejected: Access denied" means the SMTP server is rejecting the recipient. Common causes:

1. **Missing SMTP auth** - Port 587 requires SASL authentication.
2. **Using port 25 without relay permission** - Your server may block relay to external domains.

## How to fix the mail server config

Ensure `.env` uses authenticated SMTP on the submission port:

```
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=587
MAIL_USERNAME=noreply@mygigguide.co.za
MAIL_PASSWORD=your-noreply-account-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@mygigguide.co.za"
MAIL_FROM_NAME="My Gig Guide"
```

- MAIL_PORT=587 uses the submission port (requires auth)
- MAIL_USERNAME and MAIL_PASSWORD must be set for relay
- Password: see EMAIL_SETUP_COMPLETE.md or your mail accounts config

After editing .env: php artisan config:clear && php artisan config:cache
