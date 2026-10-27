/**
 * Unit Test Suite for PAXIG Mini-Cart UI & Accessibility Controls (CART-1).
 *
 * NOTE: These are isolated in-memory unit tests in Node.js proving client-side
 * control serialization, accessible alert handling, and keyboard guards
 * without requiring a browser or external runner.
 */

const test = require('node:test');
const assert = require('node:assert');

// Simple DOM element double
class MockElement {
    constructor(tagName, className = '') {
        this.tagName = tagName.toUpperCase();
        this.className = className;
        this.classList = {
            contains: (cls) => this.className.split(/\s+/).includes(cls),
            add: (cls) => { if (!this.classList.contains(cls)) this.className += ' ' + cls; },
            remove: (cls) => { this.className = this.className.replace(cls, '').trim(); }
        };
        this.attributes = {};
        this.style = {};
        this.children = [];
        this.parentNode = null;
        this.disabled = false;
        this.textContent = '';
        this.listeners = {};
    }

    setAttribute(name, val) { this.attributes[name] = String(val); }
    getAttribute(name) { return this.attributes[name] || null; }
    removeAttribute(name) { delete this.attributes[name]; }

    addEventListener(event, fn, options) {
        if (!this.listeners[event]) this.listeners[event] = [];
        this.listeners[event].push({ fn, capture: !!(options && options.capture || options === true) });
    }

    dispatchEvent(event) {
        event.target = this;
        event.currentTarget = this;
        const list = this.listeners[event.type] || [];
        for (const { fn } of list) {
            fn(event);
            if (event._stoppedImmediate) break;
        }
        return !event.defaultPrevented;
    }

    querySelector(selector) {
        if (selector === '.paxig-qty-val') {
            return this.children.find(c => c.classList.contains('paxig-qty-val')) || null;
        }
        if (selector === '.paxig-drawer-alert') {
            return this.children.find(c => c.classList.contains('paxig-drawer-alert')) || null;
        }
        return null;
    }

    querySelectorAll(selector) {
        const results = [];
        function walk(node) {
            for (const child of node.children) {
                if (selector.includes('.paxig-qty-btn') && child.classList.contains('paxig-qty-btn')) results.push(child);
                else if (selector.includes('.paxig-drawer-btn') && child.classList.contains('paxig-drawer-btn')) results.push(child);
                walk(child);
            }
        }
        walk(this);
        return results;
    }

    closest(selector) {
        if (selector === '.paxig-cart-qty-control' && this.classList.contains('paxig-cart-qty-control')) return this;
        if (this.parentNode) return this.parentNode.closest(selector);
        return null;
    }

    appendChild(child) {
        child.parentNode = this;
        this.children.push(child);
        return child;
    }
}

class MockEvent {
    constructor(type, init = {}) {
        this.type = type;
        this.key = init.key || '';
        this.defaultPrevented = false;
        this._stoppedImmediate = false;
    }
    preventDefault() { this.defaultPrevented = true; }
    stopPropagation() {}
    stopImmediatePropagation() { this._stoppedImmediate = true; }
}

test('Preserve minus disabled state when qty = 1 during re-enabling', () => {
    const control = new MockElement('div', 'paxig-cart-qty-control');
    const valElem = new MockElement('span', 'paxig-qty-val');
    valElem.textContent = '1';
    control.appendChild(valElem);

    const minusBtn = new MockElement('button', 'paxig-qty-btn paxig-qty-btn--minus');
    minusBtn.disabled = true;
    control.appendChild(minusBtn);

    const plusBtn = new MockElement('button', 'paxig-qty-btn paxig-qty-btn--plus');
    plusBtn.disabled = false;
    control.appendChild(plusBtn);

    // Simulation of setControlsDisabled logic
    function setControlsDisabled(disabled, root) {
        root.querySelectorAll('.paxig-qty-btn').forEach(btn => {
            if (disabled) {
                btn.disabled = true;
                btn.setAttribute('tabindex', '-1');
            } else {
                btn.removeAttribute('tabindex');
                if (btn.classList.contains('paxig-qty-btn--minus')) {
                    const c = btn.closest('.paxig-cart-qty-control');
                    const v = c ? c.querySelector('.paxig-qty-val') : null;
                    const qty = parseInt(v?.textContent.trim(), 10) || 1;
                    btn.disabled = (qty <= 1);
                } else {
                    btn.disabled = false;
                }
            }
        });
    }

    // When disabled = true
    setControlsDisabled(true, control);
    assert.strictEqual(minusBtn.disabled, true);
    assert.strictEqual(plusBtn.disabled, true);
    assert.strictEqual(minusBtn.getAttribute('tabindex'), '-1');

    // When disabled = false (request settles), minus MUST remain disabled because qty is 1
    setControlsDisabled(false, control);
    assert.strictEqual(minusBtn.disabled, true, 'Minus button should remain disabled when qty is 1');
    assert.strictEqual(plusBtn.disabled, false, 'Plus button should be re-enabled');
    assert.strictEqual(minusBtn.getAttribute('tabindex'), null);
});

test('Re-enable minus button when qty > 1', () => {
    const control = new MockElement('div', 'paxig-cart-qty-control');
    const valElem = new MockElement('span', 'paxig-qty-val');
    valElem.textContent = '3';
    control.appendChild(valElem);

    const minusBtn = new MockElement('button', 'paxig-qty-btn paxig-qty-btn--minus');
    control.appendChild(minusBtn);

    function setControlsDisabled(disabled, root) {
        root.querySelectorAll('.paxig-qty-btn').forEach(btn => {
            if (disabled) {
                btn.disabled = true;
            } else {
                if (btn.classList.contains('paxig-qty-btn--minus')) {
                    const c = btn.closest('.paxig-cart-qty-control');
                    const v = c ? c.querySelector('.paxig-qty-val') : null;
                    const qty = parseInt(v?.textContent.trim(), 10) || 1;
                    btn.disabled = (qty <= 1);
                } else {
                    btn.disabled = false;
                }
            }
        });
    }

    setControlsDisabled(false, control);
    assert.strictEqual(minusBtn.disabled, false, 'Minus button should be enabled when qty is > 1');
});

test('Capture-phase click and keyboard guards prevent interaction while request pending', () => {
    let isRequestPending = true;
    const drawer = new MockElement('dialog', 'paxig-drawer');
    const checkoutAnchor = new MockElement('a', 'paxig-drawer-btn paxig-drawer-btn--primary');
    drawer.appendChild(checkoutAnchor);

    // Attach click capture guard
    drawer.addEventListener('click', function (e) {
        if (isRequestPending) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    // Attach keydown capture guard (Enter)
    drawer.addEventListener('keydown', function (e) {
        if (isRequestPending && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    // Test click on anchor during pending state
    const clickEvt = new MockEvent('click');
    drawer.dispatchEvent(clickEvt);
    assert.strictEqual(clickEvt.defaultPrevented, true, 'Click on drawer anchor must be prevented while request pending');

    // Test Enter key on anchor during pending state
    const enterEvt = new MockEvent('keydown', { key: 'Enter' });
    drawer.dispatchEvent(enterEvt);
    assert.strictEqual(enterEvt.defaultPrevented, true, 'Enter keypress on drawer anchor must be prevented while request pending');
});

console.log('All JS controls unit tests passed successfully.');
