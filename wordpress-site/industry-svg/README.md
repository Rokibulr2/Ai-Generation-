# Industry landing page diagrams

Two diagrams per industry page (five pages, ten diagrams), built to the same conventions as
`../blog-svg/`: `1200×900` viewBox, site palette (`#111111` ink, `#fafafa` panel,
`#e8e8e8` borders, `#888888` secondary) with `#e8a013` as the only accent, system
sans stack, and a `<g id="rk-watermark">` logo + domain block in the top-right.

## Restaurants & Hospitality — page 731

| File | Attachment | Shows |
|---|---|---|
| `ind-restaurant-local-pack.svg` | 733 | A "near me, open now" query resolving into a three-result local pack |
| `ind-restaurant-menu-schema.svg` | 734 | A marked-up menu answering a constrained dietary question in an assistant |

## SaaS & Technology — page 732

| File | Attachment | Shows |
|---|---|---|
| `ind-saas-comparison-queries.svg` | 735 | The six decision-stage query types and the page family that should own each |
| `ind-saas-page-architecture.svg` | 736 | Feature / use-case / integration / comparison page families under a product hub |

## E-commerce & Retail — page 737

| File | Attachment | Shows |
|---|---|---|
| `ind-ecom-product-schema.svg` | 740 | Marked-up product fields and the rich result they produce |
| `ind-ecom-category-architecture.svg` | 741 | Category hierarchy beside indexable vs. blocked facet parameters |

## Healthcare & Clinics — page 738

| File | Attachment | Shows |
|---|---|---|
| `ind-health-eeat-trust.svg` | 742 | The four trust layers a YMYL page must carry |
| `ind-health-local-service.svg` | 743 | Condition / treatment / location page structure |

## Real Estate — page 739

| File | Attachment | Shows |
|---|---|---|
| `ind-realestate-area-pages.svg` | 744 | Permanent area layer above temporary listings |
| `ind-realestate-listing-lifecycle.svg` | 745 | What to do with a listing URL once it sells |

## Two layout bugs worth remembering

Both were caught by rendering the SVGs through headless Chromium before
publishing, and both are easy to reintroduce:

1. **Title vs. watermark.** A centred `<text>` at `x="600"` runs under the
   watermark, which occupies roughly `x > 870` at `y ≈ 38–74`. Titles are
   left-aligned at `x="80"` for this reason.
2. **Draw order.** SVG has no z-index — later elements paint on top. Connector
   lines must be emitted *before* the node they run beneath, or they will be
   drawn across it.

A third, in `ind-saas-page-architecture.svg`: four cards across the 1040px
content width need `width ≤ 246` with an 18px gap. At 266 they overlap by 8px
and their gold top bars merge into one continuous line.

## Page structure

Both pages clone the 13-section service-page blueprint (from page 101) and are
children of `/industries/` (page 109). The full Elementor structure for both is
exported to `../elementor-industry-pages-data.json`.

The hero uses attachment **726**
(`Rokibul-Islam-Shuvo-SEO-AEO-GEO-Strategist-in-Bangladesh.png`) rather than 689,
whose filename contains "Expert".

## Forecast figures

Section 8 (`rk-forecast`) on both pages carries outcome numbers. These are
**illustrative ranges, not measured client results**, and the note beneath them
says so explicitly. Keep that note if the figures are edited.
