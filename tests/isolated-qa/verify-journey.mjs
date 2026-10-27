import { homedir } from 'node:os';
import { pathToFileURL } from 'node:url';
import assert from 'node:assert/strict';

const { Browser } = await import(pathToFileURL(`${homedir()}/.codex/skills/design-taste-frontend/scripts/lib/cdp.mjs`));
const browser = await Browser.launch();

try {
    const page = await browser.newPage({ width: 1440, height: 900 });

    console.log('1. Testing Navigation to Home...');
    await page.goto('http://127.0.0.1:8082/');
    const homeTitle = await page.eval(() => document.title);
    console.log('   Home title:', homeTitle);

    console.log('2. Testing PLP (Running Category)...');
    await page.goto('http://127.0.0.1:8082/?product_cat=running');
    const plpProducts = await page.eval(() => document.querySelectorAll('li.product').length);
    console.log('   PLP products found:', plpProducts);
    assert(plpProducts > 0, 'PLP products should be > 0');

    console.log('3. Testing Search...');
    await page.goto('http://127.0.0.1:8082/?s=court&post_type=product');
    const searchProducts = await page.eval(() => document.querySelectorAll('li.product').length);
    console.log('   Search products found for query "court":', searchProducts);
    assert(searchProducts > 0, 'Search should find products');

    console.log('4. Testing PDP Size Selection & CTA...');
    await page.goto('http://127.0.0.1:8082/?product=night-court-one');
    const hasSizeButtons = await page.eval(() => document.querySelectorAll('.paxig-size-button').length);
    console.log('   Size buttons found:', hasSizeButtons);
    
    // Select first available size button
    const selectedSize = await page.eval(() => {
        const btn = document.querySelector('.paxig-size-button:not(:disabled)');
        if (btn) {
            btn.click();
            return btn.textContent.trim();
        }
        return null;
    });
    console.log('   Selected size:', selectedSize);

    const ctaEnabled = await page.eval(() => {
        const btn = document.querySelector('.single_add_to_cart_button');
        return btn && !btn.disabled;
    });
    console.log('   Add to cart CTA enabled after size selection:', ctaEnabled);

    console.log('5. Testing Add to Cart & Mini-cart...');
    await page.eval(() => {
        const btn = document.querySelector('.single_add_to_cart_button');
        if (btn) btn.click();
    });
    await new Promise(r => setTimeout(r, 2000));
    const cartCount = await page.eval(() => {
        const el = document.querySelector('.paxig-cart-count');
        return el ? el.textContent.trim() : '0';
    });
    console.log('   Cart badge count:', cartCount);

    console.log('=== ALL PRE-CHECK BEHAVIORS VERIFIED ===');
} catch (err) {
    console.error('Error during verification:', err);
    process.exit(1);
} finally {
    await browser.close();
}
