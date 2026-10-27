# Isolated commerce QA

This compose project uses **new** named volumes, localhost port 8087, an internal network and a test-only must-use plugin blocking mail and WordPress HTTP. It does not clone customer data. Credentials are intentionally synthetic local fixtures, never production secrets. Existing preview volumes are not mounted.

Prerequisites: the four named Docker images are already local; supply `PAXIG_QA_PLUGIN_DIR` pointing to a disposable copy of the installed WooCommerce plugin code (not uploads, config, or database). Copying code is read-only against the source preview. Record its version/hash with results. Only the nginx ingress has a normal bridge network; WordPress, CLI and database remain on the internal network. This allows localhost browser access without giving the application an outbound route.

From this directory, with that variable exported:

```sh
docker compose up -d --pull never db web ingress
docker compose run --rm cli core install --url=http://127.0.0.1:8087 --title='PAXIG isolated QA' --admin_user=paxig_qa_admin --admin_password=synthetic-local-admin-only --admin_email=qa@example.invalid --skip-email
docker compose run --rm cli plugin activate woocommerce
docker compose run --rm cli theme activate paxig-shoes
docker compose run --rm cli eval-file /var/www/html/wp-content/themes/paxig-shoes/tests/isolated-qa/seed.php
docker compose run --rm cli eval-file /var/www/html/wp-content/themes/paxig-shoes/tests/isolated-qa/check-safety.php
```

Only run installation once per new database. The seed is idempotent by SKU, but does not reset stock after tests. Checkout fixtures are synthetic and may be mutated only here. Tests must record IDs and clean their own fixtures via Woo APIs or discard the entire explicitly named QA project volumes; never clean the existing preview database. `docker compose stop` stops this QA project without deleting evidence. Destruction, if needed, targets only this file's `paxig-isolated-qa` project.

Before mutation tests, prove `wp_mail` is intercepted, external HTTP is blocked, database is `paxig_qa`, network is internal, and gateway list is COD-only. Classic and Blocks checkout need separate verification. A virtual synthetic product omits shipping; it does not establish shipping/tax or real-gateway readiness. Do not use real addresses or credentials.

Run from the repository root after safety passes:

```sh
node tests/isolated-qa/checkout.mjs
node tests/isolated-qa/cart.mjs
```

This uses the installed design skill's zero-dependency Chrome driver (Node 22 and Chrome required); override its location with `PAXIG_BROWSER_MODULE`. If fixture IDs differ, set `PAXIG_QA_PRODUCT_ID` and `PAXIG_QA_CHECKOUT_ID` to seed/option output. The script refuses targets without the QA safety header, uses a fresh browser profile, rejects an invalid nonce, observes required-field errors, submits the actual guest form and asserts receipt/product/total/COD/cart-empty outcomes. Evidence screenshot: `/tmp/paxig-qa-classic-receipt.jpg`.

Always run teardown after a test, even if it fails (from this compose directory):

```sh
docker compose run --rm cli eval-file /var/www/html/wp-content/themes/paxig-shoes/tests/isolated-qa/cleanup.php
```

Cleanup validates the exact synthetic email, product SKU, guest ownership and COD method, restores fixture stock through WooCommerce, then deletes only matching synthetic orders. Fixtures and QA database are disposable; no production cleanup is provided.

For wishlist persistence, create a synthetic customer once in this QA database after safety checks:

```sh
docker compose run --rm cli user create paxig_qa_customer customer-fixture@example.invalid --role=customer --user_pass=synthetic-local-customer-only
```

Then run `node tests/isolated-qa/wishlist.mjs` from the repository root. It checks guest reload, no guest import into accounts, authenticated add/remove across reload, currency text and a second account. Both synthetic account lists must initially be empty. A successful run restores the customer list empty; a failed run can leave fixture favorites requiring inspection before retry. Browser profiles are disposable. This does not test registration, verification mail or order association. Seed disables WooCommerce coming-soon mode in the guarded QA database so guests can reach product pages; it does not change the existing preview.
