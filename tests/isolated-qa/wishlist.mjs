// Uses only synthetic QA accounts. Does not test registration/email delivery.
import assert from 'node:assert/strict';
import { homedir } from 'node:os';
import { pathToFileURL } from 'node:url';
const { Browser } = await import(pathToFileURL(process.env.PAXIG_BROWSER_MODULE || `${homedir()}/.codex/skills/design-taste-frontend/scripts/lib/cdp.mjs`));
const origin = 'http://127.0.0.1:8087';
assert.equal((await fetch(origin)).headers.get('x-paxig-isolated-qa'), 'synthetic-only');
const productId = Number(process.env.PAXIG_QA_PRODUCT_ID || 12);
const browser = await Browser.launch();
try {
    const page = await browser.newPage({ width: 390, height: 900 });
    await page.send('Page.addScriptToEvaluateOnNewDocument', { source: `
        window.qaPending = 0;
        const originalFetch = window.fetch;
        window.fetch = (...args) => {
            window.qaPending++;
            return originalFetch(...args).finally(() => window.qaPending--);
        };
    ` });
    const waitFor = async (fn) => {
        for (let i = 0; i < 100; i++) {
            if (await page.eval(fn)) return;
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        throw new Error('Wishlist browser condition timed out');
    };
    const product = () => page.goto(`${origin}/?post_type=product&p=${productId}`);
    const toggle = async () => {
        await page.eval(() => document.querySelector('.paxig-wishlist-pdp-btn').click());
        await waitFor(() => window.qaPending === 0);
    };
    const login = async (username, password) => {
        await page.goto(`${origin}/wp-login.php`);
        await page.eval(({ username, password }) => {
            document.querySelector('#user_login').value = username;
            document.querySelector('#user_pass').value = password;
            document.querySelector('#wp-submit').click();
        }, { username, password });
        await waitFor(() => !location.pathname.includes('wp-login.php'));
        await product();
        assert.equal(await page.eval(() => window.paxigWishlist.is_logged_in), '1'); // Actual wp_localize_script scalar representation.
    };
    await product();
    await toggle();
    await product();
    assert.equal(await page.eval(() => document.querySelector('.paxig-wishlist-pdp-btn').getAttribute('aria-pressed')), 'true');
    await login('paxig_qa_customer', 'synthetic-local-customer-only');
    assert.deepEqual(await page.eval(() => window.paxigWishlist.user_ids), []);
    assert.equal(await page.eval(() => document.querySelector('.paxig-wishlist-pdp-btn').getAttribute('aria-pressed')), 'false');
    await toggle();
    await product();
    assert.deepEqual(await page.eval(() => window.paxigWishlist.user_ids), [productId]);
    await page.eval(() => document.querySelector('.paxig-header-wishlist').click());
    await waitFor(() => !!document.querySelector('.paxig-wishlist-drawer-item__price'));
    const price = await page.eval(() => document.querySelector('.paxig-wishlist-drawer-item__price').textContent);
    assert.match(price, /250[,.]000/);
    assert.doesNotMatch(price, /&#|&nbsp;/);
    await page.eval(async () => { await Promise.all(document.getAnimations().map(animation => animation.finished.catch(() => {}))); });
    assert.equal(await page.eval(() => getComputedStyle(document.querySelector('.paxig-drawer-title .paxig-wishlist-count')).position), 'static');
    await page.screenshot({ path: '/tmp/paxig-qa-wishlist.jpg' });
    await page.eval(() => document.querySelector('.paxig-wishlist-item-remove').click());
    await waitFor(() => window.qaPending === 0);
    await product();
    assert.deepEqual(await page.eval(() => window.paxigWishlist.user_ids), []);
    await page.send('Network.clearBrowserCookies'); // Keep device localStorage to exercise account isolation.
    await product();
    assert.equal(await page.eval(() => document.querySelector('.paxig-wishlist-pdp-btn').getAttribute('aria-pressed')), 'true');
    await login('paxig_qa_admin', 'synthetic-local-admin-only');
    assert.deepEqual(await page.eval(() => window.paxigWishlist.user_ids), []);
    assert.equal(await page.eval(() => document.querySelector('.paxig-wishlist-pdp-btn').getAttribute('aria-pressed')), 'false');
    console.log('PASS real browser: guest reload, account isolation, authenticated add/remove + reload, decoded VND detail, second account isolation. Synthetic account list restored empty.');
} finally {
    await browser.close();
}
