// Synthetic local fixture only. Produces one QA order; cleanup.php owns teardown.
import assert from 'node:assert/strict';
import { homedir } from 'node:os';
import { pathToFileURL } from 'node:url';
const browserModule = process.env.PAXIG_BROWSER_MODULE || `${homedir()}/.codex/skills/design-taste-frontend/scripts/lib/cdp.mjs`;
const { Browser } = await import(pathToFileURL(browserModule));
const origin = 'http://127.0.0.1:8087';
const safety = await fetch(origin);
assert.equal(safety.headers.get('x-paxig-isolated-qa'), 'synthetic-only', 'Refuse non-QA target');
const productId = Number(process.env.PAXIG_QA_PRODUCT_ID || 12);
const checkoutId = Number(process.env.PAXIG_QA_CHECKOUT_ID || 7);
assert(Number.isSafeInteger(productId) && productId > 0);
assert(Number.isSafeInteger(checkoutId) && checkoutId > 0);
const browser = await Browser.launch();
try {
    const page = await browser.newPage({ width: 390, height: 900 });
    const waitFor = async (predicate, label) => {
        for (let i = 0; i < 100; i++) {
            if (await page.eval(predicate)) return;
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        throw new Error(`Timeout: ${label}`);
    };
    await page.goto(`${origin}/?add-to-cart=${productId}`);
    await page.goto(`${origin}/?page_id=${checkoutId}`);
    assert.equal(await page.eval(() => !!document.querySelector('form.checkout')), true);
    assert.equal(await page.eval(() => !!document.querySelector('input[name="account_password"]')), false);
    await waitFor(() => !document.querySelector('form.checkout.processing, .blockUI'), 'checkout ready');
    const invalidNonce = await page.eval(async () => {
        const form = new FormData(document.querySelector('form.checkout'));
        form.set('woocommerce-process-checkout-nonce', 'invalid-qa-nonce');
        const response = await fetch('/?wc-ajax=checkout', { method: 'POST', body: form });
        return response.json();
    });
    assert.equal(invalidNonce.result, 'failure', 'Invalid nonce must not create an order');
    await page.eval(() => document.querySelector('#place_order').click());
    await waitFor(() => !!document.querySelector('.woocommerce-NoticeGroup-checkout .woocommerce-error'), 'visible required-field errors');
    const requiredErrors = await page.eval(() => document.querySelector('.woocommerce-NoticeGroup-checkout').innerText);
    assert.match(requiredErrors, /required/i);
    await page.eval(() => {
        const fields = {
            billing_first_name: 'Synthetic', billing_last_name: 'QA',
            billing_country: 'VN', billing_address_1: 'Synthetic QA fixture',
            billing_city: 'Ho Chi Minh City', billing_postcode: '700000',
            billing_phone: '0900000000', billing_email: 'checkout-fixture@example.invalid',
        };
        for (const [name, value] of Object.entries(fields)) {
            const input = document.querySelector(`[name="${name}"]`);
            if (!input) continue;
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const terms = document.querySelector('#terms');
        if (terms) terms.checked = true;
        window.jQuery(document.body).trigger('update_checkout');
    });
    await waitFor(() => !document.querySelector('form.checkout.processing, .blockUI'), 'totals refreshed');
    await page.eval(() => document.querySelector('#place_order').click());
    await waitFor(() => !!document.querySelector('.woocommerce-order-overview__order strong'), 'order receipt');
    const outcome = await page.eval(() => ({
        orderId: document.querySelector('.woocommerce-order-overview__order strong').textContent.trim(),
        receipt: document.querySelector('.woocommerce-order').innerText,
        cartCount: document.querySelector('.paxig-header-cart .paxig-cart-count')?.textContent.trim(),
        loggedIn: document.body.classList.contains('logged-in'),
    }));
    assert.equal(outcome.loggedIn, false);
    assert.equal(outcome.cartCount, '0');
    assert.match(outcome.receipt, /PAXIG QA Shoe/);
    assert.match(outcome.receipt, /250[,.]000/);
    assert.match(outcome.receipt, /QA cash on delivery/);
    await page.screenshot({ path: '/tmp/paxig-qa-classic-receipt.jpg', fullPage: true });
    console.log(JSON.stringify({ passed: ['invalid nonce rejected', 'required-field errors visible', 'guest checkout without password', 'browser form submission', 'receipt product/total/COD', 'cart emptied'], ...outcome }, null, 2));
} finally {
    await browser.close();
}
