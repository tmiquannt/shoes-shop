# PAXIG Style 3 — Design system

Style 3 carries the supplied night-court campaign image across the full storefront. The visual anchor is black court lighting, off-white product photography, condensed poster typography, and one acid-lime action color. The system applies to the homepage, category and search pages, product detail, cart, checkout, account, and content pages.

The live token definitions are in `assets/css/style-3.css`, scoped to `body.paxig-style-3`. Use those tokens when adding or changing storefront components; the values below are the shared source of truth.

## Tokens

### Color

| Token | Value | Use |
| --- | --- | --- |
| `--paxig-color-canvas` | `#080909` | Main page background and night-court field |
| `--paxig-color-surface` | `#121414` | Product tiles, order panels, footer |
| `--paxig-color-surface-raised` | `#1a1d1c` | Raised or active surface |
| `--paxig-color-media` | `#e9e9e5` | Neutral product-image field |
| `--paxig-color-text` | `#f6f6f2` | Primary text |
| `--paxig-color-text-muted` | `#c2c5bd` | Secondary text; keep at 4.5:1 contrast for body copy |
| `--paxig-color-border` | `#343735` | Quiet dividers and control outlines |
| `--paxig-color-accent` | `#dfff00` | Primary action, selected states, focus ring |
| `--paxig-color-accent-hover` | `#efff70` | Hover state for lime actions |
| `--paxig-color-danger` | `#d94a43` | Form errors and destructive feedback |
| `--paxig-color-on-accent` | `#080909` | Text/icons on lime controls |

Keep the accent limited to actionable or selected elements. Keep product photos on a neutral field so black and white shoes retain their shape.

### Typography

| Token | Role |
| --- | --- |
| `--paxig-font-display` | Self-hosted PAXIG Display (Oswald 700), then Arial Narrow/Arial fallbacks |
| `--paxig-font-body` | Arial, Helvetica, sans-serif for descriptions, help text, and form entry |
| `--paxig-type-caption` | 12px metadata and compact counts |
| `--paxig-type-small` | 14px supporting UI text |
| `--paxig-type-body` | 16px default reading size |
| `--paxig-type-lead` | 18px supporting/intro copy |
| `--paxig-type-card` | 15px product names |
| `--paxig-type-title-small` | Fluid section/card title |
| `--paxig-type-title` | Fluid product and section title |
| `--paxig-type-page-title` | Fluid category/content page title |
| `--paxig-type-product-title` | Fluid product-detail title |
| `--paxig-type-poster` | Responsive campaign title |

Use the display face for headlines, navigation, product names, prices, short category labels, and uppercase buttons. Keep paragraphs and data-entry fields in the body face. Do not mix both faces within one short label. Keep display weight at 700, tracking between `0` and `.08em`, and avoid negative tracking on Vietnamese headings. Keep Vietnamese diacritics inside the line box: line-height at least `1.02`, no fixed-height title containers, and no crop through the accent marks. The Latin and Vietnamese WOFF2 subsets are self-hosted and preloaded by `includes/style-3.php`.

### Spacing and geometry

| Token | Value |
| --- | ---: |
| `--paxig-space-1` through `--paxig-space-9` | 4, 8, 12, 16, 24, 32, 48, 64, 96px |
| `--paxig-layout-max` | 1600px |
| `--paxig-page-gutter` | 32px desktop; 24px compact desktop; 20px tablet; 16px phone |
| `--paxig-container-width` | Capped width with the current responsive gutter |
| `--paxig-grid-gap` | 16px base grid gap |
| `--paxig-section-gap` | 96px desktop, reducing at smaller breakpoints |
| `--paxig-control-height` | 46px minimum control height |
| `--paxig-tap-target` | 44px minimum interactive target |
| `--paxig-focus-width` | 3px visible focus ring |

Use square corners for this direction. Separate content with surface tone and whitespace; reserve thin lines for controls, real tables, and meaningful boundaries.

## Component rules

- **Header:** black, compact, and shared across pages. Use condensed uppercase labels; switch to the menu drawer at tablet width. Keep the logo in its existing brand face.
- **Campaign hero:** use the provided campaign image and crop it with `object-fit: cover`. Keep the two-part poster title clear of the sticky header and let Vietnamese marks breathe above the glyphs. On phones, stack the title and let the call to action span the content width.
- **Product tiles:** use square neutral image fields, `object-fit: contain`, a clear name, a category/meta label, and a high-contrast price. Keep the image field consistent across home and listing pages.
- **Filters and sorting:** keep controls at least 44px high. On tablet and phone, hide the desktop filter column and expose it through the existing filter drawer.
- **Product detail:** place gallery and purchase information side by side when they fit; stack them on phones. Keep size choices, quantity, and add-to-cart controls easy to tap.
- **Cart, checkout, and account:** use the same dark surface, body copy face, condensed button labels, lime active/focus states, and single-column form flow on phones.
- **Accessibility:** body copy and muted metadata must reach WCAG AA contrast; keyboard focus is always visible; honor `prefers-reduced-motion`; do not rely on color alone for errors or selected states.

## Responsive rules

Breakpoints in the stylesheet are mobile-first adjustments to three layout ranges:

| Range | Layout behavior |
| --- | --- |
| Above 1200px — desktop | Full navigation; 5 home products and 4 listing columns; 32px page gutters; sidebar filters |
| 901–1200px — compact desktop | Condensed header gaps; 4 home products and 3 listing columns; 24px gutters |
| 601–900px — tablet | Menu drawer; 3 home/listing columns; 20px gutters; filters open in a drawer; collections use two columns. The campaign title stacks at 780px and below so both lines stay within the safe area. |
| 600px and below — phone | 2 home/listing columns; 16px gutters; stacked product detail and collections; two-column cart/account navigation where space allows |

At every width from 320px upward, content must fit without horizontal scrolling. Use `minmax(0, 1fr)` for grid tracks, allow chip groups to wrap, and keep controls from forcing a minimum page width. Use `clamp()` tokens for titles instead of one-off viewport font sizes. Check the poster headline, product grid, filter/sort row, purchase controls, cart rows, and account navigation at phone, tablet, compact desktop, and desktop widths after layout changes.

## Adding a component

Use semantic tokens and a scoped Style 3 selector. Example:

```css
body.paxig-style-3 .my-component {
  color: var(--paxig-color-text);
  background: var(--paxig-color-surface);
  padding: var(--paxig-space-5);
  font-family: var(--paxig-font-body);
}

body.paxig-style-3 .my-component h2 {
  color: var(--paxig-color-text);
  font-family: var(--paxig-font-display);
  font-size: var(--paxig-type-title-small);
  line-height: 1.08;
}
```

Add or change a token at the scope root, adjust its breakpoint override when needed, and update this file when a public token or rule changes. Avoid introducing one-off colors, font families, and viewport-specific title sizes in component selectors.
