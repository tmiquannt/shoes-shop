/* Lucide Static 1.63.0: PHP and dynamic UI share one locally hosted SVG sprite. */
(() => {
    const spriteUrl = window.paxigIconConfig?.spriteUrl;
    if (!spriteUrl) return;
    const namespace = 'http://www.w3.org/2000/svg';
    const hydrate = root => {
        const passwordToggles = root.matches?.('.show-password-input') ? [root] : [];
        passwordToggles.push(...(root.querySelectorAll?.('.show-password-input') || []));
        passwordToggles.forEach(button => {
            if (button.classList.contains('paxig-password-toggle')) return;
            button.classList.add('paxig-password-toggle');
            ['eye', 'eye-off'].forEach(name => {
                const icon = document.createElement('span');
                icon.className = 'paxig-password-' + name;
                icon.setAttribute('data-paxig-icon', name);
                icon.setAttribute('data-icon-size', '20');
                icon.setAttribute('aria-hidden', 'true');
                button.append(icon);
            });
        });
        const placeholders = root.matches?.('[data-paxig-icon]') ? [root] : [];
        placeholders.push(...(root.querySelectorAll?.('[data-paxig-icon]') || []));
        placeholders.forEach(placeholder => {
            const name = placeholder.getAttribute('data-paxig-icon');
            if (!/^[a-z][a-z0-9-]*$/.test(name || '')) return;
            const size = Math.max(10, Math.min(64, Number(placeholder.getAttribute('data-icon-size')) || 20));
            const svg = document.createElementNS(namespace, 'svg');
            const attributes = {
                class: 'paxig-icon ' + (placeholder.getAttribute('class') || ''), width: size, height: size, viewBox: '0 0 24 24',
                fill: 'none', stroke: 'currentColor', 'stroke-width': 2,
                'stroke-linecap': 'round', 'stroke-linejoin': 'round',
                'aria-hidden': 'true', focusable: 'false', 'pointer-events': 'none',
            };
            Object.entries(attributes).forEach(([key, value]) => svg.setAttribute(key, String(value)));
            const use = document.createElementNS(namespace, 'use');
            use.setAttribute('href', spriteUrl + '#' + name);
            svg.append(use);
            placeholder.replaceWith(svg);
        });
    };
    document.addEventListener('DOMContentLoaded', () => {
        hydrate(document);
        new MutationObserver(records => records.forEach(record => record.addedNodes.forEach(node => {
            if (node.nodeType === 1) hydrate(node);
        }))).observe(document.body, {childList: true, subtree: true});
    });
})();
