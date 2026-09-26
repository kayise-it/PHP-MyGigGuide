# TikTok + social links in the Flutter app

The API already returns `tiktok` on artists and events (plus `instagram`, `facebook`, `twitter` on artists).

Example — artist **302**:

```bash
curl -s "https://www.mygigguide.co.za/api/v1/artists/302" | grep tiktok
```

This guide adds a **Social** chip row on artist and event detail screens.

---

## Step 1 — Copy the widget into your Flutter app

On your **laptop** (not the VPS):

```bash
cd ~/development/mygigguide_app

cp ~/development/PHP-MyGigGuide/docs/flutter/widgets/detail_social_links.dart lib/widgets/
```

If your Laravel folder is elsewhere, adjust the `cp` source path.

---

## Step 2 — Find which files to edit

Still in `~/development/mygigguide_app`:

```bash
grep -rn "instagram" lib/models lib/screens lib/data 2>/dev/null | head -30
grep -rn "ArtistDetail\|artist_detail" lib/screens 2>/dev/null
grep -rn "EventDetail\|event_detail" lib/screens 2>/dev/null
```

You are looking for:

| What | Typical path |
|------|----------------|
| Artist JSON model | something like `lib/models/...artist....dart` with `instagram` in `fromJson` |
| Event JSON model | something with `ticket_url` / `ticketUrl` in `fromJson` |
| Artist detail screen | `lib/screens/artist_detail_screen.dart` (or similar) |
| Event detail screen | `lib/screens/event_detail_screen.dart` (or similar) |

Paste the grep output here if you get stuck — file names vary slightly between machines.

---

## Step 3 — Artist model: add `tiktok`

Open the artist model file from Step 2 in your editor.

**A.** Near the other social fields, add:

```dart
final String? tiktok;
```

**B.** In the constructor, add `this.tiktok,` next to `this.twitter,`.

**C.** In `fromJson` / factory, add:

```dart
tiktok: json['tiktok'] as String?,
```

**D.** If you have `toJson`, add:

```dart
'tiktok': tiktok,
```

Save the file.

---

## Step 4 — Event model: add `tiktok`

Same pattern on the event/gig model:

```dart
final String? tiktok;
```

Constructor: `this.tiktok,`

`fromJson`:

```dart
tiktok: json['tiktok'] as String?,
```

Save the file.

---

## Step 5 — Artist detail screen

At the top of `artist_detail_screen.dart` (or your artist detail file), add:

```dart
import '../widgets/detail_social_links.dart';
```

Find a good spot **below the bio / about section** (above upcoming events or WhatsApp share). Add:

```dart
DetailSocialLinks(
  instagram: artist.instagram,
  facebook: artist.facebook,
  twitter: artist.twitter,
  tiktok: artist.tiktok,
),
const SizedBox(height: 16),
```

Replace `artist` with whatever your detail screen calls the loaded artist object (sometimes `widget.artist`, `_artist`, or `data`).

---

## Step 6 — Event detail screen

Import the widget:

```dart
import '../widgets/detail_social_links.dart';
```

Below the description / ticket button area, add:

```dart
DetailSocialLinks(
  tiktok: event.tiktok,
),
const SizedBox(height: 16),
```

Events only have TikTok (not Instagram/Facebook on the event record). Replace `event` with your variable name.

---

## Step 7 — Check it compiles

```bash
cd ~/development/mygigguide_app
flutter analyze lib/widgets/detail_social_links.dart lib/screens/
```

Fix any import path or variable name errors (usually wrong path to `detail_social_links.dart` or wrong property name on the model).

---

## Step 8 — Run on a device / emulator

```bash
cd ~/development/mygigguide_app
flutter run --flavor mygigguide --dart-define=BRAND=mygigguide --dart-define=SITE_URL=https://www.mygigguide.co.za
```

Open **artist 302** (Later Alligator) — you should see a **TikTok** chip under Social. Tap it — TikTok opens in the browser/app.

---

## Step 9 — Build a release APK (when happy)

```bash
cd ~/development/mygigguide_app
./scripts/build_apk.sh mygigguide --label=tiktok-social-v1
```

Check **Settings** in the app for the new build number.

---

## Optional later — edit TikTok from the app

The API accepts `tiktok` on:

- `PATCH /api/v1/artists/{id}`
- `POST` / `PATCH /api/v1/events`

To let owners set TikTok in **Add event** / **Edit profile**, add a URL field in `add_gig_screen.dart` and artist edit flows, and pass `tiktok` in `laravel_api.dart` — same pattern as `ticket_url`.

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| No Social section on artist 302 | Model missing `tiktok` in `fromJson`, or detail screen not wired |
| Chip shows but tap does nothing | `url_launcher` — ensure `https://` URLs; check Android manifest queries if needed |
| `instagram` not found in grep | Search `ticket_url` for event model; search `stage_name` for artist model |
| Import error on widget | Fix relative path: from `lib/screens/` use `../widgets/detail_social_links.dart` |

Branch on Laravel repo: `cursor/tiktok-social-links-858a` (this file + widget).
