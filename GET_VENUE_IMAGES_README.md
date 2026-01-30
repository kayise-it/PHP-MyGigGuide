# Download Venue Images from Remote Server

## Current Situation

✅ **Database**: Has all venue image paths stored
❌ **Server**: Missing all venue image files
✅ **Storage Link**: Properly configured (`public/storage` → `storage/app/public`)

## What You Need to Do

Download the venue images from your remote server (92.205.15.114) to this VPS.

## Quick Start

### Step 1: Find the Storage Path on Remote Server

From your **LOCAL machine** (with SSH access), run:

```bash
# Copy this script to your local machine first, then run:
scp /var/www/mygigguide/scripts/find_remote_storage_path.sh ~/
chmod +x ~/find_remote_storage_path.sh
~/find_remote_storage_path.sh
```

This will show you where the images are stored on the remote server.

### Step 2: Download the Images

Once you know the remote path, download using one of these methods:

#### Method A: Direct rsync (if you can access both servers)

```bash
# From your LOCAL machine
REMOTE_USER="pbxbxpt4fzn9"
REMOTE_HOST="92.205.15.114"
REMOTE_PATH="~/domains/yoursite.com/public_html/storage/app/public"  # Update this!
VPS_USER="your_vps_user"  # Update this
VPS_IP="your_vps_ip"      # Update this

# Download users folder
rsync -avz --progress \
  ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/users/ \
  ${VPS_USER}@${VPS_IP}:/var/www/mygigguide/storage/app/public/users/

# Download organisers folder
rsync -avz --progress \
  ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/organisers/ \
  ${VPS_USER}@${VPS_IP}:/var/www/mygigguide/storage/app/public/organisers/

# Download venues folder (if it exists separately)
rsync -avz --progress \
  ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/venues/ \
  ${VPS_USER}@${VPS_IP}:/var/www/mygigguide/storage/app/public/venues/
```

#### Method B: Download to Local, Then Upload

```bash
# Step 1: Download to your local machine
mkdir -p ~/venue_images_backup
rsync -avz --progress \
  pbxbxpt4fzn9@92.205.15.114:~/path/to/storage/app/public/users/ \
  ~/venue_images_backup/users/

rsync -avz --progress \
  pbxbxpt4fzn9@92.205.15.114:~/path/to/storage/app/public/organisers/ \
  ~/venue_images_backup/organisers/

# Step 2: Upload to VPS
rsync -avz --progress \
  ~/venue_images_backup/users/ \
  your_vps_user@your_vps_ip:/var/www/mygigguide/storage/app/public/users/
```

### Step 3: Fix Permissions

After uploading, fix permissions on the VPS:

```bash
sudo chown -R www-data:www-data /var/www/mygigguide/storage/app/public
sudo chmod -R 755 /var/www/mygigguide/storage/app/public
```

### Step 4: Verify

Check that images are in place:

```bash
# Count image files
find /var/www/mygigguide/storage/app/public -type f -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" | wc -l

# List directories
ls -la /var/www/mygigguide/storage/app/public/
```

## Folder Structure Expected

Based on the database, images should be in:

- `storage/app/public/users/{user_folder}/venues/{venue_folder}/images/`
- `storage/app/public/organisers/{org_folder}/venues/{venue_folder}/images/`
- `storage/app/public/artists/{artist_folder}/venues/{venue_folder}/images/`
- `storage/app/public/venues/gallery/` (some older paths)

## Database Image Path Examples

From the database, here are examples of paths stored:

```
users/usa_dave_4014/venues/venue_8530_hogshead_douglasdale_2025-10-09/images/gtnCnUTUHfBsvhGDRulosdmu5tmJk3W4y8MTjO2P.jpg
organisers/org_biggy_1673/venues/venue_3796_lewis_witt_2025-09-28/images/zqH7wKKXIxlsC4MqYXKK4JSnx1HuMRlpw25w8owr.jpg
venues/gallery/xnokSBplzVbSxkOMtjJDYxlKP8bHrE4rF5OKhuOR.jpg
```

These paths are relative to `storage/app/public/`, so the full server path would be:
`/var/www/mygigguide/storage/app/public/users/...`

## Troubleshooting

### If rsync is not available:

Use `scp` instead:

```bash
scp -r pbxbxpt4fzn9@92.205.15.114:~/path/to/storage/app/public/users /tmp/
# Then move to final location on VPS
```

### If connection fails:

Make sure:
1. Your SSH key is added to the remote server
2. The remote path is correct
3. You have read permissions on remote server
4. You have write permissions on VPS

## Files Created for You

1. **`download_venue_images.sh`** - Automated download script (run from local machine)
2. **`scripts/find_remote_storage_path.sh`** - Helper to find remote storage location
3. **`MANUAL_DOWNLOAD_INSTRUCTIONS.md`** - Detailed manual instructions
4. **`scripts/check_missing_images.php`** - PHP script to verify which images are missing

## Need Help?

If you can provide:
1. The exact path to storage on remote server
2. SSH access details for both servers

I can help create a more specific download command for your setup.

