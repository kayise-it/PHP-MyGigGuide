#!/bin/bash

# Script to find the storage path on remote server
# Run this from your LOCAL machine

REMOTE_HOST="92.205.15.114"
REMOTE_USER="pbxbxpt4fzn9"

echo "Searching for storage directory on remote server..."
echo ""

# Try to connect and find the path
ssh ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
echo "Current directory: $(pwd)"
echo ""
echo "Looking for mygigguide storage directories..."
echo ""

# Common locations to check
LOCATIONS=(
    "~/public_html/storage/app/public"
    "~/domains/*/public_html/storage/app/public"
    "~/storage/app/public"
    "~/mygigguide/storage/app/public"
    "~/*/storage/app/public"
)

for loc in "${LOCATIONS[@]}"; do
    expanded=$(eval echo $loc)
    if [ -d "$expanded" ]; then
        echo "✓ FOUND: $expanded"
        echo "   Contents:"
        ls -la "$expanded" | head -10
        echo ""
    fi
done

echo ""
echo "Searching entire home directory..."
find ~ -type d -path "*/storage/app/public" 2>/dev/null | head -10

echo ""
echo "Searching for venue image folders..."
find ~ -type d -name "venues" -path "*/storage/app/public/*" 2>/dev/null | head -10
EOF

echo ""
echo "=========================================="
echo "If you found the path above, you can now"
echo "use it in the download script."
echo "=========================================="

