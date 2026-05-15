#!/usr/bin/env python3
"""
Generates a static Open Graph card (1200x630 PNG) for stevebaron.com.
Run from the theme root: python3 scripts/build-og.py
Output: assets/og-default.png

This is intentionally a build-time script — the resulting PNG is
committed to the repo so WordPress doesn't need to spawn Python at
request time.
"""

import math
import os
import random
import sys
from PIL import Image, ImageDraw, ImageFont

ROOT       = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT_PATH   = os.path.join(ROOT, 'assets', 'og-default.png')
WIDTH      = 1200
HEIGHT     = 630

# Theme palette (light mode)
BG         = (253, 252, 250)  # --bg
INK        = ( 26,  22,  20)  # --ink
INK2       = ( 74,  65,  56)  # --ink-2
INK3       = (138, 127, 110)  # --ink-3
ACCENT     = (194,  65,  12)  # --accent  #c2410c
LINE       = (224, 217, 204)  # subtle line color

FONT_BOLD  = '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf'
FONT_REG   = '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf'
FONT_MONO  = '/usr/share/fonts/truetype/liberation/LiberationMono-Regular.ttf'

# Sanity check fonts exist
for fp in (FONT_BOLD, FONT_REG, FONT_MONO):
    if not os.path.exists(fp):
        sys.exit(f"Missing font: {fp}")

img  = Image.new('RGB', (WIDTH, HEIGHT), BG)

# ── Topographic lines, deterministic ──────────────────────────────────────
topo = Image.new('RGBA', (WIDTH, HEIGHT), (0, 0, 0, 0))
tdraw = ImageDraw.Draw(topo)
random.seed(7)

for ring in range(10):
    cx = 880 + math.sin(1.2 + ring * 0.7) * 60
    cy = 360 + math.cos(1.2 + ring * 0.5) * 40
    r  = 90 + ring * 50
    pts = []
    N = 80
    for j in range(N + 1):
        a = (j / N) * math.pi * 2
        noise = 1 + math.sin(a * 3 + ring) * 0.08 + math.cos(a * 5 + ring * 2) * 0.05
        x = cx + math.cos(a) * r * noise
        y = cy + math.sin(a) * r * noise * 0.78
        pts.append((x, y))
    tdraw.line(pts, fill=ACCENT + (38,), width=1)

img.paste(topo, (0, 0), topo)

draw = ImageDraw.Draw(img)

# ── SB mark (top-left, like the nav) ──────────────────────────────────────
MARGIN = 72
mark_size = 56
draw.rounded_rectangle(
    (MARGIN, MARGIN, MARGIN + mark_size, MARGIN + mark_size),
    radius=12,
    fill=ACCENT,
)
mark_font = ImageFont.truetype(FONT_BOLD, 32)
# Center "S" inside the mark
tb = draw.textbbox((0, 0), 'S', font=mark_font)
tw, th = tb[2] - tb[0], tb[3] - tb[1]
draw.text(
    (MARGIN + (mark_size - tw) / 2 - tb[0], MARGIN + (mark_size - th) / 2 - tb[1] - 1),
    'S',
    fill=(255, 255, 255),
    font=mark_font,
)

# ── Eyebrow (mono, top-left under mark) ──────────────────────────────────
eyebrow_font = ImageFont.truetype(FONT_MONO, 18)
draw.text(
    (MARGIN, MARGIN + mark_size + 22),
    'SALT LAKE CITY  ·  40.76 N',
    fill=INK3,
    font=eyebrow_font,
)

# ── Main name (huge) ──────────────────────────────────────────────────────
name_font = ImageFont.truetype(FONT_BOLD, 124)
draw.text((MARGIN - 4, MARGIN + 130), 'Steve Baron', fill=INK, font=name_font)

# ── Tagline ───────────────────────────────────────────────────────────────
tag_font = ImageFont.truetype(FONT_REG, 38)
draw.text(
    (MARGIN, MARGIN + 280),
    'Product, AI & Digital Transformation Executive.',
    fill=INK2,
    font=tag_font,
)
draw.text(
    (MARGIN, MARGIN + 330),
    'Former SVP at Fox Corporation.',
    fill=INK2,
    font=tag_font,
)

# ── Bottom rule + URL ────────────────────────────────────────────────────
bottom_y = HEIGHT - MARGIN
draw.line([(MARGIN, bottom_y - 30), (MARGIN + 60, bottom_y - 30)], fill=ACCENT, width=4)
url_font = ImageFont.truetype(FONT_MONO, 22)
draw.text((MARGIN, bottom_y - 4), 'stevebaron.com', fill=INK, font=url_font)

# Right-side meta: built with the theme
meta_font = ImageFont.truetype(FONT_MONO, 16)
meta = '40.7608  N   ·   111.8910  W   ·   5,100 ft'
mb = draw.textbbox((0, 0), meta, font=meta_font)
mw = mb[2] - mb[0]
draw.text((WIDTH - MARGIN - mw, bottom_y - 4), meta, fill=INK3, font=meta_font)

img.save(OUT_PATH, 'PNG', optimize=True)
print(f"Wrote {OUT_PATH} ({os.path.getsize(OUT_PATH):,} bytes, {WIDTH}x{HEIGHT})")
