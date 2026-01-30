# Quick Facebook Setup - Fix "Invalid App ID" Error

## The Problem
You're seeing "Invalid App ID" because the Facebook credentials are not set in your `.env` file.

## Quick Fix Steps

### Step 1: Get Your Facebook App ID and Secret

1. **Go to Facebook Developers**: https://developers.facebook.com/
2. **Login** with your Facebook account
3. **Click "My Apps"** → Select your app (or **Create App** if you don't have one)
4. **Go to Settings → Basic**
5. You'll see:
   - **App ID** - Copy this
   - **App Secret** - Click "Show" and copy this

### Step 2: Add to .env File

Add these lines to your `/var/www/mygigguide/.env` file:

```env
FACEBOOK_CLIENT_ID=your_app_id_here
FACEBOOK_CLIENT_SECRET=your_app_secret_here
```

**Example:**
```env
FACEBOOK_CLIENT_ID=1234567890123456
FACEBOOK_CLIENT_SECRET=abc123def456ghi789jkl012mno345pq
```

### Step 3: Set Redirect URI in Facebook App

1. In Facebook App → **Products** → **Facebook Login** → **Settings**
2. Under **Valid OAuth Redirect URIs**, add:
   ```
   https://mygigguide.co.za/auth/facebook/callback
   ```
3. Click **Save Changes**

### Step 4: Clear Laravel Cache

After adding the credentials, run:
```bash
php artisan config:clear
php artisan cache:clear
```

## If You Don't Have a Facebook App Yet

### Create New Facebook App:

1. Go to https://developers.facebook.com/apps/
2. Click **"Create App"**
3. Select **"Consumer"** as app type
4. Fill in:
   - **App Name**: My Gig Guide (or your preferred name)
   - **App Contact Email**: your-email@example.com
5. Click **Create App**

### Configure the App:

1. **Settings → Basic**:
   - Add **App Domains**: `mygigguide.co.za`
   - Add **Website**: `https://mygigguide.co.za`
   - Add **Privacy Policy URL**: `https://mygigguide.co.za/popia` (or your privacy policy)
   - Add **Terms of Service URL**: `https://mygigguide.co.za/terms` (or your terms)

2. **Products → Facebook Login → Settings**:
   - Add **Valid OAuth Redirect URIs**: `https://mygigguide.co.za/auth/facebook/callback`
   - Enable **"Use Strict Mode for Redirect URIs"** (recommended)

3. **App Review** (for production):
   - For testing: App can stay in **Development Mode**
   - For production: Submit for review to make app **Live**

## Test It

1. Add credentials to `.env`
2. Clear cache: `php artisan config:clear`
3. Try Facebook login again
4. Should redirect to Facebook (not show "Invalid App ID" error)

## Current Configuration

Your app URL is: `https://mygigguide.co.za`
Your callback URL should be: `https://mygigguide.co.za/auth/facebook/callback`

Make sure these match exactly in both your `.env` and Facebook App settings!




