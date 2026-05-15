# Project cover images

Drop the four project images here, with these exact filenames:

| File              | Used by                                                |
| ----------------- | ------------------------------------------------------ |
| `fox-weather.png` | FOX Weather (the lightning + FOX WEATHER logo)         |
| `tribune.png`     | Tribune National Digital Platform (Tribune Media logo) |
| `kfor.png`        | First Live-Video Weather App (KFOR.com / Oklahoma News 4) |
| `local-tv.png`    | Customized WordPress CMS for Local TV (Local TV LLC logo) |

Other formats (`.jpg`, `.jpeg`, `.webp`) also work — the loader checks
common extensions in this order: png, jpg, jpeg, webp.

How they get attached:

- On theme activation, `stevebaron_seed_defaults()` sideloads each
  matching file into the Media Library and sets it as the featured
  image on the corresponding project post.
- The same happens when you run **Tools → Site Setup → Reset CV &
  Projects to resume data**.
- After that, the file in this folder is no longer needed at runtime;
  the image lives in `wp-content/uploads/`. It's still useful to keep
  it here for re-installs and future re-seeds.

If a project doesn't have a matching file here, the project card falls
back to the placeholder block. No errors, no broken images.
