# Facebook OAuth Registration Setup Guide

This guide will help you verify that Facebook registration is properly configured for MyGigGuide.

## 1. Environment Variables

Ensure your `.env` file contains the following Facebook OAuth credentials:

```env
FACEBOOK_CLIENT_ID=your_facebook_app_id
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
FACEBOOK_REDIRECT_URI=https://yourdomain.com/auth/facebook/callback
```

**Note:** If `FACEBOOK_REDIRECT_URI` is not set, it will default to `{APP_URL}/auth/facebook/callback`

## 2. Facebook App Settings

### A. Create/Verify Facebook App

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Navigate to **My Apps** → Select your app (or create a new one)
3. Go to **Settings** → **Basic**

### B. Required Settings

#### App Domains
- Add your domain (e.g., `mygigguide.co.za` or `www.mygigguide.co.za`)
- Do NOT include `http://` or `https://`

#### Website
- Add your site URL: `https://yourdomain.com`

#### Privacy Policy URL
- Required for OAuth apps
- Add your privacy policy URL

#### Terms of Service URL
- Add your terms of service URL

### C. OAuth Redirect URIs

1. Go to **Products** → **Facebook Login** → **Settings**
2. Under **Valid OAuth Redirect URIs**, add:
   ```
   https://yourdomain.com/auth/facebook/callback
   ```
   - Replace `yourdomain.com` with your actual domain
   - Must match exactly what's in your `.env` file
   - Must use `https://` (not `http://`)

### D. App Permissions

1. Go to **Products** → **Facebook Login** → **Settings**
2. Under **Permissions and Features**, ensure:
   - ✅ **email** permission is enabled
   - This is required for user registration

### E. App Status

1. Go to **App Review** → **Permissions and Features**
2. For development/testing:
   - App can be in **Development Mode**
   - Add test users in **Roles** → **Test Users**
3. For production:
   - App must be **Live**
   - Email permission must be approved (if required by Facebook)

## 3. Verify Configuration

### Check Laravel Configuration

The Facebook OAuth is configured in `config/services.php`:

```php
'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URI', env('APP_URL') . '/auth/facebook/callback'),
],
```

### Test the Flow

1. Visit your registration page
2. Click "Continue with Facebook"
3. You should be redirected to Facebook login
4. After authorizing, you should be redirected back to your site

## 4. Common Issues

### Issue: "Invalid OAuth Redirect URI"
**Solution:** 
- Ensure the redirect URI in Facebook App settings matches exactly with your `.env` file
- Check for trailing slashes
- Ensure you're using `https://` (not `http://`)

### Issue: "Email permission not granted"
**Solution:**
- Ensure `email` scope is requested (already configured in `AuthController.php`)
- User must have a verified email on their Facebook account
- Email permission must be approved in Facebook App Review (for production)

### Issue: "App not available"
**Solution:**
- If app is in Development Mode, only test users can use it
- Add yourself as a test user in Facebook App settings
- Or make the app Live (requires App Review for some permissions)

### Issue: "Can't retrieve email from Facebook"
**Solution:**
- User's Facebook account must have a verified email
- Email permission must be granted
- Check Facebook App permissions in App Review

## 5. Testing Checklist

- [ ] Facebook App ID and Secret are set in `.env`
- [ ] Redirect URI is correctly configured in Facebook App
- [ ] Redirect URI matches exactly in both Facebook and `.env`
- [ ] App Domain is set in Facebook App settings
- [ ] Email permission is enabled
- [ ] App is either in Development Mode (with test users) or Live
- [ ] Privacy Policy and Terms URLs are set (required for OAuth)
- [ ] Test the registration flow end-to-end

## 6. Current Implementation

The Facebook registration is implemented in:
- **Controller:** `app/Http/Controllers/AuthController.php`
  - `redirectToFacebook()` - Redirects to Facebook OAuth
  - `handleFacebookCallback()` - Handles the callback and creates user
- **Routes:** `routes/web.php`
  - `/auth/facebook` - OAuth redirect
  - `/auth/facebook/callback` - OAuth callback
- **Views:** 
  - `resources/views/auth/register.blade.php` - Full registration page
  - `resources/views/components/auth-modal.blade.php` - Modal registration (now includes Facebook button)

## 7. Features

✅ Automatic user creation from Facebook profile
✅ Email verification (Facebook emails are auto-verified)
✅ Profile picture import from Facebook
✅ Username generation from email
✅ Role assignment (user, artist, organiser, venue_owner)
✅ Artist profile claiming (if email matches unclaimed artist)
✅ Continue URL support (redirects back to original page after registration)




