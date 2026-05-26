# Deploying to the VPS (`/var/www/mygigguide`)

Baby-step guide for updating production when `git pull` does not work yet.

---

## What works today

| Method | When |
|--------|------|
| **`rsync` from your PC** | **Proven 18 May 2026** — full API block (event create, permissions, Firebase link code). Does not need GitHub SSH on the server. Host: `dave@41.61.20.39`. |
| **`git pull` on the server** | Needs a deploy SSH key for your Linux user (see below). `git pull` → `Permission denied (publickey)` for GitHub until key is added. |

---

## Update production with rsync (no server GitHub key)

On your **PC** (replace `YOUR_SSH_HOST` with the IP or hostname you use for SSH):

```bash
cd /home/dave/development/PHP-MyGigGuide
git checkout main
git pull origin main
rsync -avz \
  --no-group --no-owner \
  --exclude '.env' \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude 'storage' \
  --exclude 'bootstrap/cache' \
  ./ dave@YOUR_SSH_HOST:/var/www/mygigguide/
```

**Deploy one PHP file** (must include the full destination folder — not the project root only):

```bash
rsync -avz app/Http/Controllers/Api/V1/MeController.php \
  dave@YOUR_SSH_HOST:/var/www/mygigguide/app/Http/Controllers/Api/V1/
```

**If you see** `chgrp ".../bootstrap/cache" failed: Operation not permitted` — the PHP files usually still copied; use the flags above next time. Laravel rebuilds `bootstrap/cache` when you run `artisan` on the server.

On the **VPS** (after rsync or pull):

```bash
cd /var/www/mygigguide
# If artisan says PailServiceProvider not found, dev cache was synced — remove and rebuild:
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
php composer.phar install --no-dev --optimize-autoloader
php artisan config:clear
php artisan route:clear
curl -s "https://www.mygigguide.co.za/api/v1/meta"
```

**If website images broke after deploy:** do **not** change app code — fix the storage symlink on the VPS first (see below). Avoid deploying experimental `/storage` route or `filesystems.php` serve changes until images are confirmed working in a browser.

**Posters / `/storage` images (403 or missing on site):**

```bash
cd /var/www/mygigguide
ls -la public/storage
php artisan storage:link
sudo chown -R www-data:www-data storage public/storage
sudo chmod -R 755 storage/app/public
php artisan route:clear
php artisan config:clear
```

Test in browser: open the homepage and one event page — posters should load. Optional curl (expect **200**):

```bash
curl -s -o /dev/null -w "%{http_code}\n" "https://www.mygigguide.co.za/storage/venues/main_pictures/tXRn4b1pOFluDyX3xCgRl9kE8o3hPfBeWZ6OEDf9.jpg"
```

Optional migrations (only if not already applied):

```bash
php artisan migrate --force --path=database/migrations/2026_05_17_120000_grant_create_events_to_user_and_venue_owner_roles.php
php artisan migrate --force --path=database/migrations/2026_05_17_140000_add_firebase_uid_to_users_table.php
```

Optional (only if you changed web CSS/JS): `npm ci && npm run build`. Skip `config:cache` until `.env` is stable (use `config:clear` after deploy).

---

## Enable `git pull` on the VPS (one-time)

Goal: user `dave` can run `git pull origin main` without Thando each time.

### 1. On your PC — create a deploy key (if you do not have one)

```bash
ssh-keygen -t ed25519 -C "dave-mygigguide-deploy" -f ~/.ssh/mygigguide_deploy -N ""
cat ~/.ssh/mygigguide_deploy.pub
```

Copy the **`.pub`** line.

### 2. On GitHub

Repo **kayise-it/PHP-MyGigGuide** → **Settings** → **Deploy keys** → **Add deploy key**

- Title: `mel55-nix02 dave deploy`
- Key: paste the `.pub` line
- Allow write access: **off** (read-only is enough)

### 3. On the VPS — install the private key for `dave`

```bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/mygigguide_deploy
```

Paste the **private** key from `~/.ssh/mygigguide_deploy` on your PC, save, then:

```bash
chmod 600 ~/.ssh/mygigguide_deploy
```

Tell Git to use it for GitHub:

```bash
nano ~/.ssh/config
```

Add:

```
Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/mygigguide_deploy
  IdentitiesOnly yes
```

```bash
chmod 600 ~/.ssh/config
```

### 4. Test

```bash
ssh -T git@github.com
cd /var/www/mygigguide
git pull origin main
```

You want a successful pull, not `Permission denied (publickey)`.

---

## If `git pull` conflicts with server-only edits

Example: `footer.blade.php` changed on the server.

```bash
git stash push -m "server-only" -- resources/views/layouts/footer.blade.php
git pull origin main
```

Restore later only if you still need those edits: `git stash list`, `git stash pop`.

---

## Do not run

- `composer install` via system `/usr/bin/composer` if it failed before — use **`php composer.phar install`** in the app directory.
- `git reset --hard` on production without a backup unless you mean to discard server changes.
