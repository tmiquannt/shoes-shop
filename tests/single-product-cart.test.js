/**
 * PDP add-once contract: execute the production script with an in-memory DOM.
 * No WordPress bootstrap, database, browser, or network mutations.
 * Current: native add-to-cart and the AJAX handler both process one request.
 * Expected: AJAX carries one product_id and selected quantity through one handler.
 * Unchanged: variant attributes, pending-submit guard, and non-JS form markup.
 */
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../assets/js/single-product.js'), 'utf8');
const uncertainMessage = 'Chưa xác nhận được sản phẩm đã thêm hay chưa. Vui lòng kiểm tra giỏ hàng trước khi thử lại.';

function setup({ fields = [['quantity', '1']], fetchResult } = {}) {
    const requests = [];
    const events = [];
    const alerts = [];
    const jqueryEvents = [];
    const classes = new Set();
    let submitListener;
    let nativeSubmits = 0;
    const button = {
        name: 'add-to-cart', value: '42', disabled: false,
        classList: { add() {}, remove() {} },
    };
    const form = {
        fields,
        classList: {
            contains: (name) => classes.has(name),
            add: (name) => classes.add(name),
            remove: (name) => classes.delete(name),
        },
        querySelector: (selector) => selector === '.single_add_to_cart_button' ? button : null,
        addEventListener: (name, listener) => { if (name === 'submit') submitListener = listener; },
        submit: () => { nativeSubmits += 1; },
    };
    // Browser FormData(form) includes successful fields, excluding the submit button.
    class FormDataDouble {
        constructor(target) { this.values = new Map(target ? target.fields : []); }
        append(name, value) { this.values.set(name, String(value)); }
        has(name) { return this.values.has(name); }
        get(name) { return this.values.get(name) ?? null; }
        delete(name) { this.values.delete(name); }
    }
    const document = {
        querySelectorAll: () => [],
        querySelector: (selector) => selector === 'form.cart' ? form : null,
        addEventListener: (name, listener) => { if (name === 'DOMContentLoaded') listener(); },
        body: { dispatchEvent: (event) => events.push(event) },
    };
    const window = {
        innerWidth: 1440,
        jQuery: () => ({ trigger: (name) => jqueryEvents.push(name) }),
        paxigMiniCart: { ajax_url: '/wp-admin/admin-ajax.php', nonce: 'cart-nonce' },
        paxigSingleProduct: { i18n: {
            add_to_cart_error: 'Could not add item to bag.',
            add_to_cart_uncertain: uncertainMessage,
        } },
    };
    vm.runInNewContext(source, {
        document, window, FormData: FormDataDouble,
        CustomEvent: class { constructor(type, init) { this.type = type; this.detail = init.detail; } },
        console: { error() {} },
        alert: (message) => alerts.push(message),
        fetch: (url, options) => {
            requests.push({ url, options });
            return fetchResult ? fetchResult(options.body) : Promise.resolve({
                json: () => Promise.resolve({ success: true, data: { fragments: {}, cart_hash: 'hash' } }),
            });
        },
    });
    return {
        requests, events, alerts, jqueryEvents, button, form,
        get nativeSubmits() { return nativeSubmits; },
        submit() {
            let prevented = false;
            submitListener({ submitter: button, preventDefault() { prevented = true; } });
            assert.equal(prevented, true);
        },
    };
}

async function settle() {
    await new Promise((resolve) => setImmediate(resolve));
}

test('simple product AJAX uses only product_id and preserves the selected quantity', async () => {
    const env = setup({ fields: [['quantity', '3']] });
    env.submit();
    await settle();
    assert.equal(env.requests.length, 1);
    const { options, url } = env.requests[0];
    assert.equal(url, '/wp-admin/admin-ajax.php');
    assert.equal(options.method, 'POST');
    assert.equal(options.credentials, 'same-origin');
    assert.equal(options.body.get('product_id'), '42');
    assert.equal(options.body.get('quantity'), '3');
    assert.equal(options.body.get('action'), 'paxig_ajax_add_to_cart');
    assert.equal(options.body.get('nonce'), 'cart-nonce');
    assert.equal(options.body.has('add-to-cart'), false, 'native Woo handler must not receive a second add instruction');
    assert.equal(env.nativeSubmits, 0);
    assert.equal(env.events.filter((event) => event.type === 'paxig_added_to_cart').length, 1);
});

test('variable product keeps its product, variation, attributes, and quantity', async () => {
    const env = setup({ fields: [
        ['add-to-cart', '42'], ['product_id', '42'], ['variation_id', '99'],
        ['attribute_pa_size', '43'], ['attribute_pa_color', 'black'], ['quantity', '2'],
    ] });
    env.submit();
    await settle();
    const payload = env.requests[0].options.body;
    assert.equal(payload.has('add-to-cart'), false);
    for (const [key, value] of env.form.fields.filter(([key]) => key !== 'add-to-cart')) {
        assert.equal(payload.get(key), value, key);
    }
});

test('a second submit while pending does not send another mutation; next settled click can submit', async () => {
    let complete;
    const env = setup({ fetchResult: () => new Promise((resolve) => { complete = resolve; }) });
    env.submit();
    env.submit();
    assert.equal(env.requests.length, 1);
    assert.equal(env.button.disabled, true);
    complete({ json: () => Promise.resolve({ success: true, data: { fragments: {} } }) });
    await settle();
    assert.equal(env.button.disabled, false);
    env.submit();
    assert.equal(env.requests.length, 2);
    complete({ json: () => Promise.resolve({ success: true, data: { fragments: {} } }) });
    await settle();
});

for (const failure of ['network', 'invalid-json']) {
    test(`${failure} failure never automatically resubmits an ambiguously completed addition`, async () => {
        const env = setup({ fetchResult: () => failure === 'network'
            ? Promise.reject(new Error('Response lost after server may have committed'))
            : Promise.resolve({ json: () => Promise.reject(new SyntaxError('Invalid response')) }) });
        env.submit();
        await settle();
        assert.equal(env.requests.length, 1);
        assert.equal(env.nativeSubmits, 0, 'automatic native fallback can apply an already committed addition again');
        assert.equal(env.jqueryEvents.filter((name) => name === 'wc_fragment_refresh').length, 1,
            'reconcile the visible bag with a read-only fragment refresh after an uncertain result');
        assert.equal(env.events.filter((event) => event.type === 'paxig_added_to_cart').length, 0);
        const errors = env.events.filter((event) => event.type === 'paxig_add_to_cart_error')
            .map((event) => event.detail.message).concat(env.alerts);
        assert.equal(errors.length, 1);
        assert.equal(errors[0], uncertainMessage);
        assert.equal(env.alerts.length, 0, 'use the existing storefront error event when supported');
        assert.equal(env.button.disabled, false);
        assert.equal(env.form.classList.contains('is-submitting'), false);
    });
}

test('a rejected addition reports its server error without another request or native submit', async () => {
    const env = setup({ fetchResult: () => Promise.resolve({
        json: () => Promise.resolve({ success: false, data: { message: 'Only one shoe is available.' } }),
    }) });
    env.submit();
    await settle();
    assert.equal(env.requests.length, 1);
    assert.equal(env.nativeSubmits, 0);
    assert.equal(env.events[0].type, 'paxig_add_to_cart_error');
    assert.equal(env.events[0].detail.message, 'Only one shoe is available.');
    assert.equal(env.button.disabled, false);
});

test('an add error opens a closed cart drawer and keeps the actual error visible', () => {
    const bodyListeners = {};
    const alertElement = { style: { display: 'none' }, textContent: '' };
    const drawer = {
        open: false,
        addEventListener() {},
        querySelector: (selector) => selector === '.paxig-drawer-alert' ? alertElement : null,
        showModal() { this.open = true; },
    };
    const document = {
        getElementById: () => drawer,
        querySelector: () => null,
        addEventListener: (name, listener) => { if (name === 'DOMContentLoaded') listener(); },
        body: {
            classList: { add() {} },
            addEventListener: (name, listener) => { bodyListeners[name] = listener; },
        },
    };
    const drawerSource = fs.readFileSync(path.join(__dirname, '../assets/js/mini-cart.js'), 'utf8');
    vm.runInNewContext(drawerSource, { document, window: {} });
    bodyListeners.paxig_add_to_cart_error({ detail: { message: 'Please check your bag before trying again.' } });
    assert.equal(drawer.open, true);
    assert.equal(alertElement.textContent, 'Please check your bag before trying again.');
    assert.equal(alertElement.style.display, 'block');
});

test('card add-to-cart with open_drawer: false updates fragments without opening drawer', () => {
    const bodyListeners = {};
    const alertElement = { style: { display: 'none' }, textContent: '' };
    const drawer = {
        open: false,
        addEventListener() {},
        querySelector: (selector) => selector === '.paxig-drawer-alert' ? alertElement : null,
        showModal() { this.open = true; },
    };
    const document = {
        getElementById: () => drawer,
        querySelector: () => null,
        querySelectorAll: () => [{
            outerHTML: '<div class="cart-badge">0</div>',
        }],
        addEventListener: (name, listener) => { if (name === 'DOMContentLoaded') listener(); },
        body: {
            classList: { add() {} },
            addEventListener: (name, listener) => { bodyListeners[name] = listener; },
        },
    };
    const drawerSource = fs.readFileSync(path.join(__dirname, '../assets/js/mini-cart.js'), 'utf8');
    vm.runInNewContext(drawerSource, { document, window: {} });
    bodyListeners.paxig_added_to_cart({
        detail: {
            fragments: { '.cart-badge': '<div class="cart-badge">1</div>' },
            open_drawer: false
        }
    });
    assert.equal(drawer.open, false, 'drawer must remain closed when open_drawer is false');

    // But standard add (like PDP) opens drawer
    bodyListeners.paxig_added_to_cart({
        detail: {
            fragments: { '.cart-badge': '<div class="cart-badge">2</div>' }
        }
    });
    assert.equal(drawer.open, true, 'drawer opens when open_drawer is not false');
});

test('jQuery added_to_cart event skips opening drawer when triggered from .vyre-card-add-to-cart', () => {
    const jqueryListeners = {};
    const drawer = {
        open: false,
        addEventListener() {},
        querySelector: () => null,
        showModal() { this.open = true; },
    };
    const document = {
        getElementById: () => drawer,
        querySelector: () => null,
        addEventListener: (name, listener) => { if (name === 'DOMContentLoaded') listener(); },
        body: {
            classList: { add() {} },
            addEventListener() {},
        },
    };
    const mockJQuery = (el) => ({
        on: (evt, fn) => { jqueryListeners[evt] = fn; },
        closest: (selector) => ({
            length: el && el.classList && el.classList.contains('vyre-card-add-to-cart') ? 1 : 0
        })
    });
    const windowContext = { jQuery: mockJQuery };
    const drawerSource = fs.readFileSync(path.join(__dirname, '../assets/js/mini-cart.js'), 'utf8');
    vm.runInNewContext(drawerSource, { document, window: windowContext });

    // When card button is clicked
    const cardBtn = { classList: { contains: (cls) => cls === 'vyre-card-add-to-cart' } };
    jqueryListeners.added_to_cart({}, {}, 'hash', cardBtn);
    assert.equal(drawer.open, false, 'drawer must not open when card button triggered added_to_cart');

    // When other button (e.g. PDP button) is clicked
    const pdpBtn = { classList: { contains: () => false } };
    jqueryListeners.added_to_cart({}, {}, 'hash', pdpBtn);
    assert.equal(drawer.open, true, 'drawer opens when non-card button triggered added_to_cart');
});
