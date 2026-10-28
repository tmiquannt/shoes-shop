# PAXIG Style 3 — Design system

Style 3 embodies the Warm Minimalist Luxe aesthetic captured across the 40 official PAXIG interface screens (`Downloads/FULL_UI_PAXG`). The visual signature is an ivory/warm stone canvas, refined neutral surface panels, crisp obsidian black typography, and bold solid black action buttons. The system applies consistently across homepage, collections (PLP), product detail (PDP), cart drawer, multi-step checkout, customer my-account, and support pages.

The live token definitions are in `assets/css/style-3.css`, scoped to `body.paxig-style-3`. Use those tokens when adding or changing storefront components; the values below are the shared source of truth.

## Tokens

### Color

| Token | Value | Use |
| --- | --- | --- |
| `--paxig-color-canvas` | `#f9f7f2` | Primary page canvas / warm stone background |
| `--paxig-color-surface` | `#efebe4` | Container panels, order cards, checkout summary boxes |
| `--paxig-color-surface-raised` | `#e5e0d8` | Interactive cards, hover states, active navigation |
| `--paxig-color-media` | `#eae6df` | Product photography image tile background |
| `--paxig-color-paper` | `#ffffff` | Modals, flyout drawers, dropdowns, input fields |
| `--paxig-color-text` | `#151514` | Primary obsidian black typography, headings, labels |
| `--paxig-color-text-muted` | `#767671` | Subtitles, breadcrumbs, placeholder text, quiet metadata |
| `--paxig-color-border` | `#d6d0c7` | Clean structural dividers and subtle container outlines |
| `--paxig-color-border-subtle` | `#e2ded7` | Header bottom hairline and delicate row rules |
| `--paxig-color-accent` | `#151514` | Primary CTA buttons, pills, active badges |
| `--paxig-color-accent-hover` | `#333330` | Button hover and active press states |
| `--paxig-color-on-accent` | `#ffffff` | Text and icons on black buttons/badges |
| `--paxig-color-danger` | `#d94a43` | Form errors and destructive feedback |
| `--paxig-color-footer` | `#151515` | Deep black contrasting footer |

Keep the accent limited to actionable or selected elements. Keep product photos on a neutral field so black and white shoes retain their shape.

### Typography

| Token | Role |
| --- | --- |
| `--paxig-font-display` | Self-hosted variable PAXIG Display (Oswald 400–700), then Arial Narrow/Arial fallbacks |
| `--paxig-font-body` | Arial, Helvetica, sans-serif for descriptions, help text, and form entry |
| `--paxig-type-caption` | 12px metadata and compact counts |
| `--paxig-type-small` | 14px supporting UI text |
| `--paxig-type-body` | 16px default reading size |
| `--paxig-type-lead` | 18px supporting/intro copy |
| `--paxig-type-card` | 16px product names and card prices across home, catalog and related products |
| `--paxig-type-nav` | 14px navigation and compact actions |
| `--paxig-type-price` | 20px primary product/total prices |
| `--paxig-type-panel-title` | 24px drawer and account panel headings |
| `--paxig-type-title-small` | Fluid section/card title |
| `--paxig-type-title` | Fluid product and section title |
| `--paxig-type-page-title` | Fluid category/content page title |
| `--paxig-type-product-title` | Fluid product-detail title |
| `--paxig-type-poster` | Responsive campaign title |

Use the display face for headlines, navigation, product names, prices, short category labels, and uppercase buttons. Keep paragraphs and data-entry fields in the body face. Do not mix both faces within one short label. Body copy uses 16px, supporting text/controls use 14px, and compact metadata uses 12px; avoid one-off intermediate sizes and sub-12px labels. Keep body weight 400 and headings/actions 700, tracking between `0` and `.08em`, and avoid negative tracking on Vietnamese headings. Use size, spacing, and case for hierarchy. Keep Vietnamese diacritics inside the line box: line-height at least `1.02`, no fixed-height title containers, and no crop through the accent marks. The Latin and Vietnamese variable WOFF2 subsets are self-hosted and preloaded by `includes/style-3.php`.

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
| `--paxig-mobile-nav-height` | 64px plus the device bottom safe area; shared by bottom navigation, page padding and sticky purchase offset |

Use square corners for this direction. Separate content with surface tone and whitespace; reserve thin lines for controls, real tables, and meaningful boundaries.

## Component rules

- **Header:** `#faf8f5` warm off-white header, compact and shared across pages with subtle `#e2ded7` bottom hairline. Use condensed uppercase labels in `#151514`; switch to the menu drawer at tablet width. Keep the logo in its existing brand face.
- **Campaign hero:** use the provided campaign image and crop it with `object-fit: cover`. Keep the two-part poster title clear of the sticky header and let Vietnamese marks breathe above the glyphs. On phones, stack the title and let the call to action span the content width.
- **Product tiles:** use square edge-to-edge product images with `object-fit: cover`, no padding or contrasting frame around photography with its own background, a clear uppercase name, a muted category label, and a high-contrast obsidian black price. Keep the same shared card across home, listings and related products. Wishlist controls remain circular and overlay the top-right corner.
- **Filters and sorting:** keep controls at least 44px high. On tablet and phone, hide the desktop filter column and expose it through the existing filter drawer.
- **Product detail:** place gallery and purchase information side by side when they fit; stack them on phones. Keep size choices, quantity, and solid black add-to-cart controls easy to tap.
- **Cart, checkout, and account:** clean paper `#ffffff` drawers/cards with warm neutral `#efebe4` summary panels, crisp obsidian black buttons, pill status badges (`#d8e2dc` for In Transit, `#e5e2db` for Delivered), and single-column form flow on phones.
- **Accessibility:** body copy and muted metadata must reach WCAG AA contrast; keyboard focus is always visible; honor `prefers-reduced-motion`; do not rely on color alone for errors or selected states.

## Responsive rules

Breakpoints in the stylesheet are mobile-first adjustments to three layout ranges:

| Range | Layout behavior |
| --- | --- |
| Above 1200px — desktop | Full navigation; 5 home products and 4 listing columns; 32px page gutters; sidebar filters |
| 901–1200px — compact desktop | Condensed header gaps; 4 home products and 3 listing columns; 24px gutters |
| 601–900px — tablet | Menu drawer; 3 home/listing columns; 20px gutters; filters open in a drawer; collections use two columns. The campaign title stacks at 780px and below so both lines stay within the safe area. |
| 600px and below — phone | 2 home/listing columns; 16px gutters; stacked product detail and collections; two-column cart/account navigation where space allows |

The shared header switches to its expandable menu at 1200px and below so navigation never competes with the brand and language/account controls. The catalog retains the grid breakpoints above. Keep the header's CSS breakpoint and `assets/js/header.js` resize threshold synchronized.

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

## Icons

Use the locally hosted **Lucide Static 1.63.0** sprite in `assets/vendor/lucide/sprite.svg`; source, pinned checksum and upstream license are beside it. Do not add emoji, Unicode arrows/close glyphs or hand-drawn SVG paths for storefront UI controls. Brand artwork and native WooCommerce rating marks retain their own assets.

PHP uses `paxig_icon('shopping-bag', 22)` from `includes/icons.php`. Dynamic JavaScript can create a decorative placeholder with `data-paxig-icon="shopping-bag"` and `data-icon-size="22"`; the shared `assets/js/icons.js` renderer hydrates it using the same sprite. Icons have a 24px viewBox, stroke width 2, and ignore pointer events so native buttons receive clicks. Keep a translated accessible label on icon-only controls; decorative SVGs have `aria-hidden="true"`.

Use `paxig_disclosure_icons()` for native details/summary controls. CSS shows plus when closed and minus when open; do not also generate text pseudo-icons. Preserve the existing control's classes/data attributes and handler ownership when replacing an icon.
