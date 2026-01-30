#!/bin/bash

# Script to download venue images from remote server
# Run this from your LOCAL machine (not on the VPS)

REMOTE_HOST="92.205.15.114"
REMOTE_USER="pbxbxpt4fzn9"
LOCAL_DEST="/var/www/mygigguide/storage/app/public"

echo "=========================================="
echo "Venue Images Download Script"
echo "=========================================="
echo ""
echo "This script will download venue images from the remote server."
echo "Make sure you're running this from your LOCAL machine."
echo ""

# Find storage directory on remote server
echo "Step 1: Finding storage directory on remote server..."
REMOTE_STORAGE=$(ssh ${REMOTE_USER}@${REMOTE_HOST} "find ~ -type d -path '*mygigguide*storage/app/public' -o -path '*storage/app/public' 2>/dev/null | head -1")

if [ -z "$REMOTE_STORAGE" ]; then
    echo "ERROR: Could not find storage directory on remote server."
    echo "Trying alternative locations..."
    
    # Try common locations
    REMOTE_STORAGE=$(ssh ${REMOTE_USER}@${REMOTE_HOST} "ls -d ~/public_html/storage/app/public ~/domains/*/public_html/storage/app/public ~/mygigguide/storage/app/public 2>/dev/null | head -1")
    
    if [ -z "$REMOTE_STORAGE" ]; then
        echo ""
        echo "Please manually find the storage path on remote server:"
        echo "  ssh ${REMOTE_USER}@${REMOTE_HOST}"
        echo "  find ~ -type d -name 'storage' | grep public"
        exit 1
    fi
fi

echo "Found remote storage at: $REMOTE_STORAGE"
echo ""

# List what we'll download
echo "Step 2: Checking what folders exist on remote server..."
ssh ${REMOTE_USER}@${REMOTE_HOST} "ls -la $REMOTE_STORAGE/ 2>/dev/null | head -20"
echo ""

# Create local directories
echo "Step 3: Creating local directories..."
mkdir -p "$LOCAL_DEST/users"
mkdir -p "$LOCAL_DEST/organisers"
mkdir -p "$LOCAL_DEST/artists"
mkdir -p "$LOCAL_DEST/venues"
echo "Created local directories"
echo ""

# Download images
echo "Step 4: Downloading images..."
echo "This may take a while depending on the number of images..."

# Download users folder
if ssh ${REMOTE_USER}@${REMOTE_HOST} "test -d $REMOTE_STORAGE/users"; then
    echo "Downloading users/ folder..."
    rsync -avz --progress ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_STORAGE}/users/ "$LOCAL_DEST/users/"
else
    echo "No users/ folder found on remote"
fi

# Download organisers folder
if ssh ${REMOTE_USER}@${REMOTE_HOST} "test -d $REMOTE_STORAGE/organisers"; then
    echo "Downloading organisers/ folder..."
    rsync -avz --progress ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_STORAGE}/organisers/ "$LOCAL_DEST/organisers/"
else
    echo "No organisers/ folder found on remote"
fi

# Download venues folder
if ssh ${REMOTE_USER}@${REMOTE_HOST} "test -d $REMOTE_STORAGE/venues"; then
    echo "Downloading venues/ folder..."
    rsync -avz --progress ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_STORAGE}/venues/ "$LOCAL_DEST/venues/"
else
    echo "No venues/ folder found on remote"
fi

# Download artists folder (if it has venue images)
if ssh ${REMOTE_USER}@${REMOTE_HOST} "test -d $REMOTE_STORAGE/artists"; then
    echo "Downloading artists/ folder..."
    rsync -avz --progress ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_STORAGE}/artists/ "$LOCAL_DEST/artists/"
else
    echo "No artists/ folder found on remote"
fi

echo ""
echo "=========================================="
echo "Download complete!"
echo "=========================================="
echo ""
echo "Images downloaded to: $LOCAL_DEST"
echo ""
echo "Verifying download..."
echo "Users folders: $(find $LOCAL_DEST/users -type d 2>/dev/null | wc -l)"
echo "Organisers folders: $(find $LOCAL_DEST/organisers -type d 2>/dev/null | wc -l)"
echo "Venues folders: $(find $LOCAL_DEST/venues -type d 2>/dev/null | wc -l)"
echo "Image files: $(find $LOCAL_DEST -type f \( -name '*.jpg' -o -name '*.jpeg' -o -name '*.png' \) 2>/dev/null | wc -l)"

