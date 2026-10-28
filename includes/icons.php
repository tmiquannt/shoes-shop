<?php
/** Locally hosted Lucide Static 1.63.0 sprite; see assets/vendor/lucide/LICENSE. */
defined('ABSPATH') || exit;

function paxig_icon($name, $size = 24, $class = '') {
    if (!is_string($name) || !preg_match('/^[a-z][a-z0-9-]*$/D', $name)) return '';
    $size = max(10, min(64, (int) $size));
    $url = get_template_directory_uri() . '/assets/vendor/lucide/sprite.svg#' . $name;
    return '<svg class="paxig-icon ' . esc_attr($class) . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" pointer-events="none" aria-hidden="true" focusable="false"><use href="' . esc_url($url) . '"></use></svg>';
}

/** Keep existing translation keys while replacing their decorative UI glyphs. */
function paxig_icon_text($text) {
    return strtr($text, [
        '→' => paxig_icon('arrow-right', 18),
        '←' => paxig_icon('arrow-left', 18),
        '↗' => paxig_icon('arrow-up-right', 18),
        '✓' => paxig_icon('check', 18),
    ]);
}

function paxig_disclosure_icons() {
    return paxig_icon('plus', 18, 'paxig-disclosure-plus')
        . paxig_icon('minus', 18, 'paxig-disclosure-minus');
}

add_action('wp_enqueue_scripts', function() {
    $path = '/assets/js/icons.js';
    wp_enqueue_script('paxig-icons', get_template_directory_uri() . $path, [], filemtime(get_template_directory() . $path), false);
    wp_localize_script('paxig-icons', 'paxigIconConfig', [
        'spriteUrl' => get_template_directory_uri() . '/assets/vendor/lucide/sprite.svg',
    ]);
}, 5);
