# Fix "Auth Failed" Error in RainLoop

## Problem

When sending emails from RainLoop, you get "Authentication failed" error.

## Solution

### Step 1: Verify Your Login Credentials in RainLoop

Make sure you're using the **full email address** and correct password:

**Email:** dave@mygigguide.co.za  
**Password:** Dave123!

**Important:** 
- Use the **full email address** (dave@mygigguide.co.za), not just "dave"
- Password is case-sensitive

### Step 2: Check RainLoop Account Settings

1. **Log into RainLoop:** https://mail.mygigguide.co.za/
2. **Go to Settings** (gear icon)
3. **Check "Accounts" section:**
   - Email: dave@mygigguide.co.za
   - Password: Dave123!
   - Make sure account is properly configured

### Step 3: Verify SMTP Settings

RainLoop should automatically use these settings (already configured):
- **SMTP Server:** localhost
- **SMTP Port:** 587
- **Security:** STARTTLS
- **Authentication:** Required

### Step 4: Test Authentication

The server-side authentication is working. If you still get "Auth failed":

1. **Log out of RainLoop completely**
2. **Clear browser cache/cookies** for mail.mygigguide.co.za
3. **Log back in** with:
   - Email: dave@mygigguide.co.za
   - Password: Dave123!
4. **Try sending an email again**

### Step 5: Check for Account Configuration Issues

If the error persists:

1. **In RainLoop Settings:**
   - Go to "Accounts"
   - Make sure the account shows: dave@mygigguide.co.za
   - Check if there are any error messages

2. **Try removing and re-adding the account:**
   - Remove the account
   - Add it again with full credentials

---

## Server Configuration Status

✅ **SMTP Authentication:** Configured and working  
✅ **Port 587:** Listening and accepting connections  
✅ **STARTTLS:** Enabled  
✅ **SASL Mechanisms:** PLAIN and LOGIN supported  
✅ **Dovecot Auth:** Working  
✅ **User Credentials:** Valid (dave@mygigguide.co.za / Dave123!)

---

## Common Causes of "Auth Failed"

1. **Wrong Password:**
   - Make sure you're using: Dave123!
   - Password is case-sensitive

2. **Wrong Email Format:**
   - Use: dave@mygigguide.co.za
   - Not: dave or dave@mygigguide

3. **Browser Cache:**
   - Clear cache and cookies
   - Try incognito/private mode

4. **Account Not Properly Configured:**
   - Remove and re-add account in RainLoop
   - Make sure account is saved correctly

---

## Verification

### Test from Server (This Works):
```bash
python3 << 'EOF'
import smtplib
server = smtplib.SMTP('localhost', 587)
server.starttls()
server.login('dave@mygigguide.co.za', 'Dave123!')
print("✅ Authentication works from server")
server.quit()
EOF
```

### Test User Credentials:
```bash
doveadm auth test dave@mygigguide.co.za Dave123!
# Should show: auth succeeded
```

---

## Quick Fix Checklist

- [ ] Using full email: dave@mygigguide.co.za
- [ ] Using correct password: Dave123!
- [ ] Cleared browser cache/cookies
- [ ] Logged out and back into RainLoop
- [ ] Checked RainLoop account settings
- [ ] Tried removing and re-adding account

---

## If Still Not Working

1. **Check RainLoop logs:**
   - Look in RainLoop interface for error details
   - Check browser console (F12) for JavaScript errors

2. **Verify credentials:**
   ```bash
   doveadm auth test dave@mygigguide.co.za Dave123!
   ```

3. **Check mail logs:**
   ```bash
   tail -f /var/log/mail.log | grep -i "auth\|sasl"
   ```

---

**Last Updated:** 2026-01-20  
**Status:** Server authentication is working. Issue is likely in RainLoop client configuration.
