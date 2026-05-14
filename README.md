# Steve Baron WordPress Theme

A personal-site theme for **stevebaron.com** — outdoorsy Utah-mountain editorial feel, warm sand backgrounds, ember-orange (#c2410c) accents, dark mode, and a load of small delights.

Built for Steve Baron's site, but the structure is general enough that the patterns here could be lifted into any personal/exec/blog theme.

---

## What's in here

### Templates
- `front-page.php` — Hero with live SLC weather, animated stats strip, recent writing, projects preview, "From the archives" random older post.
- `page-about.php` · `page-cv.php` · `page-projects.php` · `page-photos.php` · `page-now.php` · `page-contact.php` — Custom templates for each section.
- `single.php` — Long-form post template with author byline, share button, featured image parallax, "Thanks for reading" CTA card, and prev/next nav.
- `archive.php` · `index.php` — Blog index with category-chip filtering.
- `404.php` — A "no page in the forecast" forecast block with a random flavor line.

### Custom post types
- `sb_project` — Projects/case studies. Meta: year, status (Active/Acquired/Shipped/Hobby/Archived), external URL.
- `sb_experience` — CV entries. Meta: organization, dates. Taxonomy `sb_cv_section` slots entries into Experience / Education / Recognition on the CV page.
- `sb_photo` — Photos for the masonry gallery. Taxonomy `sb_photo_cat` powers the filter chips.

### Customizer settings
Everything content-y is editable in **Appearance → Customize**: hero copy, four stats, accent colors, six social URLs, headshot, CV PDF URL, skills list, contact availability text and toggle, footer tagline, RSS and newsletter URLs.

### One-click site setup
**Tools → Site Setup** in the admin auto-creates the eight expected pages, binds them to the right page templates, configures Settings → Reading (static front + posts page), and builds a Primary nav menu. Idempotent and safe to re-run.

Also exposes:
- A red **"Reset CV & Projects to resume data"** button (trashes existing seeded entries, recoverable from Trash, then re-inserts from the canonical resume content shipped in `inc/post-types.php`).
- A **"Create FOX Weather launch draft post"** button that inserts the essay shipped in `content/fox-weather-launch-DRAFT.md` as a Gutenberg-block draft post in your admin.

---

## The fun parts

### Keyboard
| Key | What it does |
|---|---|
| `⌘K` / `Ctrl+K` | Open the command palette (pages, actions, live post search) |
| `/` | Also opens the palette |
| `?` | Open the keyboard shortcuts modal |
| `R` | Toggle reading mode on a post |
| `Esc` | Close any overlay |
| `↑↑↓↓←→←→BA` | Konami code — up to an hour of snowfall + a toast |

### Easter eggs
- **Click the SB brand mark 5× in 2.5s** → toggle **Weather Geek Mode** (crosshair cursor, wavy accent link underlines, brand-mark wobble). Persists across navigation via sessionStorage.
- **Click the hero weather text 5× in 3s** → snowfall, any time of year.
- **December–February, home page, once per session** → auto-snowfall a few seconds after load.

### Live & dynamic
- **Hero weather** is fetched from the National Weather Service API (`gridpoints/SLC/97,176`), cached for 30 minutes in sessionStorage. Pulsing green dot when live. Falls back silently to the Customizer text on failure.
- **Smart greeting** in the hero eyebrow rotates based on local time of day ("Good morning · Salt Lake City · 40.76° N", "Up late · …", etc).
- **Local SLC clock** on the Contact page ticks once per second via `Intl.DateTimeFormat` with `America/Denver`.
- **Auto dark mode** decision: explicit user preference → OS `prefers-color-scheme` → time of day (dark 7pm–7am). Live-reacts to OS toggle while you're on the site.
- **Footer "currently observing"** line rotates through ten real meteorology terms (barometric pressure, lapse rate, isobars, …). Hover for a real definition.

### Post reading experience
- Auto-generated **Table of Contents** with scroll-spy on posts with 3+ headings.
- **Reading progress bar** across the top.
- **Heading permalinks** — hover any h2/h3 and `#` appears; click copies the URL.
- **Animated underline reveal** on body links.
- **Image zoom** on any body image, plus full **gallery lightbox** with ← → keyboard navigation on the photos grid.
- **Reading mode** (R key) strips nav, footer, share, eyebrow, comments.
- **Featured image parallax** drifts subtly on scroll.
- **End-of-post CTA card** with contact + RSS buttons.
- **Print stylesheet** for a clean printable essay (links expand to URLs).

### Visual polish
- **Topographic SVG background** in the hero, generated in vanilla JS, drifts with the cursor (parallax).
- **3D tilt** on project cards.
- **Animated stat counters** when the stats strip scrolls into view.
- **Stat hover lift** with accent-tinted background.
- **Hero "scroll" chevron cue** that auto-hides after first scroll.
- **Custom selection color** tinted with the accent.
- **Cross-document View Transitions** for smooth page-to-page fades (Chrome 126+, Safari 18+).

### SEO / boring-but-important
- **JSON-LD schema**: `Person` + `WebSite` on home/about/cv/contact; `BlogPosting` on posts.
- **Open Graph + Twitter Card** meta tags on every page, with per-post images and descriptions.
- **Dynamic inline SVG favicon** keyed to the accent color.
- **theme-color** meta for light/dark mode.
- **Lazy + async** on images via the `wp_get_attachment_image_attributes` filter.
- WP head bloat removed: generator, RSD, wlwmanifest, emoji, shortlink.
- **Comprehensive print stylesheet** for the CV page (renders as a real one-pager résumé).

### Admin
- **Dashboard widget** ("Steve Baron · at a glance") with stat cards for Posts/Drafts/CV entries/Projects/Photos plus latest posts and quick links.

---

## File structure

```
stevebaron/
├── style.css                  # Theme header only — actual styles in assets/
├── functions.php              # Setup, enqueues, JSON-LD, OG meta, favicon, head cleanup
├── header.php · footer.php    # Site chrome (nav, dark toggle, status pill, altitude line)
├── front-page.php             # Hero + stats + recent writing + projects + archives card
├── page-about.php · page-cv.php · page-projects.php
├── page-photos.php · page-now.php · page-contact.php
├── single.php · archive.php · index.php · page.php · 404.php
├── inc/
│   ├── customizer.php         # All Customizer settings
│   ├── post-types.php         # CPTs + taxonomies + canonical seed data
│   ├── meta-boxes.php         # Per-page meta fields (Now items, About "into" list)
│   └── setup-site.php         # Tools → Site Setup + dashboard widget + FOX Weather draft
├── template-parts/
│   └── project-card.php       # Reusable project card
├── assets/
│   ├── css/
│   │   ├── main.css           # Full design system (~2000 lines)
│   │   └── editor.css         # Block-editor typography mirroring front-end
│   └── js/
│       └── main.js            # All interactive behavior in one file
└── content/
    └── fox-weather-launch-DRAFT.md
                               # Source-of-truth for the FOX Weather essay
```

---

## Install

### Option A — Upload a zip
1. Download the repo as a zip from GitHub.
2. In WP admin: **Appearance → Themes → Add New → Upload Theme** → upload the zip.
3. Activate. An admin banner at the top of any page will offer to run the one-click site setup.

### Option B — WP-CLI
```sh
wp theme install https://github.com/stevembaron/stevebaron-wordpress-theme/archive/refs/heads/main.zip --activate
```

### Option C — Git Updater
If you've installed the [Git Updater](https://git-updater.com/) plugin, this theme will pick up GitHub releases automatically.

### After install
1. Open **Tools → Site Setup** in the admin.
2. Click **Run site setup**. This creates Home / About / CV / Projects / Writing / Photos / Now / Contact pages, binds them to their templates, sets Reading → static front, and builds the Primary nav menu.
3. Click **Reset CV & Projects to resume data** to populate the CPTs with the canonical resume content from `inc/post-types.php`.
4. (Optional) Click **Create FOX Weather launch draft post** to drop the essay as a draft into your admin.
5. Tweak everything else in **Appearance → Customize**.

---

## License

GPL-2.0-or-later.
