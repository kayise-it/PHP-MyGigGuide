# Deploying to the VPS (`/var/www/mygigguide`)

Baby-step guide for updating production when `git pull` does not work yet.

---

## What works today

| Method | When |
|--------|------|
| **`rsync` from your PC** | Works now (used for API v1 deploy). Does not need GitHub SSH on the server. |
| **`git pull` on the server** | Needs a deploy SSH key for your Linux user (see below). |

---

## Update production with rsync (no server GitHub key)

On your **PC** (replace `YOUR_SSH_HOST` with the IP or hostname you use for SSH):

```bash
cd /home/dave/development/PHP-MyGigGuide
git checkout main
git pull origin main
rsync -avz \
  --exclude '.env' \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude 'storage' \
  ./ dave@YOUR_SSH_HOST:/var/www/mygigguide/
```

On the **VPS**:

```bash
cd /var/www/mygigguide
php composer.phar dump-autoload -o
php artisan migrate --force
npm ci && npm run build
php artisan route:cache
php artisan config:cache
curl -s "https://www.mygigguide.co.za/api/v1/meta"
```

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
