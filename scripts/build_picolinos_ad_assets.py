#!/usr/bin/env python3
"""Build Rogues demo ad creatives for Picolinos morning-show sponsor."""

from __future__ import annotations

import math
from pathlib import Path

from PIL import Image, ImageDraw, ImageFilter, ImageFont

ROOT = Path(__file__).resolve().parents[1]
LOGO_SRC = ROOT / "docs" / "65629232_2375126402547351_7209404551126843392_n.png"
OUT_DIR = ROOT / "public" / "demo-ads" / "picolinos"

GREEN = (45, 120, 62)
RED = (180, 45, 42)
WARM_DARK = (38, 24, 16)
WARM_MID = (92, 52, 34)
CREAM = (255, 248, 240)


def _load_font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont | ImageFont.ImageFont:
    candidates = [
        "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf" if bold else "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
        "/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf" if bold else "/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf",
    ]
    for path in candidates:
        if Path(path).exists():
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def _rustic_background(size: tuple[int, int]) -> Image.Image:
    w, h = size
    img = Image.new("RGB", size, WARM_DARK)
    draw = ImageDraw.Draw(img)
    for y in range(h):
        t = y / max(h - 1, 1)
        r = int(WARM_DARK[0] * (1 - t) + WARM_MID[0] * t)
        g = int(WARM_DARK[1] * (1 - t) + WARM_MID[1] * t)
        b = int(WARM_DARK[2] * (1 - t) + WARM_MID[2] * t)
        draw.line([(0, y), (w, y)], fill=(r, g, b))
    # Soft vignette
    vignette = Image.new("L", size, 0)
    vd = ImageDraw.Draw(vignette)
    vd.ellipse((-w * 0.2, -h * 0.3, w * 1.2, h * 1.4), fill=180)
    vignette = vignette.filter(ImageFilter.GaussianBlur(radius=min(w, h) // 6))
    dark = Image.new("RGB", size, (0, 0, 0))
    return Image.composite(img, dark, vignette)


def _paste_logo(base: Image.Image, logo: Image.Image, box: tuple[int, int, int, int]) -> None:
    x0, y0, x1, y1 = box
    target_w = x1 - x0
    target_h = y1 - y0
    lw, lh = logo.size
    scale = min(target_w / lw, target_h / lh)
    nw, nh = max(1, int(lw * scale)), max(1, int(lh * scale))
    resized = logo.resize((nw, nh), Image.Resampling.LANCZOS)
    if logo.mode == "RGBA":
        ox = x0 + (target_w - nw) // 2
        oy = y0 + (target_h - nh) // 2
        base.paste(resized, (ox, oy), resized)
    else:
        ox = x0 + (target_w - nw) // 2
        oy = y0 + (target_h - nh) // 2
        base.paste(resized, (ox, oy))


def _draw_sponsored_badge(draw: ImageDraw.ImageDraw, x: int, y: int) -> None:
    font = _load_font(18, bold=True)
    text = "SPONSORED"
    bbox = draw.textbbox((0, 0), text, font=font)
    tw, th = bbox[2] - bbox[0], bbox[3] - bbox[1]
    pad_x, pad_y = 10, 6
    draw.rounded_rectangle(
        (x, y, x + tw + pad_x * 2, y + th + pad_y * 2),
        radius=6,
        fill=(15, 15, 20, 220),
    )
    draw.text((x + pad_x, y + pad_y - 1), text, fill=(252, 211, 77), font=font)


def build_home_banner(logo: Image.Image) -> Image.Image:
    w, h = 1200, 628
    img = _rustic_background((w, h)).convert("RGBA")
    draw = ImageDraw.Draw(img)
    # Italian accent strip
    draw.rectangle((0, 0, w, 8), fill=GREEN)
    draw.rectangle((0, 8, w, 14), fill=(240, 240, 240))
    draw.rectangle((0, 14, w, 20), fill=RED)

    _draw_sponsored_badge(draw, 24, 28)

    _paste_logo(img, logo, (48, 80, 380, 560))

    title_font = _load_font(52, bold=True)
    sub_font = _load_font(30, bold=True)
    body_font = _load_font(24)

    draw.text((420, 140), "Picolinos", fill=CREAM, font=title_font)
    draw.text((420, 210), "Addictive Pizza · Fourways", fill=(255, 200, 180), font=sub_font)
    draw.text((420, 270), "Morning show sponsor", fill=(56, 189, 248), font=body_font)
    draw.text((420, 310), "Rogues on Radio", fill=(186, 230, 253), font=body_font)
    draw.text((420, 380), "17 free toppings · Family restaurant since 1998", fill=(200, 180, 160), font=_load_font(22))
    draw.text((420, 430), "picolinos.co.za", fill=GREEN, font=_load_font(26, bold=True))

    # Rogues strip
    draw.rounded_rectangle((420, 500, 820, 560), radius=12, fill=(8, 14, 24, 230))
    draw.text((440, 518), "Listen on Rogues on Radio", fill=(148, 163, 184), font=_load_font(20))

    return img.convert("RGB")


def build_events_strip(logo: Image.Image) -> Image.Image:
    w, h = 800, 200
    img = _rustic_background((w, h)).convert("RGBA")
    draw = ImageDraw.Draw(img)
    draw.rectangle((0, 0, w, 4), fill=GREEN)
    _draw_sponsored_badge(draw, 12, 10)
    _paste_logo(img, logo, (16, 36, 160, 184))
    draw.text((180, 52), "Picolinos — Addictive Pizza", fill=CREAM, font=_load_font(28, bold=True))
    draw.text((180, 92), "Morning show sponsor · Fourways", fill=(255, 210, 190), font=_load_font(20))
    draw.text((180, 130), "picolinos.co.za", fill=GREEN, font=_load_font(22, bold=True))
    return img.convert("RGB")


def build_square_card(logo: Image.Image) -> Image.Image:
    size = 400
    img = _rustic_background((size, size)).convert("RGBA")
    draw = ImageDraw.Draw(img)
    _draw_sponsored_badge(draw, 12, 12)
    _paste_logo(img, logo, (40, 60, 360, 220))
    draw.text((40, 240), "Picolinos", fill=CREAM, font=_load_font(32, bold=True))
    draw.text((40, 285), "Morning show", fill=(56, 189, 248), font=_load_font(20))
    draw.text((40, 315), "picolinos.co.za", fill=GREEN, font=_load_font(18, bold=True))
    return img.convert("RGB")


def main() -> None:
    if not LOGO_SRC.exists():
        raise SystemExit(f"Logo not found: {LOGO_SRC}")

    OUT_DIR.mkdir(parents=True, exist_ok=True)
    logo = Image.open(LOGO_SRC).convert("RGBA")

    assets = {
        "logo.png": logo.convert("RGB"),
        "home-banner.jpg": build_home_banner(logo),
        "events-strip.jpg": build_events_strip(logo),
        "square-card.jpg": build_square_card(logo),
    }

    for name, image in assets.items():
        path = OUT_DIR / name
        if name.endswith(".jpg"):
            image.save(path, "JPEG", quality=90, optimize=True)
        else:
            image.save(path, "PNG", optimize=True)
        print(f"Wrote {path} ({image.size[0]}x{image.size[1]})")


if __name__ == "__main__":
    main()
