# Meta/Facebook App Configuration for MyGigGuide

## Your App Details
- **App ID**: `988462646675374`
- **App Name**: my gig guide
- **Domain**: `mygigguide.co.za`

## Required Configuration Steps

### 1. Basic Settings (Settings → Basic)

Navigate to: https://developers.facebook.com/apps/988462646675374/settings/basic/

#### App Domains
- **Field**: App Domains
- **Value**: `mygigguide.co.za`
- **Note**: Do NOT include `http://` or `https://`, just the domain

#### Website
- **Field**: Website
- **Value**: `https://mygigguide.co.za`
- **Note**: Must include `https://`

#### Privacy Policy URL (Required for OAuth)
- **Field**: Privacy Policy URL
- **Value**: `https://mygigguide.co.za/popia`
- **Note**: This is required for OAuth apps

#### Terms of Service URL
- **Field**: Terms of Service URL  
- **Value**: `https://mygigguide.co.za/terms` (or your terms page)
- **Note**: Recommended for OAuth apps

#### Data Deletion URL (Required for Facebook App Compliance)
- **Field**: Data Deletion URL
- **Value**: `https://www.mygigguide.co.za/auth/facebook/data-deletion`
- **Note**: 
  - This is REQUIRED for Facebook app compliance
  - Facebook will POST to this URL when users request data deletion
  - The endpoint handles deletion of user data and returns a confirmation code
  - Must be accessible via POST request

### 2. Facebook Login Settings (Products → Facebook Login → Settings)

Navigate to: https://developers.facebook.com/apps/988462646675374/use_cases/customize/settings/?product_route=fb-login&use_case_enum=FB_LOGIN&selected_tab=settings

#### Valid OAuth Redirect URIs
- **Field**: Valid OAuth Redirect URIs
- **Value**: `https://mygigguide.co.za/auth/facebook/callback`
- **Important**: 
  - Must match EXACTLY (including `https://`)
  - No trailing slash
  - This is the most critical setting for OAuth to work

#### Client OAuth Settings
- **Use Strict Mode for Redirect URIs**: ✅ Enable (Recommended)
- **Enforce HTTPS**: ✅ Enable (Required for production)

### 3. Permissions

Navigate to: Products → Facebook Login → Permissions

Ensure the following permissions are available:
- ✅ **email** - Required for user registration
- ✅ **public_profile** - Basic profile information

### 4. App Status

#### For Development/Testing:
- App can be in **Development Mode**
- Add test users in **Roles** → **Test Users**
- Only test users can use the app

#### For Production:
- App must be **Live**
- Email permission may require **App Review**
- Submit for review in **App Review** → **Permissions and Features**

## Current .env Configuration

Your `.env` file should have:
```env
FACEBOOK_CLIENT_ID=988462646675374
FACEBOOK_CLIENT_SECRET=your_app_secret_here
FACEBOOK_REDIRECT_URI=https://mygigguide.co.za/auth/facebook/callback
```

**Important**: The App Secret is **REQUIRED** for Facebook OAuth to work. Without it, you'll get "Invalid App ID" errors.

To get your App Secret:
1. See detailed instructions in: `FACEBOOK_APP_SECRET_SETUP.md`
2. Or go to: https://developers.facebook.com/apps/988462646675374/settings/basic/
3. Find "App Secret" and click "Show"
4. Copy the secret and add it to `.env` as `FACEBOOK_CLIENT_SECRET=your_secret_here`

## Quick Checklist

- [ ] App Domains set to: `mygigguide.co.za` (and `www.mygigguide.co.za`)
- [ ] Website set to: `https://mygigguide.co.za` or `https://www.mygigguide.co.za`
- [ ] Privacy Policy URL set (required)
- [ ] Data Deletion URL set to: `https://www.mygigguide.co.za/auth/facebook/data-deletion` (required)
- [ ] Valid OAuth Redirect URI set to: `https://mygigguide.co.za/auth/facebook/callback`
- [ ] App Secret added to `.env` file
- [ ] Email permission enabled
- [ ] App status appropriate (Development or Live)

## Testing

After configuration:
1. Clear Laravel cache: `php artisan config:clear`
2. Test Facebook login on your site
3. Should redirect to Facebook (not show "Invalid App ID" error)

## Troubleshooting

### "Invalid App ID" Error

This is the most common error when Facebook signup/login doesn't work. Here's how to fix it:

**Step 1: Verify App Secret is Set**
- Check your `.env` file for `FACEBOOK_CLIENT_SECRET`
- It should NOT be empty: `FACEBOOK_CLIENT_SECRET=` ❌
- It should have a value: `FACEBOOK_CLIENT_SECRET=abc123...` ✅
- If empty, follow instructions in `FACEBOOK_APP_SECRET_SETUP.md`

**Step 2: Verify App ID is Correct**
- ✅ App ID should be: `988462646675374`
- Check `.env` file: `FACEBOOK_CLIENT_ID=988462646675374`

**Step 3: Clear Configuration Cache**
```bash
php artisan config:clear
```

**Step 4: Verify Facebook App Settings**
- App Domains: `mygigguide.co.za` and `www.mygigguide.co.za`
- Valid OAuth Redirect URI: `https://www.mygigguide.co.za/auth/facebook/callback`
- App Status: Should be "Live" for production or "Development" for testing

**Step 5: Check Laravel Logs**
- Look in `storage/logs/laravel.log` for detailed error messages
- Search for "Facebook OAuth" to find specific errors
- The improved error handling will now show detailed error information

**Common Causes:**
- ❌ Missing or empty `FACEBOOK_CLIENT_SECRET` in `.env` (most common)
- ❌ App Secret copied incorrectly (extra spaces, line breaks)
- ❌ Configuration cache not cleared after adding App Secret
- ❌ Wrong App ID in `.env` file
- ❌ Facebook app not properly configured

### "Invalid OAuth Redirect URI" Error
- ⚠️ Check redirect URI in Facebook matches exactly: `https://www.mygigguide.co.za/auth/facebook/callback`
- ⚠️ No trailing slashes
- ⚠️ Must use `https://` (not `http://`)
- ⚠️ Check both `mygigguide.co.za` and `www.mygigguide.co.za` are in App Domains

### "Facebook login is not properly configured"
- This error appears when `FACEBOOK_CLIENT_ID` or `FACEBOOK_CLIENT_SECRET` is missing
- Add the missing credentials to `.env` file
- See `FACEBOOK_APP_SECRET_SETUP.md` for instructions

### "Facebook authentication failed" or "Unable to connect to Facebook"
- Check Laravel logs for detailed error information
- Verify App Secret is correct (not expired or regenerated)
- Ensure Facebook app is in "Live" mode for production
- Check that email permission is approved (may require App Review)

### "Email permission not granted"
- ⚠️ User's Facebook account must have verified email
- ⚠️ Email permission must be enabled in app settings
- ⚠️ For production, may need App Review approval
- ⚠️ Check Facebook App → Products → Facebook Login → Permissions

### "Facebook authentication session expired"
- User took too long to complete Facebook login
- Try again - this is usually a temporary issue
- If persistent, check session configuration

## Getting Help

If you're still experiencing issues:

1. **Check the logs**: `storage/logs/laravel.log` - look for "Facebook OAuth" entries
2. **Verify configuration**: Run `php artisan config:clear` and test again
3. **Check Facebook App Settings**: Ensure all settings match this guide
4. **Get App Secret**: Follow `FACEBOOK_APP_SECRET_SETUP.md` if App Secret is missing
5. **Test with a test user**: If app is in Development mode, add test users in Facebook App → Roles → Test Users

