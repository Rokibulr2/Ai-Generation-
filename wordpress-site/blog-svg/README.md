# Blog article diagrams (SVG)

Inline diagrams for two articles on growwithrokibul.com. All are authored at a
`1200×900` viewBox, use the site palette (`#111111` ink, `#fafafa` panel,
`#e8e8e8` borders, `#888888` secondary) with `#e8a013` as the single accent, and
carry a branding watermark in the top-right corner.

## How to Grow Your Brand Organically — post 719

| File | Attachment | Shows |
|---|---|---|
| `grow-site-architecture.svg` | 715 | Hub-and-spoke structure, three clicks deep |
| `grow-seo-aeo-geo.svg` | 716 | One body of content across three search surfaces |
| `grow-community-flywheel.svg` | 717 | Community participation → UGC → citation loop |

## The Most Important SEO Factors in 2026 — post 724

| File | Attachment | Shows |
|---|---|---|
| `seo-factor-priority.svg` | 720 | Nine factors in foundational / high / compounding tiers |
| `seo-semantic-map.svg` | 721 | Covered subtopics vs. coverage gaps around a core entity |
| `seo-eeat-sources.svg` | 722 | Which E-E-A-T signals are written vs. earned |

## Watermark

Every diagram ends with a `<g id="rk-watermark">` block in the top-right corner:
the site logo (attachment 671, the 512×512 site icon downscaled to 72px and
inlined as a base64 PNG `data:` URI) beside the text `growwithrokibul.com`.

The logo has to be inlined rather than referenced. An SVG loaded through
`<img src>` cannot fetch external resources, so an `href` to the uploads
directory would silently render nothing. The same restriction is why the type
uses a system sans stack instead of Geist.

To re-apply the watermark after editing a diagram, append the block before the
closing `</svg>`; the guard is the `id="rk-watermark"` string, so re-running the
script over an already-watermarked file is a no-op.

## Deploying

Files live at `wp-content/uploads/2026/07/`. SVG uploads are blocked in the media
library on this install, so they are written to disk directly and registered with
`wp_insert_attachment()`, which skips the upload MIME check. Re-uploading through
the admin UI will not work.
