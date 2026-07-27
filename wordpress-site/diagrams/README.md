# Article diagrams — "Optimizing Jewelry Brands for ChatGPT Search"

Source for the five images on [post 703](https://growwithrokibul.com/) on
growwithrokibul.com. All five are rendered server-side with PHP GD using the
Google Sans faces that ship with the Site Kit plugin, drawn on a 2× supersampled
canvas and resampled down on save.

| Image | Attachment | Size | Source |
|---|---|---|---|
| `featured_image.png` | 704 | 1200×630 | `fig5-featured.php` |
| `search_comparison_discovery.png` | 705 | 1200×886 | `../blog-diagram-generator.php` |
| `rag_retrieval_pipeline.png` | 706 | 1200×719 | `../blog-diagram-generator.php` |
| `onpage_spec_architecture.png` | 707 | 1200×1145 | `../blog-diagram-generator.php` |
| `chatgpt_schema_mapping.png` | 708 | 1200×853 | `../blog-diagram-generator.php` |

## Why there are two generators

`rk-diagram-lib.php` (`RKCanvas`) plus `fig5-featured.php` produced the featured
image, which is a fixed-size 1200×630 OG card — a known canvas with copy written
to fit it.

The four in-article diagrams were originally built the same way and had to be
rebuilt: at a fixed canvas height with an unfitted 29px title, long strings ran
off the right edge. Headings were clipped mid-word, the black pull-quote bars
overflowed their own background, and body copy spilled out of its cards.

`../blog-diagram-generator.php` replaces those four. It measures first and draws
second: titles shrink to fit the content column, every string wraps to its
container, card heights come from their wrapped contents, and the canvas height
is the sum of the laid-out blocks. Nothing is positioned at a hard-coded
coordinate that text can outgrow.

## Regenerating

The four diagrams, plus a refresh of their WordPress attachment metadata:

```bash
wp eval-file wordpress-site/blog-diagram-generator.php
```

The featured image takes an output path in `$ARGS['path']`:

```php
$ARGS = array( 'path' => WP_CONTENT_DIR . '/uploads/2026/07/featured_image.png' );
require 'wordpress-site/diagrams/fig5-featured.php';
```

Filenames are reused deliberately so attachment IDs and post content stay
untouched; only `_wp_attachment_metadata` needs refreshing afterwards, which the
generator does for the four diagrams it writes.
