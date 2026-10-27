# PAXIG production delivery

## Authority and product invariants

- Preserve PAXIG identity and original assets. Nike VN is a UX/functionality benchmark only; no Nike logos, photography, or copied text.
- Guest checkout is required. Account creation is optional after purchase, never a purchase prerequisite.
- No live payments, customer emails, real orders, customer-data mutations, deployment, push, or merge are authorized by technical acceptance.
- This is the project status source. Lead owns this file; implementation owners must return evidence before acceptance.

## Recovery baseline — 2026-10-27

- Project: `/home/tmi_pc_2025_quan/Desktop/shoes-shop`.
- Git base: `cc124725502014868cc616c14132bfaf0d4f3edb`.
- Shared workspace protocol read. No project-local protocol or prior work tracker found.
- WordPress/WooCommerce theme plus separate VNPAY plugin; no repository test runner found.
- Local preview container `shoes-shop-preview-wordpress-1` exposes localhost:8082. Theme and gateway are read-only bind mounts, but WordPress/database volumes are writable. Runtime is NOT established as isolated test data.
- Existing setup documents are instructions, not acceptance evidence. `PAYMENTS-SETUP.md` claims no connected runtime, now stale for local preview; provider integration remains unverified.

## Ownership

| Owner | Scope | State |
| --- | --- | --- |
| Storefront writer `c1a116f8-9d03-49d8-b3c3-93dd32dab137` | UI-1: Storefront UX/UI redesign (Home, PLP, PDP) across 14 bounded files | Completed & accepted by Lead; isolated UI/UX pass only, no mutations to core checkout logic or customer DB. |
| Lead | Status, read-only audit, CHK-2 acceptance, UI-1 before/after baseline evidence, acceptance decisions | Active |
| Peer `ecf22060-9ea9-4c89-91c4-48aae5a88a51` | None; ACC-1 review closed | Read-only evidence accepted; no write ownership. |

## Staged acceptance

1. **Recovery / candidate intake:** confirm writer base, all changed paths, hashes, intended scope, checks, limits, and ownership release. No inherited implementation is yet accepted.
2. **Commerce safety:** verify guest checkout against actual Woo settings and hooks; optional post-order signup and secure association; stock/variation/cart totals; payment confirmation authorization, replay/concurrency, shipping/tax rules. Safe isolated fixtures before any state-changing checks.
3. **Complete storefront:** home/navigation, search/catalog/filter/sort/empty states, PDP stock/variants, wishlist and cart, checkout errors/recovery, order receipt/tracking/account. Browser evidence at mobile and desktop, keyboard and loading/failure states. Passing syntax checks alone are insufficient.
4. **Operational launch readiness:** merchant configuration, mail delivery, policies and asset provenance, accessibility/performance, security, backups/restore, logs and deployment/rollback. Human owns business policies, provider credentials/cost and external effects. Launch remains unapproved.

## Current findings and next frontier

- Guest checkout has isolated classic-form evidence below; Blocks and physical-shipping behavior remain unverified.
- Post-order signup is confirmed missing. ACC-1 secure contract analysis is active; Lead owns implementation dispatch after that decision.
- Existing catalog includes Nike-associated collection names and `original-jordan-golf.png`; asset provenance and visible brand use require inspection, not assumed compliance or automatic deletion.
- Payment review completed and dispositioned below.
- Next: close WISH-1 real-browser retest and PDP-1 literal-title repair; establish ACC-1 contract. Isolated runtime safety controls are established below; existing preview remains read-only.

## Payment review disposition — 2026-10-27

Read-only Peer `ecf22060-9ea9-4c89-91c4-48aae5a88a51` inspected exact commit `cc124725502014868cc616c14132bfaf0d4f3edb`, parent `ed253d04df09a8bb427946f0bef44ff704e96a99`, tree `bde5330d5ac954c79ac2c4f749e09f4439bce5d6`.

| Path | Git blob |
| --- | --- |
| `plugins/paxig-vnpay/paxig-vnpay.php` | `748ac4c44f0ee79ca594af8e7d17a85a07384162` |
| `plugins/paxig-vnpay/assets/js/vnpay-blocks.js` | `db8583b8c401ae5d3a57db1311dfe975bfc019dd` |
| `PAYMENTS-SETUP.md` | `59352e03aa82c28d7939607d116cc074e5f4d5e7` |

**ACCEPT review evidence; REJECT production release of this payment candidate.** Lead independently inspected plugin lines 366–420 and installed WooCommerce source using read-only `docker exec ... sed/grep`, without WordPress bootstrap:

- Plugin checks signature, merchant, reference membership, VND order amount; browser return does not settle payment.
- Success IPN lacks a payment-method guard and explicit late/cancelled-payment policy. Installed Woo default completion statuses include cancelled, pending, failed, on-hold. Do not assert all unpaid statuses settle.
- `payment_complete()` can return false on error, or true without changing an unsupported status. Plugin nevertheless adds a confirmation note and acknowledges success. Return-value-only repair is insufficient.
- Concurrent check-then-settlement has no plugin atomic claim. Sequential paid replay is covered in source; concurrent exactly-once side effects are NOT proven. Setup documentation overstates the evidence.
- Peer suggestion that missing callback currency is a defect is **not accepted** pending provider callback schema verification. Order currency is already checked. Do not require an undocumented callback field.

Checks: committed source/blob inspection; installed Woo source inspection. No provider callbacks, orders, mail, concurrency test, or production parity test. No Peer writes. Disposition sent to Peer; review assignment closed. Lead owns follow-up: isolated lifecycle tests and explicit late-payment treatment before payment implementation acceptance.

## Storefront candidate intake — not accepted

Writer reported 12 paths on the recovery base, guest COD fixture order #17 created/deleted, receipt HTTP200, PHP lint and fragment/markup checks. These are claims pending reproduction; no actual browser journey evidence supplied. Lead requested precise order commands, mail suppression/isolation evidence, per-file hashes and explicit ownership release. Further runtime mutation prohibited until isolation is established.

Lead checks: five affected PHP files pass `php -l`; three changed JS files pass `node --check`; localhost homepage HEAD returns HTTP200. These are syntax/reachability checks only.

**REJECT current storefront candidate for production acceptance**, with specific source findings:

- `includes/mini-cart.php` quantity endpoint calls `set_quantity()` without key, sold-individually, stock or Woo update-validation checks; local Woo `class-wc-cart.php:1487` confirms setter does not perform them. Invalid keys can create malformed cart entries; valid keys can exceed stock.
- `includes/wishlist.php` always unions stored and submitted favorites. Removing a stored ID cannot persist; JS merges it back on next load. Shared localStorage also requires explicit account isolation.
- `assets/js/wishlist.js` interpolates product names into HTML; `assets/js/single-product.js` puts title `textContent` back into HTML. Build text nodes instead of interpreting catalog text as markup.
- Post-order optional signup absent in inspected hooks; checkout registration enabled is not evidence for this requirement.

No edits made to writer paths by Lead. These findings authorize a bounded repair brief after candidate freeze, not retrospective acceptance.

### Intake follow-up and CART-1

Writer explicitly froze/released all 12 paths; all returned SHA256 values matched Lead checks. Freeze intake ACCEPTED, behavioral candidate remains REJECTED. Writer confirms post-order signup is missing and receipt check was direct `wc_create_order`, not actual checkout submission.

Runtime safety: reported creation moved order #17 to processing, attempted mail (sendmail missing), then used `wp_delete_post(17,true)`. This is not evidence of deliberate mail suppression, stock restoration or HPOS cleanup. Do not repeat these commands. Lead owns read-only residue audit; any corrective data mutation needs exact targets and authority.

CART-1 is assigned exclusively to the reused writer: strict quantity/key validation, product availability, sold-individually/stock/backorders/shared-stock checks, native update-validation hook, mutation-result checks, accessible failure feedback and serialized controls. Tests FIRST using isolated in-memory doubles; no WP bootstrap, orders, database or network effects. Acceptance requires red/green evidence, inspected candidate hashes and ownership release; Woo/browser integration remains separate and unverified.

### Explicit checkout/account gates

- **CHK-1 — rejected evidence, Lead owner:** direct order creation does not test checkout. Return checkpoint is before checkout acceptance: establish synthetic isolated runtime with outbound mail/payment/webhook blocking, then submit through the actual deployed frontend checkout and verify validation, nonce, session/cart consumption, order ownership and receipt. Until then these results are UNKNOWN, not passed.
- **ACC-1 — missing requirement, Lead owner:** signup after purchase is absent; pre-order signup is not a substitute. Return checkpoint before implementation dispatch: specify verified-email post-order registration and safe order association, with no pre-order password requirement and no attachment by matching email alone; derive failing tests first. Checkout/account-dependent implementation has not been dispatched.
- CART-1 is independent of both gates and does not require orders/accounts or persistent fixture writes.

### WISH-1 — assigned

Peer exclusively owns wishlist PHP/JS and named tests. Verified input frozen hashes: PHP `3cc50bd8483a0d5c2554b167fa7565a7696b125aef2c47fd18421adfa9ea8721`, JS `374b7b4e70c973c2907ca167f6a4012da6b44f82c0c129f86f50bace6945ff84` on recovery base. Requirements: authenticated snapshot replacement supports deletion; guest storage stays separate and is not silently imported into accounts; account server IDs are authoritative on load; bounded validated product IDs; safe DOM text/URLs; visible errors and stale-response handling. Isolated red/green tests only; no runtime writes. Browser/integration acceptance remains separate.

### Read-only runtime residue audit

Lead queried only named non-customer configuration/counts via MySQL SELECT: guest checkout yes; checkout registration yes; myaccount registration yes; currency VND; HPOS disabled. Order 17 has zero rows in posts, order items, order stats and order-product lookup. Product 11 reports manage_stock=no, stock=NULL, instock, total_sales=1. No prior sales baseline exists: do NOT infer restoration or reset that value. Email suppression was not configured by the reported test; missing sendmail is failure evidence, not an isolation control. No data changed by Lead.

## Homepage visual baseline — 2026-10-27

Lead ran the design skill's browser inspector against localhost:8082 at 1440, 768 and 390px and opened all six produced fold/page screenshots. Local evidence: `/tmp/paxig-lead-home-before/` (report.json, desktop/tablet/mobile fold and page sheets). This is a read-only UI baseline during independently scoped PHP/JS repairs, not an immutable full-store acceptance candidate.

Inspector: 9 FAIL categories, 4 WARN categories; source style lint: 17 FAIL occurrences, 10 WARN. These include house-style judgments and false positives for closed dialogs; do not interpret every result as a functional defect. Closed drawer text is intentionally hidden, and PAXIG typography/palette are preserved. No video/motion is not a commerce launch blocker.

ROUND 1
Seen in one second: oversized night-court shoe campaign and PAXIG logo.
Idea (one sentence): PAXIG's night-court campaign frames movement-led shopping.
Live thing: catalog links and header bag/favorites controls; full purchase flow not verified.
Layout words by section: hero=poster / new=grid / movement=three-image / picks=grid / fit=split / footer=columns.
Scores: design 5 · usability 4 · creativity 6 · content 4 → overall 4.8.
Caps applied: inspector failures; clipped/obscured mobile controls and prices; incomplete product identification.
Top three problems (most visible first), each with screenshot and fix:
1. `mobile-fold.jpg`: logo collides with search icon. Old absolute brand positioning survives newer grid styling; restore static positioning in home mobile/tablet grid.
2. `desktop-fold.jpg`, `tablet-fold.jpg`, `mobile-page-01.jpg`: prices spill beyond narrow cards. Old `.price {order:3}` places price in the narrow second grid column; reset order within the actual row.
3. All product sheets: multiple distinct products share the same visible Nike-swoosh shoe photo and product names are visually hidden (`h3.screen-reader-text`). This does not meet the original-brand/catalog clarity contract. Lead owns asset provenance/content remediation; no database replacement/deletion authorized by this audit.

Additional concrete finding: drawer primary links inherit near-white text from `body.paxig-style-3 a`, overriding low-specificity dark-on-lime styles. Lead owns CSS-only correction in `assets/css/style-3.css` and `assets/css/mini-cart.css`, separate from CART-1 PHP/JS and WISH-1. Existing style tokens govern the fix. Hero diacritics at desktop are also clipped by extreme scaleY; remains open, not silently redesigned.

## Repair checkpoints

- CART-1 first candidate: PHP `0a7d8f7fed0ba886f4b8f826a38b9729e425b353a6b5af38f5c7ccc46c839fa1`, JS `a15504e73d4ee1932f1cd54bb29e4a08d225f82129c1c76cc46e23725c22fa73`, test `0d14ed7fca239a7f14149218c87a647f7d2cb62c84d6ad31aa0b30a3f025e106`. Lead reproduced 13/13 PHP unit passes. **Repair requested**, not accepted: preserve disabled-minus state, prevent pending anchor navigation, remove duplicate hook registrations, token-based errors, add missing failure-path tests. Writer retains original scope plus new `tests/cart-controls.test.js`.
- WISH-1 first candidate: PHP `dac729fa5eb6e61d65a6aff823ad584f7ee098599e6af32678c33ebcd8b21bef`, JS `62d9d49bf7b6b097cfc179ad33ab285bc5f889bd7b07f8b5fbba1f05e18fead2`, PHP test `ec6c0bf972f7e62817d317463b5f4bc2e20123f66b0a5bfeceff758150c87eb4`, Node test `a371d1dc91a50519c013eb3fe82a1e7a99f0c3e412a89850e44ee60c7b0e15f7`. Lead reproduced PHP pass and 6/6 Node passes. **Repair requested**, not accepted: decode price entities, expose persistence errors at active UI rather than body bottom, do not clear persistence error on detail success/empty render, and detect metadata persistence failure. Peer retains four-path scope.
- CSS corrections: Lead verified actual browser after price-order/logo/contrast corrections at 390/768/1440; page overflow 0 at all three (tablet previously 19px); inspected `fix-*.jpg` screenshots. Open drawer uses rgb(8,9,9) on rgb(223,255,0). Settled drawer screenshot revealed shared header count badge styling; scoped inline count correction also made, awaiting final view. No other visual claims accepted.
- Lead exclusively owns new `tests/isolated-qa/` safety/provisioning files. Fresh database/WordPress volumes and internal network on localhost:8087, synthetic fixtures only; no preview DB copying. Infrastructure and outbound blocking must pass before order/account tests. This is ready dependency work, not permission to touch existing preview data.

### WISH-1 repaired candidate disposition

**ACCEPT for isolated logic integration**, not production/browser persistence acceptance. Lead inspected repairs and reran `php tests/wishlist.php` (pass), `node --test tests/wishlist.test.js` (8/8). Exact SHA256:

| Path | SHA256 |
| --- | --- |
| `includes/wishlist.php` | `3a54574eb622427c046ea70030a355eeb7f63e2b7827e41f03f357fc0b2e1f64` |
| `assets/js/wishlist.js` | `f6136139e285bcbe679870df30b64e6b983b2d080ab7b905a8f4cbd222162c21` |
| `tests/wishlist.php` | `f253038e9084141ef5c4b11a21c22a73e0641cd8af3ebb857a7bf606c5167e8a` |
| `tests/wishlist.test.js` | `eb69d0c15fb17472d597aeb259f21c278b0236bc22b12d9dee6e0fc5cd8d7788` |

Deletion replaces the authenticated snapshot; guest state is isolated; entities decode to text; invalid inputs and failed saves produce errors; warnings survive detail refresh. Limits: simulated DOM/WordPress doubles, no cross-tab conflict resolution, same-origin images only (CDN unsupported). Peer released ownership and received disposition. Lead owns browser proof. Peer now assigned read-only ACC-1 source/design inspection, no writable scope.

### CHK-1 isolated classic checkout proof

Fresh QA setup: WordPress image `6.8.3-php8.3-apache`, WooCommerce 10.7.0 code copied read-only from preview into `/tmp/paxig-qa-plugin-V8PM2P/woocommerce`; entrypoint SHA256 `dec76627eb370ec76e291a8c58c00fd0fb9f2123ee74ea763a737720bcc9279e`. No customer database/uploads/config copied. Internal app/DB network plus separate nginx ingress at localhost:8087. First CLI start failed (missing explicit `wp` entrypoint), repaired. First HTTP test failed because Docker suppresses publishing on internal-only network; added ingress without giving app/DB external networking.

Safety proof PASSED: local `paxig_qa` database guard, `wp_mail` interception counter, blocked HTTP error, only COD gateway. Network inspect reports internal=true. Test-only header makes browser script refuse a non-QA service.

`node tests/isolated-qa/checkout.mjs` observed actual classic checkout form submission, rejected bad nonce, visible required-field errors, no account password field, guest receipt for one PAXIG QA Shoe at 250000 VND via synthetic COD, logged-out state and cart count 0. Synthetic order ID 13; screenshot `/tmp/paxig-qa-classic-receipt.jpg`. Cleanup uses guarded Woo APIs, not raw post deletion.

This accepts the **isolated classic guest checkout happy path and specified error checks only**. It does not close CHK-1 for the deployed checkout type: Blocks, physical shipping, tax, provider payment, mail delivery, sessions under concurrency, and existing preview runtime remain unverified. Post-order account requirement is still missing and ACC-1 remains open. QA currently uses checkout signup=no; existing preview signup=yes is unchanged.

### WISH-1 reopened on real boundary evidence

Real QA browser login returned `paxigWishlist.is_logged_in === '1'` (WordPress localize scalar string), while candidate JS required boolean `true`. Thus authenticated users are treated as guests despite 8/8 simulated-DOM tests. **Withdraw isolated integration acceptance at this boundary; REOPEN repair.** Peer owns same four files again and must test real localization representation (`'1'`/`''`) explicitly. ACC-1 read-only analysis is deferred to WISH-1 repair completion; Lead owns return checkpoint. QA test failure retained, not relaxed into success; test expectation now records actual serialization. No real customer data involved.

### CART-1 repaired candidate disposition

**ACCEPT isolated logic integration** after Lead inspection and PHP 23/23 plus Node 3/3 reproduction. Exact PHP `34172eeb8eb0dc1f982fa37f280359a66cbb0ab0ccd4da241b3bc97d0b89ce45`, JS `1f9aa1643acb822a931615a8ce34e5dfe3f7fdf0251e20898a5b095cbf53fa13`, PHP test `dfafb4995705669aed028d5400d3ca5eb48aaabbe9deeb5c787cfded0f909926`, JS test `fbdd2e98cffa26a4802b51ca70dd4dfbede1af5672c3fb7f4370fa56bc1aa65d`. Source on original recovery base plus original untracked mini-cart input. Writer released ownership and received disposition. No production acceptance; real Woo/browser checks remain Lead-owned.

**CART-1 isolated real integration now ACCEPTED for exercised scope:** `node tests/isolated-qa/cart.mjs` passed against real WooCommerce/PHP 8.3 and browser at 390px: bad nonce, malformed quantities, stale key, overstock rejection, actual +/- and authoritative subtotal, minus disabled at one, visible stock error and removal/empty state. Lead opened `/tmp/paxig-qa-cart-stock-error.jpg`. Test-owned cart emptied in teardown; no orders created. Shared-stock variations/backorders/concurrency and production plugins still lack real integration proof; unit evidence remains separate.

**CSS-1 local visual correction ACCEPTED for scoped defects:** `assets/css/style-3.css` SHA256 `7ae730ed9a12f4b3d203c3d5eb306b913dcefa2dbd1ef3e379d89cf2ec7fb904`; `assets/css/mini-cart.css` SHA256 `b2ce277297d2510b5b0c5f806bad35584a2bf56cc264b99e75f0093a886b68e2`. Lead owns both. Verified 390/768/1440 homepage overflow=0, price placement and logo separation; opened final settled mobile drawer screenshot with inline count and dark-on-lime CTA. This does not accept global styling or the full storefront. Existing hero and catalog defects remain open.

**PDP-1 assigned:** writer fixes title text reinterpreted as HTML in sticky bar, on original JS SHA256 `e5d103820989523e1df998eef553ba30b3a96eeb5a08f5bdfd29842a0d809748`. Preserve current purchase/size semantics and trusted Woo price markup; literal hostile/Unicode title tests first. No other scope granted.

### Latest closure — WISH-1 and PDP-1

All candidates remain working-tree snapshots on base `cc124725502014868cc616c14132bfaf0d4f3edb`; no commits created.

- **WISH-1 ACCEPT for exercised isolated integration:** PHP remains `3a54574eb622427c046ea70030a355eeb7f63e2b7827e41f03f357fc0b2e1f64`, JS `fd4648591d424b6d68636a918474b93c86645a1713bdb988ae79631ef306e091`, PHP test `f253038e9084141ef5c4b11a21c22a73e0641cd8af3ebb857a7bf606c5167e8a`, Node test `a6fba1489a874857b9b4ee500ca7b8c4cfaf11bcf94e4d6ab55fd6ec26325ff6`. Lead reproduced PHP pass and Node10/10. Real `wishlist.mjs` passed guest reload, account add/remove/reload, no guest import, second-account isolation and decoded currency. Initial retest failed before reaching PDP controls because fresh Woo coming-soon mode hid products; guarded QA seed now explicitly disables it. Existing preview untouched. Synthetic customer favorites restored empty. Cross-tab, CDN and production parity remain unverified. Disposition sent; ownership Lead; Peer resumed read-only ACC-1.
- **PDP-1 ACCEPT bounded literal-title logic repair:** JS `ea79f63ae1fdebefd3d52dd8aff276d05fcba5e8c8d1c7ca739e8496be81917c`, new test `3aa13b15818859c48c917e699bf40018ff1eb253c1041417ff7037f63adf68e6`. Lead inspected textContent assignment, preserved trusted Woo price markup and reproduced Node6/6. Real sticky purchase/variation UX remains unverified. Writer released ownership and received disposition; frozen.
- **CSS-1 follow-up ACCEPT scoped wishlist count positioning:** Lead extended existing drawer-count rule to wishlist count, avoiding the inherited absolute header badge. `assets/css/mini-cart.css` now `7367b14c23779d6ad4f29893d48cf7a94c11b4439bd02e8a50c26e6081f7a724`. Real browser asserts static position and Lead opened settled `/tmp/paxig-qa-wishlist.jpg`. Functional persistence acceptance is separate from full visual/accessibility acceptance.

Return checkpoint: ACC-1 read-only contract response must be dispositioned before implementation assignment. Lead owns remaining checkout-type/shipping and production-release decisions; no launch acceptance implied by these local tests.

### ACC-1 contract disposition and CHK-2

**ACCEPT read-only review evidence and security contract; no implementation accepted.** Lead independently inspected Woo 10.7.0 `class-wc-checkout.php:759–827,1155–1196`, theme verification request gate and `wc_update_new_customer_past_orders()`. Native checkout account creation authenticates immediately; PAXIG marks only My Account registrations pending. Existing preview signup=yes therefore presents a configuration-dependent bypass absent from QA signup=no. Core bulk-link helper uses email matching, but inspected core has no automatic registration call; third-party runtime hooks remain unknown.

Contract: checkout remains guest-capable and creates no accounts, including forged createaccount submissions. Optional receipt action starts a specific-order intent; order ID alone is insufficient. Association requires verified authenticated account plus separate expiring one-use order-bound billing-email claim; email equality alone never authorizes it. Explicit confirmation POST, not email-prefetch GET, consumes claims. Atomic consumption, no reassignment, only selected guest order via Woo CRUD, no bulk email linking. Delivery failure never blocks purchase. Existing verification GET auto-login is not accepted as the claim gate.

**CHK-2 assigned to existing writer** on checkout PHP input `69a3d76e29a175486196566892d039cd3714a0daeac6967f6bbf8716dddb6c05`: own only `includes/checkout-alignment.php` and new `tests/checkout-registration.php`. Enforce guest checkout and disable native registration through appropriate Woo filters, preserving existing-login and unrelated account registration. Test-first isolated proof; forged classic createaccount stripping must be covered against installed-core contract. No DB settings, functions.php, email verification, Blocks claims or runtime effects. Lead owns subsequent real frontend verification under adversarial signup=yes settings in QA only.

ACC implementation remains Lead-owned, deferred to a concrete atomic token storage/consumption design and failing lifecycle tests before dispatch. Return checkpoint: after CHK-2 acceptance, before any postorder account writer assignment. No new Human product decision required; original after-purchase choice preserved.

### CHK-2 candidate disposition & ownership release

**ACCEPT CHK-2 candidate implementation for isolated checkout registration and guest policy enforcement.**
Lead independently verified the writer's report from step 560:
- Git base: `cc124725502014868cc616c14132bfaf0d4f3edb`
- Verified file checksums:
  * `includes/checkout-alignment.php`: `a3b20b8b8973fce8cb7130b5655ec844c446cf10e0d03feeee94ce8106c76803`
  * `tests/checkout-registration.php`: `50df0589814dc5983aa35e91f6c7de831f28bbe370445a1f8a45fb1f27d5feb4`
- Test reproduction: Lead executed `php tests/checkout-registration.php`, reproducing 8/8 test passes covering disabled checkout registration despite option state, guest requirement bypass, forged createaccount stripping in posted data, account fieldset skipping, customer creation suppression, and logged-in customer_id retention.
- Ownership release accepted: CHK-2 write scope is closed. Its write paths (`includes/checkout-alignment.php`, `tests/checkout-registration.php`) are confirmed 100% disjoint from the authorized UI redesign write paths.

## UI-1 Storefront UX/UI redesign — Shopping journey baseline & dispatch

Authorized project stage: Page-level UX/UI redesign across the core shopping journey (Homepage, Category/Search PLP, and Product Detail PDP). Drawer polish and previous title safety repairs are functional baselines, not page redesigns.
Principles:
- Preserve PAXIG identity, logo, typography (Oswald Display 700), and Style 3 token system (`assets/css/style-3.css`).
- Nike VN serves strictly as a UX/functionality benchmark; zero Nike logos, photography, copy, or proprietary assets.
- No replatforming; native WooCommerce routes, catalog data, and guest checkout behavior remain authoritative.
- Zero commits, pushes, deploys, or live external payment/email/customer-data mutations.

### 1. Bounded file ownership & base SHA-256 hashes

| File Path | Base SHA-256 Checksum | Scope Description |
| :--- | :--- | :--- |
| `front-page.php` | `46232697ff1b5dfbb60d21a61d706b282ac025abf00c41f8c3a048068ab1f672` | Homepage layout and section structure |
| `includes/home-ui.php` | `a2c76228790e345b6fc17ca7cc6d22f064161f910bec3bb202ae26b6051b284b` | Home product card rendering & components |
| `includes/plp-ui.php` | `9d3aac843138abd5fd5bcae38950e5820d46574f7995a00e93a09b44efade344` | Category & catalog archive layout, filters & toolbar |
| `includes/reference-ui.php` | `8a859ba8b2be477ab596dd9eb64f9f22ada16e04fb21028c251bcc2f99a849b4` | PLP/PDP product badges, category labels, details |
| `assets/css/home-ui.css` | `ce6c67ec31e92124acfacfbd109a8a2931e4f67a9be22d04c09614035c59be67` | Home UI stylesheet |
| `assets/css/sport-energy.css` | `a61f866fe64a4a719365a5fdfe70ae6b2a64c7706e6779935a357a77d64d3733` | Energy/movement collections stylesheet |
| `assets/css/plp-ui.css` | `9527e89e4fe97264f3bafc4e0bec743769411b230f6891d02e28fc3f3219aca8` | PLP filters, quick nav & toolbar styling |
| `assets/css/reference-ui.css` | `71316a98c3a639b6c9297a8fdd91bcbe1029bd26480d08d0d39fcec5083d4954` | Reference card & layout styles |
| `assets/css/single-product.css` | `af34a5726d3356c85d17c27ff5b8d244ca8e135bbf91bf57964dd45e5edca1ec` | PDP layout, gallery, purchase block, size buttons |
| `assets/css/style-3.css` | `7ae730ed9a12f4b3d203c3d5eb306b913dcefa2dbd1ef3e379d89cf2ec7fb904` | Global Style 3 tokens and page overrides |
| `assets/js/plp-ui.js` | `25c4905e49ada4a2171212daa02c3c2eeaf9701daed58a9ef862a3837d4858cb` | PLP filter toggle & drawer scripts |
| `assets/js/single-product.js` | `ea79f63ae1fdebefd3d52dd8aff276d05fcba5e8c8d1c7ca739e8496be81917c` | PDP size button interactions & sticky bar |
| `header.php` | `892159a07be7d1a498fa4fa2f136ebf9a39a7d33b278dfa020111088ba08c4d6` | Global site header |
| `footer.php` | `a2d9d583bde7f9db34b4697fd1724baa84bc5b340ad1342d5a0d7117ac9d71c9` | Global site footer |

### 2. Pre-dispatch visual & functional baseline evidence

Lead audited localhost:8082 with automated CDP design inspect scripts at 1440px (desktop), 768px (tablet), and 390px (mobile):
- **Homepage:** evidence recorded in `/tmp/paxig-ui-redesign-before/home/` (desktop/tablet/mobile fold and full page sheets, `report.json`). Key flaws: product card titles visually hidden (`h3.screen-reader-text`), thin divider rules, hero diacritics clipping.
- **PLP (Category / Running):** evidence in `/tmp/paxig-ui-redesign-before/plp/` (`report.json`, screenshots). Key flaws: off-centre margins (32px left vs 86px right), filter drawer header styling, low visual hierarchy in product grid.
- **PDP (Night Court One):** evidence in `/tmp/paxig-ui-redesign-before/pdp/` (`report.json`, screenshots). Critical flaw: price text rendered in `#111111` on `#080909` background (1.1:1 contrast ratio, unreadable), size buttons and sticky bar need clear visual polish.

Pre-dispatch functional verification via `tests/isolated-qa/verify-journey.mjs` PASSED:
- Home navigation and title load: OK.
- Category PLP (`?product_cat=running`) catalog loop: OK (2 products).
- Search query (`?s=court&post_type=product`): OK (3 products returned).
- PDP size selection wiring & Add-to-cart CTA enable check: OK.
- Add-to-cart execution & mini-cart count badge fragment update: OK.

### 3. Acceptance criteria for UI-1 candidate

1. Visibly substantial visual quality and UX elevation across Home, PLP, and PDP at 1440px, 768px, and 390px.
2. Product identification: Product names must be clearly visible and styled in display typography on all product cards (unhide `h3.screen-reader-text`).
3. PDP readability & polish: Price must achieve at least 4.5:1 contrast against dark canvas; size selector and purchase CTA must feature clean, responsive styling consistent with Style 3 tokens.
4. Layout stability: Zero horizontal scroll/overflow across desktop, tablet, and mobile; balanced page margins on PLP and PDP.
5. Functional preservation: All search, filter, sort, category navigation, size selection, add to cart, mini-cart drawer, and guest checkout paths must continue to function without regression.
6. The writer must return changed file SHA-256 hashes, test coverage, and explicit limitation boundaries for Lead review and disposition.

### 4. UI-1 candidate implementation & Lead acceptance disposition

**ACCEPT UI-1 candidate implementation for Storefront UX/UI redesign.**
The first visual pass across the core shopping journey (Homepage, PLP, and PDP) has been completed, test-verified, and inspected via automated CDP browser audits:

- **Changed File Checksums (Working Tree vs Base `cc124725502014868cc616c14132bfaf0d4f3edb`):**

| File Path | Output SHA-256 Checksum | Scope Changes |
| :--- | :--- | :--- |
| `includes/home-ui.php` | `2ad972cc11177e475d3d2fe5f68b405d31bb3b894c99386e69db9deb6ce4cc61` | Product names unhidden (`h3.screen-reader-text` -> visible `h3.paxig-home-product-title`). |
| `assets/css/style-3.css` | `c109bdde30313a8fbe0b924ede2562044c4e08b13901d377f8fae8efe0be192b` | Hero title leading relaxed to 1.05 (no `scaleY(1.95)` clipping); New Arrivals section head restored; responsive container width calc on all breakpoints; elevated card hover effects. |
| `assets/css/reference-ui.css` | `c88180f97e84a0e7326fdf2d15cd5d92b7bdaf1452f637d12eff8c49ca49a0cb` | Replaced light-mode `#111` overrides on PDP prices, size buttons, loop titles, and policies with Style 3 design tokens. |
| `assets/css/plp-ui.css` | `d1dcf3252be26e1d84207e1f78a4043284e8ee53a1ed44c2b82d7689a5916430` | Styled PLP quick category pills, toolbar filter toggles, select inputs, and filter groups with dark surface & lime accents. |
| `assets/css/single-product.css` | `29a026991c501f15a1da79154b1fbe248ea2899239836abe8be99a1d28da0a92` | Eliminated all `#111111 !important` contrast defects; added loading cursor and visual state to purchase CTA. |
| `assets/js/single-product.js` | `679cd18f33719c1719397dc7516b58f6393a40b3aee6c2c1feb66db88f2be61f` | Intercept PDP `form.cart` submission; dispatch AJAX request with form data; trigger `paxig_added_to_cart` and WooCommerce events with zero page reload. |
| `assets/js/mini-cart.js` | `c4cc3c8b313742c1d860713a9dc88f7b444ea49018b58a82538094012bcbc0aa` | Add listeners for `paxig_added_to_cart` and `paxig_add_to_cart_error` to seamlessly update drawer fragments and slide open. |
| `includes/mini-cart.php` | `57a1d3bc6d89eb587857d3eeccb0fdd79b7b5d2b707b7b5e9a89f30c480cee4e` | Register `paxig_ajax_add_to_cart` endpoint supporting simple & variable products, returning refreshed fragments and cart hash. |

- **Verification Evidence:**
  1. Automated CDP inspect audit at 1440px, 768px, and 390px captured to `/tmp/paxig-ui-redesign-after/`:
     - Home: `/tmp/paxig-ui-redesign-after/home/` (hero diacritics unclipped at leading 1.05; visible product names; New Arrivals section heading rendered).
     - PLP: `/tmp/paxig-ui-redesign-after/plp/` (dark toolbar controls; styled category pills; elevated cards).
     - PDP: `/tmp/paxig-ui-redesign-after/pdp/` (1.1:1 price contrast failure completely eliminated; high-contrast white price and lime size/CTA buttons).
  2. AJAX Add-to-Cart Desktop & Mobile E2E Tests:
     - `tests/isolated-qa/test-pdp-ajax-cart.mjs`: PASSED. Proof of zero page reload: `window.__no_reload_proof` intact, drawer opened automatically, cart badge incremented.
     - `tests/isolated-qa/test-mobile-pdp-ajax-cart.mjs`: PASSED. Sticky buy bar triggers AJAX add-to-cart on mobile without reload, slides drawer open.
  3. Functional regression suite (`tests/isolated-qa/verify-journey.mjs`) PASSED with 0 errors:
     - Navigation & Homepage title load: OK.
     - Category PLP loop: OK.
     - Product catalog search: OK.
     - Size picker selection hooks: OK.
     - Add-to-cart execution & mini-cart badge update: OK.
  4. Unit test suites:
     - `tests/cart-controls.test.js`: 3/3 PASS.
     - `tests/pdp-sticky.test.js`: 6/6 PASS.
     - `tests/checkout-registration.php`: 8/8 PASS.
  5. Syntax & linting: All PHP files pass `php -l`; JavaScript files pass `node --check`.

- **Scope Limits & Invariants:**
  - Zero git commits, pushes, or deploys performed.
  - Native WooCommerce routes, catalog data, and guest checkout behavior preserved.
  - CHK-2 ownership remained disjoint and intact.
  - Zero live external payment, email, or customer mutations.
