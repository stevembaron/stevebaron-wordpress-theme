#!/usr/bin/env python3
"""
Builds the four project cover images for the Steve Baron theme.

Usage:
    python3 scripts/build-project-images.py <fox> <tribune> <kfor> <localtv>

Each argument is a path to the source image. The script outputs four
PNGs to assets/projects/, each 800x600 (4:3) so they fill the theme's
project card aspect ratio without distortion.

Output:
    assets/projects/fox-weather.png   (bleed — fills the canvas)
    assets/projects/tribune.png        (logo card on warm-sand bg)
    assets/projects/kfor.png           (logo card on warm-sand bg)
    assets/projects/local-tv.png       (logo card on warm-sand bg)

Requirements:
    pip install pillow
"""

import os
import sys
from PIL import Image, ImageOps

ROOT     = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT_DIR  = os.path.join(ROOT, 'assets', 'projects')
os.makedirs(OUT_DIR, exist_ok=True)

W, H     = 800, 600
BG       = (253, 252, 250)   # theme --bg, warm sand


def fit_within(img, max_w, max_h):
    """Scale img down (or up) to fit within (max_w, max_h), preserving aspect."""
    ratio = min(max_w / img.width, max_h / img.height)
    return img.resize(
        (max(1, int(img.width * ratio)), max(1, int(img.height * ratio))),
        Image.Resampling.LANCZOS,
    )


def render_card(src_path, out_name, padding=80):
    """Center the source image on a warm-sand 800x600 canvas with padding."""
    src = Image.open(src_path).convert('RGBA')
    canvas = Image.new('RGB', (W, H), BG)
    fitted = fit_within(src, W - 2 * padding, H - 2 * padding)
    x = (W - fitted.width) // 2
    y = (H - fitted.height) // 2
    if fitted.mode == 'RGBA':
        canvas.paste(fitted, (x, y), fitted)
    else:
        canvas.paste(fitted, (x, y))
    out_path = os.path.join(OUT_DIR, out_name)
    canvas.save(out_path, 'PNG', optimize=True)
    return out_path


def render_bleed(src_path, out_name):
    """Fill the 800x600 canvas with the source image, center-cropping if needed."""
    src = Image.open(src_path).convert('RGB')
    fitted = ImageOps.fit(
        src, (W, H),
        method=Image.Resampling.LANCZOS,
        centering=(0.5, 0.5),
    )
    out_path = os.path.join(OUT_DIR, out_name)
    fitted.save(out_path, 'PNG', optimize=True)
    return out_path


def main(argv):
    if len(argv) != 5:
        sys.stderr.write(__doc__)
        sys.exit(2)

    fox, tribune, kfor, localtv = argv[1:5]

    for label, path in [('FOX Weather', fox), ('Tribune', tribune),
                        ('KFOR', kfor), ('Local TV', localtv)]:
        if not os.path.isfile(path):
            sys.exit(f"Source not found for {label}: {path}")

    print("Building project cover images (800x600 each)...")
    out = render_bleed(fox, 'fox-weather.png')
    print(f"  bleed    → {os.path.relpath(out, ROOT)}")
    out = render_card(tribune, 'tribune.png', padding=100)
    print(f"  card     → {os.path.relpath(out, ROOT)}")
    out = render_card(kfor, 'kfor.png', padding=70)
    print(f"  card     → {os.path.relpath(out, ROOT)}")
    out = render_card(localtv, 'local-tv.png', padding=110)
    print(f"  card     → {os.path.relpath(out, ROOT)}")
    print("Done. Commit the four PNGs in assets/projects/ to ship them with the theme.")


if __name__ == '__main__':
    main(sys.argv)
