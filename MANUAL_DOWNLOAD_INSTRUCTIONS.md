# Manual Instructions to Download Venue Images

Since the SSH connection requires authentication from your local machine, here are the steps to download the venue images:

## Option 1: Using the Script (Recommended)

1. **On your LOCAL machine** (not the VPS), copy the script:
   ```bash
   # Copy the script to your local machine first
   scp /var/www/mygigguide/download_venue_images.sh ~/
   ```

2. **Edit the script** to update the LOCAL_DEST path if needed

3. **Run the script from your local machine:**
   ```bash
   chmod +x ~/download_venue_images.sh
   ~/download_venue_images.sh
   ```

## Option 2: Manual Download Steps

### Step 1: Find the storage path on remote server

Connect to your remote server:
```bash
ssh pbxbxpt4fzn9@92.205.15.114
```

Then find the storage directory:
```bash
find ~ -type d -path '*storage/app/public' | head -1
# OR
ls -la ~/public_html/storage/app/public
# OR  
ls -la ~/domains/*/public_html/storage/app/public
```

### Step 2: Download the images to your local machine first

From your **LOCAL machine**, download the folders:
```bash
REMOTE_USER="pbxbxpt4fzn9"
REMOTE_HOST="92.205.15.114"
REMOTE_STORAGE="~/domains/yoursite.com/public_html/storage/app/public"  # Update this path!

# Download users folder
rsync -avz --progress ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_STORAGE}/users/ ~/venue_images_backup/users/

# Download organisers folder  
rsync -avz --progress ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_STORAGE}/organisers/ ~/venue_images_backup/organisers/

# Download venues folder
rsync -avz --progress ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_STORAGE}/venues/ ~/venue_images_backup/venues/
```

### Step 3: Upload to VPS

From your **LOCAL machine**, upload to VPS:
```bash
VPS_USER="your_vps_user"  # Update this
VPS_HOST="your_vps_ip"     # Update this

rsync -avz --progress ~/venue_images_backup/users/ ${VPS_USER}@${VPS_HOST}:/var/www/mygigguide/storage/app/public/users/
rsync -avz --progress ~/venue_images_backup/organisers/ ${VPS_USER}@${VPS_HOST}:/var/www/mygigguide/storage/app/public/organisers/
rsync -avz --progress ~/venue_images_backup/venues/ ${VPS_USER}@${VPS_HOST}:/var/www/mygigguide/storage/app/public/venues/
```

## Option 3: Direct Transfer (if you have access to both)

If you can access both servers from the same machine:

```bash
# Direct transfer from remote to VPS
rsync -avz --progress \
  pbxbxpt4fzn9@92.205.15.114:~/domains/yoursite.com/public_html/storage/app/public/users/ \
  your_vps_user@your_vps_ip:/var/www/mygigguide/storage/app/public/users/
```

## Verify After Download

After downloading, verify the images are in place:

```bash
# On the VPS
cd /var/www/mygigguide
ls -la storage/app/public/
find storage/app/public -type f -name "*.jpg" | wc -l  # Count images
```

## Set Correct Permissions

After uploading, fix permissions:

```bash
sudo chown -R www-data:www-data /var/www/mygigguide/storage/app/public
sudo chmod -R 755 /var/www/mygigguide/storage/app/public
```

