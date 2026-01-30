# How to Get Your Facebook App Secret

This guide will help you retrieve your Facebook App Secret and add it to your `.env` file to enable Facebook signup/login.

## Your Facebook App Details
- **App ID**: `988462646675374`
- **App Name**: my gig guide
- **Settings URL**: https://developers.facebook.com/apps/988462646675374/settings/basic/

## Step-by-Step Instructions

### Step 1: Navigate to Facebook App Settings

1. Go to: https://developers.facebook.com/apps/988462646675374/settings/basic/
2. Make sure you're logged into Facebook with an account that has access to this app
3. If you see a login page, log in with your Facebook account

### Step 2: Find the App Secret

1. On the Basic Settings page, scroll down to find the **"App Secret"** section
2. You'll see a field labeled **"App Secret"** with a hidden value (shown as dots or asterisks)
3. Next to the App Secret field, you'll see a button that says **"Show"** or an eye icon 👁️
4. Click the **"Show"** button
5. Facebook may ask you to:
   - Enter your Facebook password for security
   - Complete a security check (CAPTCHA)
6. After verification, the App Secret will be revealed

### Step 3: Copy the App Secret

1. Click on the revealed App Secret to select it
2. Copy the entire App Secret (it's a long string of letters and numbers)
3. **Important**: Keep this secret secure - don't share it publicly or commit it to version control

### Step 4: Add App Secret to .env File

1. Open your `.env` file in the project root: `/var/www/mygigguide/.env`
2. Find the line that says:
   ```
   FACEBOOK_CLIENT_SECRET=
   ```
3. Add your App Secret after the equals sign:
   ```
   FACEBOOK_CLIENT_SECRET=your_app_secret_here
   ```
4. Replace `your_app_secret_here` with the actual App Secret you copied
5. Make sure there are no spaces around the equals sign
6. Save the file

### Step 5: Clear Configuration Cache

After adding the App Secret, clear Laravel's configuration cache:

```bash
cd /var/www/mygigguide
php artisan config:clear
```

### Step 6: Verify Configuration

1. Check that the App Secret was saved correctly:
   ```bash
   grep FACEBOOK_CLIENT_SECRET .env
   ```
   You should see your App Secret (not empty)

2. Test Facebook login on your site
3. It should now redirect to Facebook instead of showing an error

## Example .env Configuration

Your `.env` file should have these Facebook-related variables:

```env
FACEBOOK_CLIENT_ID=988462646675374
FACEBOOK_CLIENT_SECRET=abc123def456ghi789jkl012mno345pq
FACEBOOK_REDIRECT_URI=https://www.mygigguide.co.za/auth/facebook/callback
```

## Troubleshooting

### "Show" Button Not Visible
- Make sure you're logged in with an account that has admin/developer access to the app
- Try refreshing the page
- Check if you're on the correct app (App ID: 988462646675374)

### App Secret Not Working
- Verify you copied the entire secret (no spaces, no line breaks)
- Make sure you saved the `.env` file
- Run `php artisan config:clear` to clear cache
- Check for typos in the `.env` file

### Still Getting "Invalid App ID" Error
- Verify both `FACEBOOK_CLIENT_ID` and `FACEBOOK_CLIENT_SECRET` are set
- Check that the App Secret matches what's shown in Facebook
- Ensure the redirect URI in Facebook matches: `https://www.mygigguide.co.za/auth/facebook/callback`
- Clear config cache: `php artisan config:clear`

### Can't Access Facebook App Settings
- You need to be an admin or developer of the Facebook app
- Contact the app owner to grant you access
- Or create a new Facebook app if you don't have access

## Security Notes

⚠️ **Important Security Reminders:**
- Never commit the `.env` file to version control (it should be in `.gitignore`)
- Never share your App Secret publicly
- Don't paste the App Secret in chat, email, or documentation
- If the App Secret is compromised, regenerate it in Facebook App Settings

## Need Help?

If you're still having issues:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Look for Facebook OAuth errors in the logs
3. Verify all Facebook app settings match the configuration in `META_APP_CONFIGURATION.md`




