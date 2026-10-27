const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../assets/js/wishlist.js'), 'utf8');

class FakeClassList {
  constructor() { this.values = new Set(); }
  add(...values) { values.forEach((value) => this.values.add(value)); }
  remove(...values) { values.forEach((value) => this.values.delete(value)); }
  contains(value) { return this.values.has(value); }
  toggle(value, force) {
    const shouldAdd = force === undefined ? !this.contains(value) : force;
    if (shouldAdd) this.add(value); else this.remove(value);
    return shouldAdd;
  }
}

class FakeElement {
  constructor(tagName = 'div', classes = []) {
    this.tagName = tagName.toUpperCase();
    this.classList = new FakeClassList();
    classes.forEach((value) => this.classList.add(value));
    this.dataset = {};
    this.attributes = new Map();
    this.children = [];
    this.listeners = new Map();
    this.style = {};
    this.parentElement = null;
    this.open = false;
    this._text = '';
    this._html = '';
    this.innerHTMLWrites = [];
  }
  set textContent(value) { this._text = String(value); this.children = []; }
  get textContent() { return this._text + this.children.map((child) => child.textContent).join(''); }
  set innerHTML(value) { this._html = String(value); this.innerHTMLWrites.push(this._html); this.children = []; }
  get innerHTML() { return this._html; }
  append(...nodes) {
    for (const node of nodes) {
      node.parentElement = this;
      this.children.push(node);
    }
  }
  replaceChildren(...nodes) { this.children = []; this._html = ''; this.append(...nodes); }
  setAttribute(name, value) { this.attributes.set(name, String(value)); }
  getAttribute(name) { return this.attributes.get(name) ?? null; }
  addEventListener(name, callback) {
    if (!this.listeners.has(name)) this.listeners.set(name, []);
    this.listeners.get(name).push(callback);
  }
  dispatch(name, target = this) {
    const event = {
      target,
      preventDefault() {},
      stopPropagation() {},
    };
    for (const callback of this.listeners.get(name) || []) callback(event);
  }
  closest(selector) {
    const className = selector.startsWith('.') ? selector.slice(1) : null;
    let node = this;
    while (node) {
      if (className && node.classList.contains(className)) return node;
      node = node.parentElement;
    }
    return null;
  }
  querySelector(selector) { return this.queryMap?.get(selector) || null; }
  focus() {}
  showModal() { this.open = true; }
  close() { this.open = false; this.dispatch('close'); }
}

class FakeDocument extends FakeElement {
  constructor({ buttonIds = [] } = {}) {
    super('#document');
    this.body = new FakeElement('body');
    this.header = new FakeElement('button', ['paxig-header-wishlist']);
    this.buttons = buttonIds.map((id) => {
      const button = new FakeElement('button', ['paxig-wishlist-btn']);
      button.dataset.productId = String(id);
      button.setAttribute('aria-pressed', 'false');
      return button;
    });
    this.counts = [new FakeElement('span', ['paxig-wishlist-count'])];
    this.drawer = new FakeElement('dialog', ['paxig-wishlist-drawer']);
    this.content = new FakeElement('div', ['paxig-wishlist-drawer-content']);
    this.empty = new FakeElement('div', ['paxig-wishlist-empty']);
    this.items = new FakeElement('div', ['paxig-wishlist-drawer-items']);
    this.closeButton = new FakeElement('button', ['paxig-drawer-close']);
    this.content.append(this.empty, this.items);
    this.drawer.queryMap = new Map([
      ['.paxig-wishlist-drawer-content', this.content],
      ['.paxig-wishlist-empty', this.empty],
      ['.paxig-wishlist-drawer-items', this.items],
      ['.paxig-drawer-close', this.closeButton],
    ]);
  }
  querySelector(selector) {
    if (selector === '.paxig-header-wishlist') return this.header;
    if (selector === '#paxig-wishlist-drawer') return this.drawer;
    return null;
  }
  getElementById(id) { return id === 'paxig-wishlist-drawer' ? this.drawer : null; }
  querySelectorAll(selector) {
    if (selector === '.paxig-wishlist-btn') return this.buttons;
    if (selector === '.paxig-wishlist-count') return this.counts;
    if (selector === '.paxig-home-product') return [];
    return [];
  }
  createElement(tagName) { return new FakeElement(tagName); }
  fireReady() { this.dispatch('DOMContentLoaded', this); }
}

class FakeFormData {
  constructor() { this.values = new Map(); }
  append(key, value) {
    if (!this.values.has(key)) this.values.set(key, []);
    this.values.get(key).push(String(value));
  }
}

function makeResponse(data, success = true) {
  return { ok: true, json: async () => ({ success, data }) };
}

function boot({
  ids = [],
  buttonIds = [],
  auth = false,
  userIds = [],
  storage = new Map(),
  fetcher = async () => makeResponse({ saved_ids: [], items: [] }),
} = {}) {
  const document = new FakeDocument({ buttonIds });
  const fetchCalls = [];
  const fetch = (url, options) => {
    fetchCalls.push(options);
    return fetcher(url, options, fetchCalls.length - 1);
  };
  const localStorage = {
    getItem(key) { return storage.has(key) ? storage.get(key) : null; },
    setItem(key, value) { storage.set(key, String(value)); },
  };
  const context = {
    document,
    window: {
      location: { href: 'https://shop.example/favorites/', origin: 'https://shop.example' },
      paxigWishlist: {
        ajax_url: '/admin-ajax.php', nonce: 'test-nonce', is_logged_in: auth, user_ids: userIds,
      },
    },
    localStorage,
    FormData: FakeFormData,
    URL,
    fetch,
    console,
  };
  vm.runInNewContext(source, context, { filename: 'wishlist.js' });
  document.fireReady();
  return { document, storage, fetchCalls, context };
}

function savedIds(storage) {
  return JSON.parse(storage.get('paxig_wishlist') || '[]');
}

test('guest removals persist in guest-only localStorage across reload', () => {
  const storage = new Map([['paxig_wishlist', '[5,6]']]);
  const first = boot({ storage, buttonIds: [5, 6] });
  first.document.dispatch('click', first.document.buttons[0]);
  assert.deepEqual(savedIds(storage), [6]);
  assert.equal(first.document.counts[0].textContent, '1');

  const reloaded = boot({ storage, buttonIds: [5, 6] });
  assert.equal(reloaded.document.buttons[0].getAttribute('aria-pressed'), 'false');
  assert.equal(reloaded.document.buttons[1].getAttribute('aria-pressed'), 'true');
  assert.equal(reloaded.document.counts[0].textContent, '1');
});

test('authenticated state comes only from the current account snapshot, never shared guest storage', async () => {
  const storage = new Map([['paxig_wishlist', '[101]']]);
  const first = boot({ auth: '1', userIds: [202], storage, buttonIds: [101, 202] });
  assert.equal(first.document.buttons[0].getAttribute('aria-pressed'), 'false');
  assert.equal(first.document.buttons[1].getAttribute('aria-pressed'), 'true');
  assert.equal(storage.get('paxig_wishlist'), '[101]');

  first.document.dispatch('click', first.document.buttons[1]);
  await new Promise((resolve) => setImmediate(resolve));
  assert.deepEqual(first.fetchCalls[0].body.values.get('product_ids[]') || [], []);

  const second = boot({ auth: '1', userIds: [303], storage, buttonIds: [101, 303] });
  assert.equal(second.document.buttons[0].getAttribute('aria-pressed'), 'false');
  assert.equal(second.document.buttons[1].getAttribute('aria-pressed'), 'true');
  assert.equal(storage.get('paxig_wishlist'), '[101]');
});

test('WordPress anonymous localization uses guest-only storage', () => {
  const storage = new Map([['paxig_wishlist', '[5,6]']]);
  const app = boot({ auth: '', userIds: [202], storage, buttonIds: [5, 6, 202] });

  assert.equal(app.document.buttons[0].getAttribute('aria-pressed'), 'true');
  assert.equal(app.document.buttons[1].getAttribute('aria-pressed'), 'true');
  assert.equal(app.document.buttons[2].getAttribute('aria-pressed'), 'false');
  assert.equal(storage.get('paxig_wishlist'), '[5,6]');
});

test('string false is not treated as an authenticated localization value', () => {
  const storage = new Map([['paxig_wishlist', '[5]']]);
  const app = boot({ auth: 'false', userIds: [202], storage, buttonIds: [5, 202] });

  assert.equal(app.document.buttons[0].getAttribute('aria-pressed'), 'true');
  assert.equal(app.document.buttons[1].getAttribute('aria-pressed'), 'false');
  assert.equal(storage.get('paxig_wishlist'), '[5]');
});

test('stale detail responses cannot replace the newest wishlist rendering', async () => {
  const pending = [];
  const fetcher = () => new Promise((resolve) => pending.push(resolve));
  const app = boot({ storage: new Map([['paxig_wishlist', '[1,2]']]), buttonIds: [1, 2], fetcher });
  app.document.header.dispatch('click');
  app.document.dispatch('click', app.document.buttons[0]);
  assert.equal(pending.length, 2);

  pending[1](makeResponse({ items: [{ id: 2, name: 'Newest', permalink: 'https://shop.example/p/2' }] }));
  await new Promise((resolve) => setImmediate(resolve));
  pending[0](makeResponse({ items: [{ id: 1, name: 'Stale', permalink: 'https://shop.example/p/1' }] }));
  await new Promise((resolve) => setImmediate(resolve));
  assert.match(app.document.items.textContent, /Newest/);
  assert.doesNotMatch(app.document.items.textContent, /Stale/);
});

test('detail errors are visible and product title/permalink are rendered safely', async () => {
  const app = boot({
    storage: new Map([['paxig_wishlist', '[9]']]),
    fetcher: async () => makeResponse({ items: [{
      id: 9,
      name: '<img src=x onerror=alert(1)>',
      permalink: 'javascript:alert(1)',
      price_html: '<b>$10</b>',
    }] }),
  });
  app.document.header.dispatch('click');
  await new Promise((resolve) => setImmediate(resolve));
  assert.match(app.document.items.textContent, /<img src=x onerror=alert\(1\)>/);
  assert.equal(app.document.items.innerHTMLWrites.some((html) => html.includes('onerror')), false);
  const links = [];
  const walk = (node) => {
    if (node.tagName === 'A') links.push(node);
    node.children.forEach(walk);
  };
  walk(app.document.items);
  assert.ok(links.length > 0);
  assert.ok(links.every((link) => !String(link.href || link.getAttribute('href')).startsWith('javascript:')));
});

test('detail requests are bounded and failed fetches show an error', async () => {
  const app = boot({
    storage: new Map([['paxig_wishlist', JSON.stringify(Array.from({ length: 150 }, (_, i) => i + 1))]]),
    fetcher: async () => { throw new Error('offline'); },
  });
  app.document.header.dispatch('click');
  await new Promise((resolve) => setImmediate(resolve));
  const ids = app.fetchCalls[0].body.values.get('product_ids[]') || [];
  assert.ok(ids.length <= 100);
  assert.match(app.document.items.textContent, /could not load|unable to load|try again/i);
});

test('failed removal while drawer is open remains visible after an empty-state render', async () => {
  const storage = new Map([['paxig_wishlist', '[77]']]);
  const app = boot({
    auth: true,
    userIds: [88],
    buttonIds: [88],
    storage,
    fetcher: async (_url, options) => {
      const action = options.body.values.get('action')[0];
      if (action === 'paxig_sync_user_wishlist') throw new Error('offline');
      return makeResponse({ items: [] });
    },
  });
  app.document.header.dispatch('click');
  await new Promise((resolve) => setImmediate(resolve));
  app.document.dispatch('click', app.document.buttons[0]);
  await new Promise((resolve) => setImmediate(resolve));
  await new Promise((resolve) => setImmediate(resolve));
  const error = app.document.content.children.find((child) => child.classList.contains('paxig-wishlist-persistence-error'));
  assert.ok(error);
  assert.match(error.textContent, /could not sync favorites/i);
  assert.equal(app.document.drawer.open, true);
  assert.equal(app.document.empty.style.display, 'flex', 'Removing the final favorite must render the empty state.');
  assert.equal(app.document.items.children.some((child) => child.classList.contains('paxig-wishlist-detail-error')), false);
  assert.equal(storage.get('paxig_wishlist'), '[77]');

  app.document.header.dispatch('click');
  app.document.header.dispatch('click');
  assert.match(error.textContent, /could not sync favorites/i, 'A subsequent empty render must not clear the persistence warning.');
});

test('successful detail response does not clear a failed account persistence warning', async () => {
  let finishDetails;
  const app = boot({
    auth: true,
    userIds: [],
    buttonIds: [88],
    fetcher: async (_url, options) => {
      const action = options.body.values.get('action')[0];
      if (action === 'paxig_sync_user_wishlist') throw new Error('offline');
      return new Promise((resolve) => { finishDetails = resolve; });
    },
  });
  app.document.dispatch('click', app.document.buttons[0]);
  await new Promise((resolve) => setImmediate(resolve));
  await new Promise((resolve) => setImmediate(resolve));
  const error = app.document.content.children.find((child) => child.classList.contains('paxig-wishlist-persistence-error'));
  assert.ok(error);
  assert.equal(app.document.drawer.open, true, 'A sync failure must expose its alert in the drawer.');
  finishDetails(makeResponse({ items: [{ id: 88, name: 'Shoe', permalink: 'https://shop.example/p/88' }] }));
  await new Promise((resolve) => setImmediate(resolve));
  assert.match(error.textContent, /could not sync favorites/i);
  assert.match(app.document.items.textContent, /Shoe/);
  assert.equal(app.document.items.children.some((child) => child.classList.contains('paxig-wishlist-detail-error')), false);
});

test('VND price entities are rendered as decoded text', async () => {
  const app = boot({
    storage: new Map([['paxig_wishlist', '[9]']]),
    fetcher: async () => makeResponse({ items: [{
      id: 9,
      name: 'Shoe',
      permalink: 'https://shop.example/p/9',
      price: '9.000\u00a0₫',
    }] }),
  });
  app.document.header.dispatch('click');
  await new Promise((resolve) => setImmediate(resolve));
  assert.match(app.document.items.textContent, /9\.000\u00a0₫/);
  assert.doesNotMatch(app.document.items.textContent, /&nbsp;|&#8363;/);
});
