<?php
/**
 * Internationalization (i18n) & Bicultural Support (EN / VI) for PAXIG's Shoes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load theme textdomain for gettext translations.
 */
function paxig_shoes_load_textdomain() {
	load_theme_textdomain( 'paxig-shoes', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'paxig_shoes_load_textdomain' );

/**
 * Detect current active language ('vi' or 'en').
 *
 * Precedence:
 * 1. Query parameter ?lang=vi or ?lang=en
 * 2. Cookie 'paxig_lang'
 * 3. Default: 'en'
 *
 * @return string 'vi' or 'en'
 */
function paxig_get_current_language() {
	if ( isset( $_GET['lang'] ) && is_string( $_GET['lang'] ) ) {
		$candidate = strtolower( trim( $_GET['lang'] ) );
		if ( in_array( $candidate, [ 'vi', 'en' ], true ) ) {
			return $candidate;
		}
	}

	if ( isset( $_COOKIE['paxig_lang'] ) && is_string( $_COOKIE['paxig_lang'] ) ) {
		$candidate = strtolower( trim( $_COOKIE['paxig_lang'] ) );
		if ( in_array( $candidate, [ 'vi', 'en' ], true ) ) {
			return $candidate;
		}
	}

	return 'en';
}

/**
 * Persist language selection in cookie when switching.
 */
function paxig_persist_language_cookie() {
	if ( isset( $_GET['lang'] ) && is_string( $_GET['lang'] ) ) {
		$candidate = strtolower( trim( $_GET['lang'] ) );
		if ( in_array( $candidate, [ 'vi', 'en' ], true ) ) {
			if ( ! headers_sent() ) {
				$path = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
				$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
				setcookie( 'paxig_lang', $candidate, time() + ( 86400 * 365 ), $path, $domain, is_ssl(), false );
			}
			$_COOKIE['paxig_lang'] = $candidate;
		}
	}
}
add_action( 'init', 'paxig_persist_language_cookie', 1 );

/**
 * Filter WordPress locale to match active language.
 */
function paxig_filter_locale( $locale ) {
	if ( is_admin() ) {
		return $locale;
	}

	$lang = paxig_get_current_language();
	return 'vi' === $lang ? 'vi' : 'en_US';
}
add_filter( 'locale', 'paxig_filter_locale', 20 );
add_filter( 'determine_locale', 'paxig_filter_locale', 20 );

/**
 * Filter HTML language attribute.
 */
function paxig_filter_language_attributes( $attributes ) {
	if ( is_admin() ) {
		return $attributes;
	}

	$lang = paxig_get_current_language();
	return 'vi' === $lang ? 'lang="vi"' : 'lang="en-US"';
}
add_filter( 'language_attributes', 'paxig_filter_language_attributes', 20 );

/**
 * Render sleek language switcher toggle (VI | EN).
 *
 * @param string $class Additional CSS classes.
 */
function paxig_render_language_switcher( $class = '' ) {
	$current_lang = paxig_get_current_language();

	// Preserve current URL parameters while updating lang
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
	$vi_url = add_query_arg( 'lang', 'vi', $request_uri );
	$en_url = add_query_arg( 'lang', 'en', $request_uri );
	?>
	<div class="paxig-lang-switch <?php echo esc_attr( $class ); ?>" role="group" aria-label="<?php echo esc_attr__( 'Language Switcher', 'paxig-shoes' ); ?>">
		<a
			href="<?php echo esc_url( $vi_url ); ?>"
			class="paxig-lang-btn <?php echo 'vi' === $current_lang ? 'is-active' : ''; ?>"
			hreflang="vi"
			title="Tiếng Việt"
			aria-pressed="<?php echo 'vi' === $current_lang ? 'true' : 'false'; ?>"
		>VI</a>
		<span class="paxig-lang-divider" aria-hidden="true">/</span>
		<a
			href="<?php echo esc_url( $en_url ); ?>"
			class="paxig-lang-btn <?php echo 'en' === $current_lang ? 'is-active' : ''; ?>"
			hreflang="en"
			title="English"
			aria-pressed="<?php echo 'en' === $current_lang ? 'true' : 'false'; ?>"
		>EN</a>
	</div>
	<?php
}
