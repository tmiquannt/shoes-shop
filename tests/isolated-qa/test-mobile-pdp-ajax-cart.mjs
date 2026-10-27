import { homedir } from 'node:os';
import { pathToFileURL } from 'node:url';
import assert from 'node:assert/strict';

const { Browser } = await import(pathToFileURL(`${homedir()}/.codex/skills/design-taste-frontend/scripts/lib/cdp.mjs`));
const browser = await Browser.launch();

try {
    const page = await browser.newPage({ width: 390, height: 844 });

    const url = 'http://127.0.0.1:8082/?product=street-runner-02';
    console.log(`Navigating to mobile ${url}...`);
    await page.goto(url);

    // Initial state
    const initial = await page.eval(() => {
        window.__no_reload_proof = 77777;
        const countEl = document.querySelector('.paxig-cart-count');
        const drawer = document.getElementById('paxig-cart-drawer');
        const stickyBar = document.querySelector('.paxig-sticky-buy-bar');
        return {
            cartCount: countEl ? parseInt(countEl.textContent.trim(), 10) || 0 : 0,
            drawerOpen: drawer ? drawer.open : false,
            stickyBarExists: !!stickyBar
        };
    });
    console.log(`Initial Cart Count: ${initial.cartCount}, Drawer Open: ${initial.drawerOpen}, Sticky Bar Exists: ${initial.stickyBarExists}`);
    assert.equal(initial.stickyBarExists, true, 'Sticky buy bar should exist on mobile');

    // Click Sticky Add to Bag Button
    console.log('Clicking .paxig-sticky-buy-bar__btn on mobile...');
    const clickResult = await page.eval(() => {
        const btn = document.querySelector('.paxig-sticky-buy-bar__btn');
        if (btn) {
            btn.click();
            return true;
        }
        return false;
    });
    console.log(`Button clicked: ${clickResult}`);

    // Wait for AJAX completion & drawer animation
    await new Promise(r => setTimeout(r, 2000));

    // Verify post-click state
    const post = await page.eval(() => {
        const countEl = document.querySelector('.paxig-cart-count');
        const drawer = document.getElementById('paxig-cart-drawer');
        const items = Array.from(document.querySelectorAll('.paxig-cart-drawer-item')).map(it => {
            const title = it.querySelector('.paxig-cart-drawer-item__title')?.textContent.trim();
            const qty = it.querySelector('.paxig-qty-val')?.textContent.trim();
            return { title, qty };
        });

        return {
            flagPreserved: window.__no_reload_proof === 77777,
            cartCount: countEl ? parseInt(countEl.textContent.trim(), 10) || 0 : 0,
            drawerOpen: drawer ? drawer.open : false,
            items: items
        };
    });

    console.log(`Post-Click Results (Mobile):`);
    console.log(`- Page Reload Prevented (Flag Preserved): ${post.flagPreserved}`);
    console.log(`- Drawer Open: ${post.drawerOpen}`);
    console.log(`- New Cart Count: ${post.cartCount}`);
    console.log(`- Drawer Items Count: ${post.items.length}`);
    if (post.items.length > 0) {
        console.log(`- Item 0: ${JSON.stringify(post.items[0])}`);
    }

    assert.equal(post.flagPreserved, true, 'FAIL: Page reloaded on mobile! Window flag was lost.');
    assert.equal(post.drawerOpen, true, 'FAIL: Cart drawer did not open automatically on mobile.');
    assert(post.cartCount > initial.cartCount, `FAIL: Cart count did not increase (was ${initial.cartCount}, now ${post.cartCount}).`);

    console.log('SUCCESS: Mobile PDP Add to Cart functions completely via AJAX with zero reload!');
} finally {
    await browser.close();
}
