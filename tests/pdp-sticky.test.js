/**
 * Isolated Unit Test Suite for PAXIG PDP Mobile Sticky Buy Bar (PDP-1).
 *
 * NOTE: These are in-memory unit tests using isolated Node.js environment.
 * They prove client-side sticky bar DOM construction, text node safety,
 * trusted price markup preservation, and event delegation without browser or DB runtime.
 */

const test = require('node:test');
const assert = require('node:assert');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const SCRIPT_PATH = path.resolve(__dirname, '../assets/js/single-product.js');
const scriptSource = fs.readFileSync(SCRIPT_PATH, 'utf8');

// Lightweight Mock DOM Implementation
class MockNode {
    constructor(nodeType, nodeName) {
        this.nodeType = nodeType; // 1 = Element, 3 = Text
        this.nodeName = nodeName;
        this.parentNode = null;
    }
}

class MockTextNode extends MockNode {
    constructor(text) {
        super(3, '#text');
        this.nodeValue = String(text);
    }
    get textContent() { return this.nodeValue; }
    set textContent(val) { this.nodeValue = String(val); }
}

class MockElement extends MockNode {
    constructor(tagName, className = '') {
        super(1, tagName.toUpperCase());
        this.tagName = tagName.toUpperCase();
        this.className = className;
        this.attributes = {};
        this.childNodes = [];
        this.style = {};
        this.listeners = {};
        this.disabled = false;
        this.type = 'button';
        this.dataset = {};
        this.classList = {
            contains: (c) => this.className.split(/\s+/).filter(Boolean).includes(c),
            toggle: (c, force) => {
                const parts = new Set(this.className.split(/\s+/).filter(Boolean));
                const shouldHave = force !== undefined ? !!force : !parts.has(c);
                if (shouldHave) parts.add(c);
                else parts.delete(c);
                this.className = Array.from(parts).join(' ');
            }
        };
    }

    get children() {
        return this.childNodes.filter(n => n.nodeType === 1);
    }

    setAttribute(name, val) { this.attributes[name] = String(val); }
    getAttribute(name) { return this.attributes[name] || null; }
    removeAttribute(name) { delete this.attributes[name]; }

    addEventListener(event, fn) {
        if (!this.listeners[event]) this.listeners[event] = [];
        this.listeners[event].push(fn);
    }

    dispatchEvent(event) {
        event.target = this;
        event.currentTarget = this;
        const list = this.listeners[event.type] || [];
        for (const fn of list) {
            fn(event);
        }
        return !event.defaultPrevented;
    }

    appendChild(child) {
        child.parentNode = this;
        this.childNodes.push(child);
        return child;
    }

    get textContent() {
        return this.childNodes.map(n => n.textContent).join('');
    }

    set textContent(text) {
        this.childNodes = [new MockTextNode(text)];
    }

    get innerHTML() {
        return serializeHTML(this.childNodes);
    }

    set innerHTML(html) {
        this.childNodes = parseHTML(html, this);
    }

    querySelector(selector) {
        return findSelector(this, selector);
    }

    querySelectorAll(selector) {
        const results = [];
        findAllSelectors(this, selector, results);
        return results;
    }

    getBoundingClientRect() {
        return { top: 0, bottom: 50, left: 0, right: 100, width: 100, height: 50 };
    }

    click() {
        this.dispatchEvent({ type: 'click', target: this, defaultPrevented: false });
    }
}

function parseHTML(html, parentNode = null) {
    const root = { tagName: '#root', childNodes: [] };
    const stack = [root];
    const voidTags = new Set(['IMG', 'BR', 'INPUT', 'HR', 'META', 'LINK']);
    const tagRegex = /<(\/)?([a-zA-Z0-9-]+)([^>]*)>|([^<]+)/g;
    let match;

    while ((match = tagRegex.exec(html)) !== null) {
        const [_, isClosing, tagName, attrString, text] = match;
        if (text) {
            const current = stack[stack.length - 1];
            const textNode = new MockTextNode(text);
            textNode.parentNode = current === root ? parentNode : current;
            current.childNodes.push(textNode);
        } else if (isClosing) {
            if (stack.length > 1 && stack[stack.length - 1].tagName === tagName.toUpperCase()) {
                stack.pop();
            }
        } else {
            const tagUpper = tagName.toUpperCase();
            const elem = new MockElement(tagUpper);
            elem.parentNode = stack[stack.length - 1] === root ? parentNode : stack[stack.length - 1];
            if (attrString) {
                const classMatch = attrString.match(/class=[\"']([^\"']+)[\"']/);
                if (classMatch) elem.className = classMatch[1];
                const typeMatch = attrString.match(/type=[\"']([^\"']+)[\"']/);
                if (typeMatch) elem.type = typeMatch[1];
            }
            stack[stack.length - 1].childNodes.push(elem);
            if (!voidTags.has(tagUpper) && !attrString.endsWith('/')) {
                stack.push(elem);
            }
        }
    }
    return root.childNodes;
}

function serializeHTML(nodes) {
    let out = '';
    for (const node of nodes) {
        if (node.nodeType === 3) {
            out += node.nodeValue;
        } else if (node.nodeType === 1) {
            const tag = node.tagName.toLowerCase();
            const cls = node.className ? ` class="${node.className}"` : '';
            out += `<${tag}${cls}>${serializeHTML(node.childNodes)}</${tag}>`;
        }
    }
    return out;
}

function findSelector(root, selector) {
    const list = [];
    findAllSelectors(root, selector, list);
    return list[0] || null;
}

function findAllSelectors(root, selector, results) {
    for (const child of root.childNodes) {
        if (child.nodeType === 1) {
            if (matches(child, selector)) {
                results.push(child);
            }
            findAllSelectors(child, selector, results);
        }
    }
}

function matches(elem, selector) {
    const trimmed = selector.trim();
    if (trimmed.includes(' ')) {
        // Descendant selector support (e.g. '.summary .price')
        const parts = trimmed.split(/\s+/);
        if (parts.length === 2 && matches(elem, parts[1])) {
            let p = elem.parentNode;
            while (p) {
                if (p.nodeType === 1 && matches(p, parts[0])) return true;
                p = p.parentNode;
            }
        }
        return false;
    }
    if (trimmed.startsWith('.')) {
        return elem.classList.contains(trimmed.slice(1));
    }
    return elem.tagName === trimmed.toUpperCase();
}

function createTestEnvironment(options = {}) {
    const document = new MockElement('#document');
    const body = new MockElement('body');
    document.body = body;
    document.appendChild(body);

    const summary = new MockElement('div', 'summary');
    const productTitle = new MockElement('h1', 'product_title');
    productTitle.textContent = options.title !== undefined ? options.title : 'PAXIG Street Court Shoe';

    const priceElem = new MockElement('span', 'price');
    priceElem.innerHTML = options.priceHTML !== undefined ? options.priceHTML : '<span class="amount">1.500.000₫</span>';

    summary.appendChild(productTitle);
    summary.appendChild(priceElem);

    const mainBuyBtn = new MockElement('button', 'single_add_to_cart_button');
    mainBuyBtn.textContent = 'Thêm vào giỏ hàng';

    body.appendChild(summary);
    body.appendChild(mainBuyBtn);

    const window = {
        innerWidth: options.innerWidth !== undefined ? options.innerWidth : 800,
        addEventListener: (event, fn) => document.addEventListener(event, fn),
        document: document,
        IntersectionObserver: class {
            constructor(callback) { this.callback = callback; }
            observe() {}
            unobserve() {}
            disconnect() {}
        }
    };

    document.createElement = function(tagName) {
        return new MockElement(tagName);
    };

    return { window, document, body, productTitle, priceElem, mainBuyBtn };
}

function runScript(env) {
    const sandbox = {
        window: env.window,
        document: env.document,
        Event: class { constructor(type) { this.type = type; } },
        IntersectionObserver: env.window.IntersectionObserver,
        console: console,
    };
    vm.createContext(sandbox);
    vm.runInContext(scriptSource, sandbox);
    env.document.dispatchEvent({ type: 'DOMContentLoaded' });
}

// =========================================================
// PDP-1 Sticky Buy Bar Regression & Behavior Unit Tests
// =========================================================

test('REGRESSION: Escaped HTML/markup in productTitle remains pure text and does NOT produce markup nodes', () => {
    const maliciousTitle = '<img src=x onerror=alert(1)> PAXIG Air Turbo';
    const env = createTestEnvironment({ title: maliciousTitle });
    runScript(env);

    const stickyBar = env.body.querySelector('.paxig-sticky-buy-bar');
    assert.ok(stickyBar, 'Sticky buy bar should be created');

    const titleElem = stickyBar.querySelector('.paxig-sticky-buy-bar__title');
    assert.ok(titleElem, 'Title element should exist in sticky buy bar');

    // Regression assertions:
    // If title was interpolated into innerHTML, titleElem will contain an <img> child element!
    const imgNode = titleElem.querySelector('img');
    assert.strictEqual(imgNode, null, 'Title must not create an <img> element node from title string');
    assert.strictEqual(titleElem.children.length, 0, 'Title element must have 0 element children (text node only)');
    assert.strictEqual(titleElem.textContent.trim(), maliciousTitle, 'Title textContent must match the exact raw catalog title');
});

test('Normal Unicode & Vietnamese product titles are preserved cleanly in sticky bar', () => {
    const vnTitle = 'Giày Chạy Bộ Nam PAXIG Air Pegasus 40 - Đen/Trắng';
    const env = createTestEnvironment({ title: vnTitle });
    runScript(env);

    const stickyBar = env.body.querySelector('.paxig-sticky-buy-bar');
    const titleElem = stickyBar.querySelector('.paxig-sticky-buy-bar__title');

    assert.strictEqual(titleElem.textContent.trim(), vnTitle);
    assert.strictEqual(titleElem.children.length, 0);
});

test('Special characters & angle brackets in catalog title remain pure text without tag parsing', () => {
    const specialTitle = 'PAXIG ZoomX Vaporfly <Next% 3> "Special Edition" & More';
    const env = createTestEnvironment({ title: specialTitle });
    runScript(env);

    const stickyBar = env.body.querySelector('.paxig-sticky-buy-bar');
    const titleElem = stickyBar.querySelector('.paxig-sticky-buy-bar__title');

    assert.strictEqual(titleElem.children.length, 0, 'No XML/HTML tags should be created from angle brackets');
    assert.strictEqual(titleElem.textContent.trim(), specialTitle);
});

test('Preserves existing trusted WooCommerce price markup in sticky bar', () => {
    const wooPriceMarkup = '<del class="original"><span class="amount">2.500.000₫</span></del> <ins class="sale"><span class="amount">1.990.000₫</span></ins>';
    const env = createTestEnvironment({ priceHTML: wooPriceMarkup });
    runScript(env);

    const stickyBar = env.body.querySelector('.paxig-sticky-buy-bar');
    const priceElem = stickyBar.querySelector('.paxig-sticky-buy-bar__price');

    assert.ok(priceElem, 'Price container must exist');
    assert.strictEqual(priceElem.querySelector('del') !== null, true, 'Trusted del tag must be preserved');
    assert.strictEqual(priceElem.querySelector('ins') !== null, true, 'Trusted ins tag must be preserved');
});

test('Sticky buy button click triggers main buy button click delegation', () => {
    const env = createTestEnvironment();
    let mainButtonClicked = false;
    env.mainBuyBtn.click = () => { mainButtonClicked = true; };

    runScript(env);

    const stickyBar = env.body.querySelector('.paxig-sticky-buy-bar');
    const stickyBtn = stickyBar.querySelector('.paxig-sticky-buy-bar__btn');
    assert.ok(stickyBtn, 'Sticky buy button must exist');

    stickyBtn.click();
    assert.strictEqual(mainButtonClicked, true, 'Clicking sticky bar button must click main add to cart button');
});

test('Sticky buy bar is not initialized on desktop screen widths (> 900px)', () => {
    const env = createTestEnvironment({ innerWidth: 1200 });
    runScript(env);

    const stickyBar = env.body.querySelector('.paxig-sticky-buy-bar');
    assert.strictEqual(stickyBar, null, 'Sticky buy bar must not initialize when innerWidth > 900');
});
