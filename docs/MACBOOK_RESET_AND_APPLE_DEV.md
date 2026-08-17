# MacBook reset & Apple development (Dave)

Guide for the inherited **MacBook Pro (Retina, 15-inch, Mid 2014)** — reset, realistic dev limits, and post-setup.

**Machine:** `MacBookPro11,2` · 2.5 GHz quad-core Intel i7 · **16 GB RAM**  
**Official last macOS:** **Big Sur 11.7.x**

---

## Before you erase

1. **Find My / Activation Lock** — System Settings → Apple ID. If signed in as the **previous owner** and Find My is on, they must remove the Mac from [icloud.com/find](https://www.icloud.com/find) or sign out before you can fully own it.
2. **Save anything you need** — USB drive or cloud (skip if nothing to keep).
3. **Plug in the charger** — erase + reinstall can take 30–90+ minutes.

---

## Factory reset

### 1. Shut down

Apple menu → **Shut Down**.

### 2. Boot into Recovery

Turn on and **immediately** hold **⌘ Command + R** until Apple logo or “macOS Utilities” appears.

If that fails, try **⌥ Option + ⌘ Command + R** (Internet Recovery — needs Wi‑Fi).

### 3. Erase the disk

1. **Disk Utility** → Continue.
2. Select the **internal drive** (top-level physical disk or container).
3. **Erase**:
   - **Name:** `Macintosh HD`
   - **Format:** **APFS** if offered, else **Mac OS Extended (Journaled)**
   - **Scheme:** **GUID Partition Map** (if asked)
4. Erase → Done → quit Disk Utility.

### 4. Reinstall macOS

1. **Reinstall macOS** → Continue.
2. Wi‑Fi, agree to terms, select `Macintosh HD`.
3. Wait (can be long on older hardware).

### 5. Set up as your Mac

- Create **your** user — do **not** use the previous owner’s Apple ID.
- iCloud optional at first; add your Apple ID later.

---

## After reinstall

1. **About This Mac** — confirm model and macOS version.
2. **Software Update** (repeat until no more updates) — target **Big Sur 11.7.10**.
3. Confirm no old Apple ID under System Settings → Apple ID.

---

## Blockers

| Problem | Fix |
|--------|-----|
| **Find My / Activation Lock** | Previous owner removes device from their Apple ID |
| **Firmware password** | Previous owner removes it, or Apple with proof of purchase |
| **Recovery won’t start** | ⌥⌘R + good Wi‑Fi |
| **Can’t erase in Disk Utility** | Select **top-level** internal disk, not only a volume |
| **Very slow** | Normal on 2014 hardware; stay on power |

---

## Apple / Flutter dev — what this Mac can do (2026)

| Task | Mid 2014 MBP |
|------|----------------|
| Web, Git, Flutter **Android** | **Fine** |
| Learning Xcode, older Simulator | **OK on Big Sur** |
| **Latest Xcode (15/16)** + **App Store submit today** | **No** — needs newer macOS + Mac |
| Flutter `flutter build ios` for current store rules | **Blocked** without newer Xcode |

**Xcode ceiling (approx.):**

| macOS on this Mac | Max useful Xcode |
|-------------------|------------------|
| Big Sur 11.7 (official max) | **~13.4.x** |
| Monterey+ via OpenCore Legacy Patcher | Possible but hacky — not recommended as main dev Mac |

**For shipping My Gig Guide to the App Store:** use a **newer Mac (M1+)** or **cloud Mac CI** (Codemagic, GitHub Actions `macos-*`, etc.).

This Mac is still useful for: reset daily driver, Android builds, learning, device testing with older tooling.

---

## Post-reset dev checklist (optional)

After Big Sur + Xcode from Mac App Store (latest version it offers):

```bash
xcode-select --install
sudo xcodebuild -license accept
```

Install Homebrew from [brew.sh](https://brew.sh), then:

```bash
brew install git flutter
flutter doctor -v
```

Expect `flutter doctor` to show iOS gaps on this hardware — normal.

---

## Hardware tips (2014 15")

- **16 GB RAM** — good; helps Xcode when OS isn’t too new.
- **Battery** — likely worn; use charger for long installs.
- **Heat** — 15" 2014 runs warm; vents clear, laptop stand helps.
- **SSD** — if original and slow, an upgrade helps day-to-day.

---

## Related docs

- Flutter app repo: `~/development/mygigguide_app`
- Product handoff: [SESSION_HANDOFF.md](./SESSION_HANDOFF.md)
- Dave preferences: [personal.md](./personal.md)
