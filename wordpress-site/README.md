# WordPress / Elementor build — SEO Home (WearView style)

Implementation of the Claude Design project file **"SEO Home - WearView Style.dc.html"**
as a fully Elementor-based WordPress homepage on **growwithrokibul.com**.

- **Live page:** https://growwithrokibul.com/ (page ID 13, set as the site front page)
- **Template:** Elementor Canvas (no theme header/footer — the design's own sticky nav and dark footer are part of the page)
- **Theme:** Hello Elementor · **Elementor:** 4.2.0 (flexbox containers)
- **Build date:** 2026-07-21

## Files

| File | Deployed as |
|---|---|
| `elementor-home-data.json` | `_elementor_data` post meta on page 13 — 20 top-level containers, 144 widgets |
| `wpcode-rk-fonts-geist.html` | WPCode HTML snippet, site-wide header — Google Fonts (Geist + Geist Mono) |
| `wpcode-rk-design-system-css.css` | WPCode CSS snippet, site-wide header — full design system + responsive breakpoints |
| `wpcode-rk-sliders-nav.js` | WPCode JS snippet, site-wide footer — slider arrows, gallery auto-advance, mobile nav |
| `design-source-seo-home-wearview.html` | Original design file imported from the Claude Design project |

## Architecture

Every design section is an Elementor **flexbox container** with a `rk-*` CSS class;
content uses native Elementor widgets (heading, text editor, button, image,
social icons, accordion, HTML). All visual styling lives in the WPCode CSS
design-system snippet keyed to those classes, so the page stays fully editable
in the Elementor editor while matching the design pixel-for-pixel.

Sections: sticky nav · hero · "What I Do" auto-advancing gallery slider ·
stats band · about (with social icons) · work experience · client logos ·
3-step process · 9 services · 4 industries · 3 alternating "why me" rows ·
collage strip · before/after · comparison table · testimonial slider ·
video reviews · platforms grid · FAQ accordion · dark CTA · footer.

Elementor kit globals (ID 6) were aligned to the design: system colors set to
`#111111`/`#555555`, system typography set to Geist, button defaults dark.

## Notes

- All images are Elementor placeholders — replace them in the Elementor editor
  (each image widget is labeled by its section).
- Nav anchor links target section IDs: `#about`, `#experience`, `#steps`,
  `#services`, `#industries`, `#compare`, `#faq`, `#contact`.
- Social/footer links currently point to `#` — fill in real profiles.
