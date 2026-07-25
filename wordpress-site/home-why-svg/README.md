# Home "Why Choose Me" illustrations

Three inline SVGs that replaced the stock photography in the **A Consultant Built
for the AI Search Era** section on the homepage (page 13).

| File | Row | Elementor widget | Attachment |
|---|---|---|---|
| `why-ai-search-visibility.svg` | AI search visibility | `b3e9f52` | 712 |
| `why-fullstack-inhouse.svg` | Full-stack, in-house | `93b83de` | 713 |
| `why-transparent-reporting.svg` | Transparent reporting | `c9375a7` | 714 |

They replaced `why-ai-search-era.jpg` (58), `why-fullstack-development.jpg` (59)
and `why-transparent-reporting.jpg` (60). Those attachments are still in the
media library but are no longer referenced anywhere.

## Design notes

Each is authored at a `1200×900` viewBox to match the section's
`aspect-ratio: 4/3` rule, so `object-fit: cover` never crops them. They use the
site palette only — `#111111` ink, `#fafafa` panel, `#e8e8e8` borders, `#888888`
secondary text, with `#e8a013` as the single accent.

Type uses a generic system sans stack rather than Geist. An SVG referenced
through `<img src>` cannot load external webfonts, so Geist would silently fall
back anyway; the diagrams are built so structure carries the meaning and the
labels stay short.

## Deploying

The files live at `wp-content/uploads/2026/07/`. WordPress blocks SVG uploads
through the media library on this install, so they were written to disk directly
and registered with `wp_insert_attachment()` — which skips the upload MIME check
— then pointed at from the three Elementor image widgets. Re-uploading through
the admin UI will not work; copy the files and update the attachment rows.
