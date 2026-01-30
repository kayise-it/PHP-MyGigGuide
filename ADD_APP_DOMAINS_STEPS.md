# How to Add App Domains to Facebook App Settings

## Quick Steps to Fix "Can't load URL" Error

### Step 1: Navigate to Basic Settings
1. Go to: https://developers.facebook.com/apps/988462646675374/settings/basic/
2. Scroll down to find the **"App Domains"** section

### Step 2: Add Your Domains
In the **App Domains** field, add BOTH:
- `mygigguide.co.za`
- `www.mygigguide.co.za`

**Important Notes:**
- Add each domain on a separate line OR separated by commas
- Do NOT include `http://` or `https://`
- Just the domain name: `mygigguide.co.za` and `www.mygigguide.co.za`

### Step 3: Update Website Field
Also check the **Website** field and make sure it's set to:
- `https://www.mygigguide.co.za` (or `https://mygigguide.co.za`)

### Step 4: Save Changes
1. Click the **"Save Changes"** button at the bottom of the page
2. Wait for confirmation that changes are saved

### Step 5: Also Update Website Site URL (if shown)
If there's a **Site URL** field, set it to:
- `https://www.mygigguide.co.za`

## Visual Guide

The App Domains field should look like this:
```
App Domains:
[mygigguide.co.za]
[www.mygigguide.co.za]
```

Or if it's a single text field, enter:
```
mygigguide.co.za, www.mygigguide.co.za
```

## After Saving

1. Clear your browser cache or try in incognito mode
2. Test Facebook login again on your site
3. The error should be resolved

## Alternative: If You Can't Find the Field

If the App Domains field is not visible:
1. Make sure you're on the **Basic** settings tab (not Advanced)
2. Scroll down - it's usually below the App ID and App Secret
3. Look for a section labeled "Basic Settings" or "App Settings"
4. The field might be labeled "App Domains" or "Domain"

## Current Configuration

Your app ID: `988462646675374`
Your domains to add:
- `mygigguide.co.za`
- `www.mygigguide.co.za`




