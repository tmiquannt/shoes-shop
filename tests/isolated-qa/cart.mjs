// Real WooCommerce cart endpoints, synthetic fixture only, no order creation.
import assert from 'node:assert/strict';
import { homedir } from 'node:os';
import { pathToFileURL } from 'node:url';
const { Browser } = await import(pathToFileURL(process.env.PAXIG_BROWSER_MODULE || `${homedir()}/.codex/skills/design-taste-frontend/scripts/lib/cdp.mjs`));
const origin = 'http://127.0.0.1:8087';
assert.equal((await fetch(origin)).headers.get('x-paxig-isolated-qa'), 'synthetic-only');
const productId = Number(process.env.PAXIG_QA_PRODUCT_ID || 12);
const browser = await Browser.launch();
let page, key;
try {
    page = await browser.newPage({ width: 390, height: 900 });
    const waitFor = async (fn) => {
        for (let i = 0; i < 100; i++) {
            if (await page.eval(fn)) return;
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        throw new Error('Cart browser condition timed out');
    };
    await page.goto(`${origin}/?add-to-cart=${productId}`);
    await page.eval(() => document.querySelector('.paxig-header-cart').click());
    key = await page.eval(() => document.querySelector('.paxig-cart-qty-control').dataset.cartItemKey);
    const post = (quantity, itemKey = key, nonce = undefined) => page.eval(async ({ quantity, itemKey, nonce }) => {
        const body = new FormData();
        body.set('action', 'paxig_update_cart_quantity');
        body.set('nonce', nonce ?? window.paxigMiniCart.nonce);
        body.set('cart_item_key', itemKey);
        body.set('quantity', quantity);
        const response = await fetch(window.paxigMiniCart.ajax_url, { method: 'POST', body });
        return { status: response.status, text: await response.text() };
    }, { quantity, itemKey, nonce });
    assert.equal((await post('2', key, 'invalid')).status, 403);
    for (const quantity of ['-1', '1.5', '6', '999999999999999999']) {
        assert.equal(JSON.parse((await post(quantity)).text).success, false, `Reject ${quantity}`);
    }
    assert.equal(JSON.parse((await post('2', 'stale-key')).text).success, false);
    await page.eval(() => document.querySelector('.paxig-qty-btn--plus').click());
    await waitFor(() => document.querySelector('.paxig-qty-val')?.textContent.trim() === '2');
    assert.match(await page.eval(() => document.querySelector('.paxig-cart-drawer-subtotal').textContent), /500[,.]000/);
    await page.eval(() => document.querySelector('.paxig-qty-btn--minus').click());
    await waitFor(() => document.querySelector('.paxig-qty-val')?.textContent.trim() === '1');
    assert.equal(await page.eval(() => document.querySelector('.paxig-qty-btn--minus').disabled), true);
    assert.equal(JSON.parse((await post('5')).text).success, true);
    await page.goto(origin);
    await page.eval(() => document.querySelector('.paxig-header-cart').click());
    await page.eval(() => document.querySelector('.paxig-qty-btn--plus').click());
    await waitFor(() => !!document.querySelector('.paxig-drawer-alert')?.textContent);
    assert.equal(await page.eval(() => document.querySelector('.paxig-qty-val').textContent.trim()), '5');
    assert.match(await page.eval(() => document.querySelector('.paxig-drawer-alert').textContent), /stock/i);
    await page.screenshot({ path: '/tmp/paxig-qa-cart-stock-error.jpg' });
    await page.eval(() => document.querySelector('.paxig-cart-item-remove').click());
    await waitFor(() => !document.querySelector('.paxig-cart-qty-control'));
    assert.match(await page.eval(() => document.querySelector('.paxig-cart-drawer-content').textContent), /Empty/);
    console.log('PASS real browser/Woo: invalid nonce, malformed quantity, stale key, stock rejection without mutation, +/- authoritative subtotal, minus disabled at one, visible stock error, removal/empty state.');
} finally {
    if (page && key) {
        // Restore this test-owned session cart even after a failed assertion.
        await page.eval(async (itemKey) => {
            if (!window.paxigMiniCart) return;
            const body = new FormData();
            body.set('action', 'paxig_update_cart_quantity');
            body.set('nonce', window.paxigMiniCart.nonce);
            body.set('cart_item_key', itemKey);
            body.set('quantity', '0');
            await fetch(window.paxigMiniCart.ajax_url, { method: 'POST', body });
        }, key).catch(() => {});
    }
    await browser.close();
}
