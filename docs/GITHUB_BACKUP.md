# GitHub backup — web + Flutter app

**Updated:** 17 Aug 2026

Use this to back up all My Gig Guide code to GitHub. The Laravel repo is already connected; the Flutter app still needs its first push from your laptop.

---

## GitHub status (checked 17 Aug 2026)

| Repo | GitHub | Status |
|------|--------|--------|
| **Laravel (web/API)** | [kayise-it/PHP-MyGigGuide](https://github.com/kayise-it/PHP-MyGigGuide) | Connected — `origin` works, `gh` CLI authenticated |
| **Flutter app** | Not on GitHub yet | Create `kayise-it/mygigguide_app` and push from laptop |

Cloud Agent runs only have the Laravel repo. Your laptop (`~/development/`) has newer work (polls, radio flavors, Mix 93.8, notifications) that is **not on GitHub yet**.

---

## Step 1 — Push Laravel (web) from your laptop

```bash
cd ~/development/PHP-MyGigGuide

git status
git add -A
git commit -m "Backup: polls, notifications, Mix 93.8 admin, radio work"
git pull origin main --rebase
git push origin main
```

If you get conflicts, keep your local versions for files you changed recently (polls, services, admin views).

**After push:** rsync to VPS as usual — see `docs/DEPLOY_VPS.md`.

---

## Step 2 — Create Flutter repo on GitHub (one-time)

On GitHub: **New repository** → name `mygigguide_app` → private → no README (we push from laptop).

Or from your laptop (if `gh` is logged in as you):

```bash
gh repo create kayise-it/mygigguide_app --private \
  --description "My Gig Guide Flutter app — flavors: mygigguide, rogues, fm919, hot1027, vowfm, risefm, mix938"
```

---

## Step 3 — First push Flutter app from your laptop

```bash
cd ~/development/mygigguide_app

# If not a git repo yet:
git init
git branch -M main

# Safe .gitignore (do not commit secrets or build output)
cat >> .gitignore <<'EOF'
.dart_tool/
.packages
build/
*.apk
*.aab
*.ipa
android/key.properties
android/app/*.keystore
ios/Runner/GoogleService-Info.plist
.env
EOF

git add -A
git commit -m "Initial backup: MGG app with radio flavors and Mix 93.8"
git remote add origin https://github.com/kayise-it/mygigguide_app.git
git push -u origin main
```

---

## Mix 93.8 — build checklist (on your laptop)

After your `brand_config.dart` Instagram edit:

```bash
cd ~/development/mygigguide_app

# 1. Square launcher icon (≥512×512) — replace assets/images/brands/mix938/app_icon.png if needed
dart run flutter_launcher_icons -f flutter_launcher_icons-mix938.yaml

# 2. Standalone Mix flavor APK
./scripts/build_apk.sh mix938 --label=v1

# 3. Vanilla MGG with On Air strip (includes Mix card)
./scripts/build_apk.sh mygigguide --label=on-air-mix938
```

**Verify:** Mix standalone opens with neon green chrome; vanilla Home shows Mix in the On Air strip; Instagram chip appears on the Radio tab.

---

## What this cloud backup branch adds to Laravel

- Polls API: `GET /api/v1/polls/{context}`, `POST /api/v1/polls/{poll}/vote`
- Admin polls at `/admin/polls` (contexts include **mix938**)
- `php artisan poll:create mix938 "Question?" "Option A" "Option B"`
- WhatsApp alerts via Evolution API (claims + new events)

Merge this PR, then push your laptop copy so GitHub has everything in one place.
