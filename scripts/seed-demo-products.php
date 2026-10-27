<?php
/**
 * Seed a local PAXIG demo catalog through WooCommerce's product APIs.
 *
 * Run from the WordPress container:
 *   php /var/www/html/wp-content/themes/paxig-shoes/scripts/seed-demo-products.php --dry-run
 *   php /var/www/html/wp-content/themes/paxig-shoes/scripts/seed-demo-products.php --apply
 */

if ( PHP_SAPI !== 'cli' ) {
	header( 'HTTP/1.1 404 Not Found' );
	exit;
}

function paxig_seed_error( $message, $exit_code = 1 ) {
	fwrite( STDERR, 'PAXIG demo seeder: ' . $message . PHP_EOL );
	exit( $exit_code );
}

function paxig_seed_usage() {
	return "Usage: php scripts/seed-demo-products.php [--dry-run|--apply] [--limit=1..1000] [--allow-production] [--help]\n\n"
		. "Defaults to --dry-run. --apply downloads one distinct, reusable Wikimedia Commons image per product.\n"
		. "Existing products with a PAXIG-DEMO- SKU are skipped, so interrupted runs can be resumed.\n";
}

$options = array(
	'apply'            => false,
	'allow-production' => false,
	'limit'            => 1000,
);

foreach ( array_slice( $argv, 1 ) as $argument ) {
	if ( '--help' === $argument || '-h' === $argument ) {
		echo paxig_seed_usage();
		exit( 0 );
	}

	if ( '--apply' === $argument ) {
		$options['apply'] = true;
		continue;
	}

	if ( '--dry-run' === $argument ) {
		$options['apply'] = false;
		continue;
	}

	if ( '--allow-production' === $argument ) {
		$options['allow-production'] = true;
		continue;
	}

	if ( preg_match( '/^--limit=(\d+)$/', $argument, $matches ) ) {
		$options['limit'] = (int) $matches[1];
		continue;
	}

	paxig_seed_error( 'Unknown argument: ' . $argument . PHP_EOL . paxig_seed_usage() );
}

if ( $options['limit'] < 1 || $options['limit'] > 1000 ) {
	paxig_seed_error( '--limit must be between 1 and 1000.' );
}

$configured_root = getenv( 'PAXIG_WP_ROOT' );
$wp_load_candidates = array();
if ( $configured_root ) {
	$wp_load_candidates[] = rtrim( $configured_root, '/\\' ) . '/wp-load.php';
}
$wp_load_candidates[] = dirname( __DIR__, 4 ) . '/wp-load.php';
$wp_load_candidates[] = '/var/www/html/wp-load.php';

$wp_load = '';
foreach ( array_unique( $wp_load_candidates ) as $candidate ) {
	if ( is_file( $candidate ) ) {
		$wp_load = $candidate;
		break;
	}
}

if ( ! $wp_load ) {
	paxig_seed_error( 'Could not find wp-load.php. Run this script inside the WordPress container or set PAXIG_WP_ROOT.' );
}

require_once $wp_load;

if ( ! function_exists( 'wc_get_products' ) || ! class_exists( 'WC_Product_Simple' ) ) {
	paxig_seed_error( 'WooCommerce is not active.' );
}

function paxig_seed_is_local_site() {
	$host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	return in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true )
		|| (bool) preg_match( '/\.(test|local|localhost)$/', $host );
}

if ( $options['apply'] && 'production' === wp_get_environment_type() && ! paxig_seed_is_local_site() && ! $options['allow-production'] ) {
	paxig_seed_error( 'This WordPress site reports a production environment. Use a local/staging site or pass --allow-production intentionally.' );
}

$products = array(
	'categories' => array(
		'lifestyle'  => array( 'name' => 'Lifestyle' ),
		'running'    => array( 'name' => 'Running' ),
		'basketball' => array( 'name' => 'Basketball' ),
		'football'   => array( 'name' => 'Football' ),
		'training'   => array( 'name' => 'Training' ),
	),
	'models' => array(
		'Aero Dash', 'Arc Runner', 'Cloud Sprint', 'Court Pulse', 'Crosswind',
		'Daily Motion', 'Flex Route', 'Forward Pace', 'Metro Glide', 'Motion Grid',
		'Nova Court', 'Open Run', 'Rapid Form', 'Skyline Step', 'Sprint Current',
		'Stadium Flow', 'Street Echo', 'Stride Form', 'Swift Range', 'Urban Pace',
	),
	'colors' => array(
		array( 'name' => 'Red / Black', 'slug' => 'red-black' ),
		array( 'name' => 'White / Grey', 'slug' => 'neutral' ),
		array( 'name' => 'Lime / Black', 'slug' => 'lime-black' ),
		array( 'name' => 'Navy / White', 'slug' => 'navy-white' ),
		array( 'name' => 'Sand / Orange', 'slug' => 'sand-orange' ),
		array( 'name' => 'Blue / Grey', 'slug' => 'blue-grey' ),
		array( 'name' => 'Black / White', 'slug' => 'black-white' ),
		array( 'name' => 'Purple / Pink', 'slug' => 'purple-pink' ),
		array( 'name' => 'Olive / Cream', 'slug' => 'olive-cream' ),
		array( 'name' => 'Silver / Blue', 'slug' => 'silver-blue' ),
	),
	'genders' => array(
		array( 'name' => 'Men', 'slug' => 'men' ),
		array( 'name' => 'Women', 'slug' => 'women' ),
		array( 'name' => 'Unisex', 'slug' => 'unisex' ),
		array( 'name' => 'Kids', 'slug' => 'kids' ),
	),
);

$sku_prefix = 'PAXIG-DEMO-';
$existing_ids = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => '_sku',
				'value'   => $sku_prefix,
				'compare' => 'LIKE',
			),
		),
	)
);

$existing_skus = array();
foreach ( $existing_ids as $product_id ) {
	$existing_skus[ (string) get_post_meta( $product_id, '_sku', true ) ] = true;
}

$missing = array();
for ( $number = 1; $number <= $options['limit']; $number++ ) {
	$sku = $sku_prefix . str_pad( (string) $number, 5, '0', STR_PAD_LEFT );
	if ( empty( $existing_skus[ $sku ] ) ) {
		$missing[] = $number;
	}
}

if ( ! $missing ) {
	echo 'PAXIG demo seeder: nothing to create; all ' . $options['limit'] . ' requested SKUs already exist.' . PHP_EOL;
	exit( 0 );
}

if ( ! $options['apply'] ) {
	echo 'DRY RUN: ' . count( $missing ) . ' of ' . $options['limit'] . ' products would be created.' . PHP_EOL;
	echo 'Existing PAXIG demo products: ' . count( $existing_skus ) . '. No products, terms, or images were changed.' . PHP_EOL;
	foreach ( array_slice( $missing, 0, 3 ) as $number ) {
		$index       = $number - 1;
		$model       = $products['models'][ intdiv( $index, 50 ) ];
		$color_index = intdiv( $index % 50, 5 );
		$edition     = ( $index % 5 ) + 1;
		echo '  ' . $sku_prefix . str_pad( (string) $number, 5, '0', STR_PAD_LEFT ) . ' — ' . $model . ' ' . $products['colors'][ $color_index ]['name'] . ' Edition ' . $edition . PHP_EOL;
	}
	echo 'Pass --apply to write the products to WooCommerce.' . PHP_EOL;
	exit( 0 );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function paxig_seed_commons_user_agent() {
	$contact = getenv( 'PAXIG_WIKIMEDIA_CONTACT' );
	if ( ! $contact ) {
		$contact = home_url( '/' );
	}
	return 'PAXIG Demo Product Seeder/1.0 (' . sanitize_text_field( $contact ) . ')';
}

function paxig_seed_commons_request( $url, $args = array() ) {
	$defaults = array(
		'timeout'     => 30,
		'redirection' => 3,
		'headers'     => array(
			'User-Agent' => paxig_seed_commons_user_agent(),
			'Accept'     => 'application/json, image/*;q=0.9, */*;q=0.8',
		),
	);
	$args = array_replace_recursive( $defaults, $args );

	for ( $attempt = 0; $attempt < 4; $attempt++ ) {
		$response = wp_remote_get( $url, $args );
		if ( is_wp_error( $response ) ) {
			if ( 3 === $attempt ) {
				throw new RuntimeException( 'Wikimedia request failed: ' . $response->get_error_message() );
			}
			usleep( ( 1 << $attempt ) * 1000000 );
			continue;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		if ( 429 === $status || $status >= 500 ) {
			if ( 3 === $attempt ) {
				throw new RuntimeException( 'Wikimedia returned HTTP ' . $status . ' after retries.' );
			}
			$retry_after = (int) wp_remote_retrieve_header( $response, 'retry-after' );
			$delay       = $retry_after > 0 ? min( 30, $retry_after ) : ( 1 << $attempt );
			sleep( $delay );
			continue;
		}

		return $response;
	}

	throw new RuntimeException( 'Wikimedia request failed after retries.' );
}

function paxig_seed_commons_license_allowed( $license ) {
	$license = strtoupper( trim( wp_strip_all_tags( html_entity_decode( (string) $license, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) ) );
	if ( preg_match( '/\b(NC|ND)\b/', $license ) ) {
		return false;
	}
	return false !== strpos( $license, 'CC0' )
		|| false !== strpos( $license, 'PUBLIC DOMAIN' )
		|| (bool) preg_match( '/^PD(?:[\s_-]|$)/', $license )
		|| (bool) preg_match( '/^CC BY(?:-SA)?(?:\s|$)/', $license );
}

function paxig_seed_commons_image_info( $page ) {
	if ( empty( $page['imageinfo'][0] ) ) {
		return false;
	}

	$info     = $page['imageinfo'][0];
	$metadata = isset( $info['extmetadata'] ) && is_array( $info['extmetadata'] ) ? $info['extmetadata'] : array();
	$license  = isset( $metadata['LicenseShortName']['value'] ) ? wp_strip_all_tags( html_entity_decode( $metadata['LicenseShortName']['value'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : '';
	$mime     = isset( $info['mime'] ) ? strtolower( $info['mime'] ) : '';
	$width    = isset( $info['width'] ) ? (int) $info['width'] : 0;
	$height   = isset( $info['height'] ) ? (int) $info['height'] : 0;
	$artist   = isset( $metadata['Artist']['value'] ) ? wp_strip_all_tags( html_entity_decode( $metadata['Artist']['value'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : '';
	$title    = isset( $page['title'] ) ? (string) $page['title'] : '';
	$page_url = isset( $info['descriptionurl'] ) ? esc_url_raw( $info['descriptionurl'] ) : '';
	$thumb    = isset( $info['thumburl'] ) ? esc_url_raw( $info['thumburl'] ) : '';
	$license_url = isset( $metadata['LicenseUrl']['value'] ) ? esc_url_raw( wp_strip_all_tags( html_entity_decode( $metadata['LicenseUrl']['value'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) ) : '';

	if ( ! preg_match( '/^image\/(jpeg|png|webp)$/', $mime ) || min( $width, $height ) < 500 || ! paxig_seed_commons_license_allowed( $license ) ) {
		return false;
	}
	if ( ! paxig_seed_wikimedia_url_allowed( $thumb ) || ! paxig_seed_commons_page_url_allowed( $page_url ) || ! $title ) {
		return false;
	}
	if ( $license_url && ! paxig_seed_commons_license_url_allowed( $license_url ) ) {
		return false;
	}
	$license_url = $license_url ? set_url_scheme( $license_url, 'https' ) : '';

	$title = preg_replace( '/^File:/i', '', $title );
	return array(
		'page_id'     => isset( $page['pageid'] ) ? (int) $page['pageid'] : 0,
		'title'       => sanitize_text_field( $title ),
		'thumb_url'   => $thumb,
		'page_url'    => $page_url,
		'artist'      => sanitize_text_field( $artist ? $artist : 'Wikimedia Commons contributor' ),
		'license'     => sanitize_text_field( $license ),
		'license_url' => $license_url,
		'sha1'        => isset( $info['sha1'] ) ? sanitize_text_field( $info['sha1'] ) : '',
		'mime'        => $mime,
	);
}

function paxig_seed_wikimedia_url_allowed( $url ) {
	$parts = wp_parse_url( $url );
	return is_array( $parts )
		&& isset( $parts['scheme'], $parts['host'] )
		&& 'https' === strtolower( $parts['scheme'] )
		&& in_array( strtolower( $parts['host'] ), array( 'thumb.wikimedia.org', 'upload.wikimedia.org' ), true );
}

function paxig_seed_commons_page_url_allowed( $url ) {
	$parts = wp_parse_url( $url );
	return is_array( $parts )
		&& isset( $parts['scheme'], $parts['host'] )
		&& 'https' === strtolower( $parts['scheme'] )
		&& 'commons.wikimedia.org' === strtolower( $parts['host'] );
}

function paxig_seed_commons_license_url_allowed( $url ) {
	$parts = wp_parse_url( $url );
	return is_array( $parts )
		&& isset( $parts['scheme'], $parts['host'] )
		&& in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true )
		&& in_array( strtolower( $parts['host'] ), array( 'creativecommons.org', 'commons.wikimedia.org' ), true );
}

function paxig_seed_commons_manifest( $limit ) {
	$option_name = 'paxig_demo_commons_manifest_v1';
	$manifest    = get_option( $option_name, array() );
	if ( ! is_array( $manifest ) || empty( $manifest['items'] ) || ! isset( $manifest['query_index'] ) ) {
		$manifest = array(
			'items'       => array(),
			'seen_pages'  => array(),
			'seen_sha1'   => array(),
			'query_index' => 0,
			'continue'    => array(),
		);
	}

	$queries = array( 'sneaker', 'running shoes', 'basketball shoes', 'football boots', 'sports shoes', 'trainers' );
	$calls   = 0;
	while ( count( $manifest['items'] ) < $limit && $manifest['query_index'] < count( $queries ) && $calls < 200 ) {
		$params = array(
			'action'               => 'query',
			'generator'            => 'search',
			'gsrsearch'            => $queries[ $manifest['query_index'] ],
			'gsrnamespace'         => 6,
			'gsrlimit'             => 50,
			'prop'                 => 'imageinfo',
			'iiprop'               => 'url|extmetadata|size|mime|sha1',
			'iiextmetadatafilter'  => 'LicenseShortName|LicenseUrl|Artist',
			'iiurlwidth'           => 640,
			'format'               => 'json',
			'formatversion'        => 2,
		);
		if ( ! empty( $manifest['continue'] ) ) {
			$params = array_merge( $params, $manifest['continue'] );
		}

		$url      = add_query_arg( $params, 'https://commons.wikimedia.org/w/api.php' );
		$response = paxig_seed_commons_request( $url );
		$payload  = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $payload ) || ! isset( $payload['query']['pages'] ) ) {
			throw new RuntimeException( 'Wikimedia returned an unreadable image search response.' );
		}

		foreach ( $payload['query']['pages'] as $page ) {
			$image = paxig_seed_commons_image_info( $page );
			if ( ! $image || empty( $image['page_id'] ) || isset( $manifest['seen_pages'][ $image['page_id'] ] ) ) {
				continue;
			}
			if ( $image['sha1'] && isset( $manifest['seen_sha1'][ $image['sha1'] ] ) ) {
				continue;
			}

			$manifest['items'][] = $image;
			$manifest['seen_pages'][ $image['page_id'] ] = true;
			if ( $image['sha1'] ) {
				$manifest['seen_sha1'][ $image['sha1'] ] = true;
			}
		}

		$manifest['continue'] = isset( $payload['continue'] ) && is_array( $payload['continue'] ) ? $payload['continue'] : array();
		if ( ! $manifest['continue'] ) {
			$manifest['query_index']++;
		}
		if ( get_option( $option_name, false ) === false ) {
			add_option( $option_name, $manifest, '', false );
		} else {
			update_option( $option_name, $manifest, false );
		}

		$calls++;
		if ( count( $manifest['items'] ) < $limit ) {
			echo 'Wikimedia images found: ' . count( $manifest['items'] ) . ' / ' . $limit . PHP_EOL;
			usleep( 500000 );
		}
	}

	if ( count( $manifest['items'] ) < $limit ) {
		throw new RuntimeException( 'Wikimedia Commons returned only ' . count( $manifest['items'] ) . ' usable unique shoe images; ' . $limit . ' are required. No products were created.' );
	}

	return array_slice( $manifest['items'], 0, $limit );
}

function paxig_seed_commons_image_id( $candidate ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_paxig_commons_page_id',
			'meta_value'     => (string) $candidate['page_id'],
		)
	);
	if ( $existing ) {
		$attached_file = get_attached_file( $existing[0] );
		if ( $attached_file && is_file( $attached_file ) ) {
			return (int) $existing[0];
		}
	}

	$url = $candidate['thumb_url'];
	if ( ! paxig_seed_wikimedia_url_allowed( $url ) ) {
		throw new RuntimeException( 'The Wikimedia image URL was not an approved Wikimedia host.' );
	}

	$temp_file = wp_tempnam( 'paxig-commons-' . $candidate['page_id'] );
	if ( ! $temp_file ) {
		throw new RuntimeException( 'Could not create a temporary file for Wikimedia image ' . $candidate['page_id'] . '.' );
	}
	try {
		$response = paxig_seed_commons_request(
			$url,
			array(
				'timeout'  => 45,
				'stream'   => true,
				'filename' => $temp_file,
				'headers'  => array( 'Accept' => 'image/*' ),
			)
		);
	} catch ( Throwable $error ) {
		if ( is_file( $temp_file ) ) {
			@unlink( $temp_file );
		}
		throw $error;
	}
	$status = (int) wp_remote_retrieve_response_code( $response );
	$size   = is_file( $temp_file ) ? (int) filesize( $temp_file ) : 0;
	$image_info = $size > 0 && $size <= 8388608 ? @getimagesize( $temp_file ) : false;
	if ( $status < 200 || $status >= 300 || ! $image_info || ! in_array( $image_info['mime'], array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) {
		if ( is_file( $temp_file ) ) {
			@unlink( $temp_file );
		}
		throw new RuntimeException( 'Could not download a valid image for Wikimedia file ' . $candidate['page_id'] . '.' );
	}

	$extensions = array( 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp' );
	$base_name  = substr( sanitize_title( $candidate['title'] ), 0, 80 );
	$file       = array(
		'name'     => 'commons-' . $candidate['page_id'] . '-' . ( $base_name ? $base_name : 'shoe' ) . '.' . $extensions[ $image_info['mime'] ],
		'type'     => $image_info['mime'],
		'tmp_name' => $temp_file,
		'error'    => UPLOAD_ERR_OK,
		'size'     => $size,
	);
	$credit = sprintf(
		'Image "%s" by %s, licensed under %s. Source: %s. The catalog image is resized from the original.',
		$candidate['title'],
		$candidate['artist'],
		$candidate['license'],
		$candidate['page_url']
	);
	$attachment_id = media_handle_sideload(
		$file,
		0,
		$candidate['title'],
		array(
			'post_content' => $credit,
			'post_excerpt' => $credit,
		)
	);
	if ( is_file( $temp_file ) ) {
		@unlink( $temp_file );
	}
	if ( is_wp_error( $attachment_id ) ) {
		throw new RuntimeException( 'Could not add Wikimedia image to Media Library: ' . $attachment_id->get_error_message() );
	}

	update_post_meta( $attachment_id, '_paxig_commons_page_id', (string) $candidate['page_id'] );
	update_post_meta( $attachment_id, '_paxig_commons_title', $candidate['title'] );
	update_post_meta( $attachment_id, '_paxig_commons_author', $candidate['artist'] );
	update_post_meta( $attachment_id, '_paxig_commons_license', $candidate['license'] );
	update_post_meta( $attachment_id, '_paxig_commons_license_url', $candidate['license_url'] );
	update_post_meta( $attachment_id, '_paxig_commons_page_url', $candidate['page_url'] );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Demo shoe photo: ' . $candidate['title'] );
	usleep( 250000 );
	return (int) $attachment_id;
}

function paxig_seed_term( $slug, $name, $taxonomy ) {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	if ( $term && ! is_wp_error( $term ) ) {
		return $term;
	}

	$inserted = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
	if ( is_wp_error( $inserted ) ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		}
		throw new RuntimeException( 'Could not create term ' . $name . ' in ' . $taxonomy . ': ' . $inserted->get_error_message() );
	}

	$term = get_term( (int) $inserted['term_id'], $taxonomy );
	if ( ! $term || is_wp_error( $term ) ) {
		throw new RuntimeException( 'Could not reload term ' . $name . ' in ' . $taxonomy . '.' );
	}
	return $term;
}

function paxig_seed_category_ids( $specifications ) {
	$ids = array();
	foreach ( $specifications as $slug => $category ) {
		$ids[ $slug ] = (int) paxig_seed_term( $slug, $category['name'], 'product_cat' )->term_id;
	}
	$ids['new-arrivals'] = (int) paxig_seed_term( 'new-arrivals', 'New Arrivals', 'product_cat' )->term_id;
	$ids['best-sellers'] = (int) paxig_seed_term( 'best-sellers', 'Best Sellers', 'product_cat' )->term_id;
	return $ids;
}

function paxig_seed_attribute( $slug, $label ) {
	$attribute_id = 0;
	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		if ( $slug === $attribute->attribute_name ) {
			$attribute_id = (int) $attribute->attribute_id;
			break;
		}
	}

	if ( ! $attribute_id ) {
		$attribute_id = wc_create_attribute(
			array(
				'name'         => $label,
				'slug'         => $slug,
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			)
		);
		if ( is_wp_error( $attribute_id ) ) {
			throw new RuntimeException( 'Could not create WooCommerce attribute ' . $label . ': ' . $attribute_id->get_error_message() );
		}
		$attribute_id = (int) $attribute_id;
	}

	$taxonomy = wc_attribute_taxonomy_name( $slug );
	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy(
			$taxonomy,
			array( 'product' ),
			array(
				'hierarchical'          => false,
				'update_count_callback' => '_update_post_term_count',
				'labels'                => array(
					'name'          => $label,
					'singular_name' => $label,
				),
				'show_ui'               => true,
				'query_var'             => true,
				'rewrite'               => false,
				'public'                => true,
			)
		);
	}

	return array(
		'id'       => $attribute_id,
		'name'     => $taxonomy,
		'slug'     => $slug,
		'label'    => $label,
	);
}

function paxig_seed_product_attribute( $attribute_spec, $term_name, $position ) {
	$attribute = new WC_Product_Attribute();
	$attribute->set_id( $attribute_spec['id'] );
	$attribute->set_name( $attribute_spec['name'] );
	$attribute->set_options( array( $term_name ) );
	$attribute->set_position( $position );
	$attribute->set_visible( true );
	$attribute->set_variation( false );
	return $attribute;
}

// Gather and validate the full image set before creating any products or terms.
$image_candidates = paxig_seed_commons_manifest( $options['limit'] );

$category_ids = paxig_seed_category_ids( $products['categories'] );
$gender_attribute = paxig_seed_attribute( 'gender', 'Gender' );
$sport_attribute  = paxig_seed_attribute( 'sport', 'Sport' );
$color_attribute  = paxig_seed_attribute( 'color', 'Color' );

$gender_terms = array();
foreach ( $products['genders'] as $gender ) {
	$gender_terms[] = paxig_seed_term( $gender['slug'], $gender['name'], $gender_attribute['name'] )->name;
}

$color_terms = array();
foreach ( $products['colors'] as $color ) {
	$color_terms[] = paxig_seed_term( $color['slug'], $color['name'], $color_attribute['name'] )->name;
}

$sport_terms = array();
foreach ( $products['categories'] as $slug => $category ) {
	$sport_terms[ $slug ] = paxig_seed_term( $slug, $category['name'], $sport_attribute['name'] )->name;
}

$created = 0;
foreach ( $missing as $number ) {
	$index       = $number - 1;
	$category_key = array_keys( $products['categories'] )[ $index % count( $products['categories'] ) ];
	$category     = $products['categories'][ $category_key ];
	$model        = $products['models'][ intdiv( $index, 50 ) ];
	$color_index  = intdiv( $index % 50, 5 );
	$color        = $products['colors'][ $color_index ];
	$edition      = ( $index % 5 ) + 1;
	$sku          = $sku_prefix . str_pad( (string) $number, 5, '0', STR_PAD_LEFT );
	$name         = 'PAXIG ' . $model . ' ' . $color['name'] . ' Edition ' . $edition;
	$gender       = $products['genders'][ $index % count( $products['genders'] ) ]['name'];
	$regular      = 650000 + ( ( $index * 7919 ) % 2750000 );
	$regular      = (int) ( round( $regular / 10000 ) * 10000 );
	$stock        = 8 + ( ( $index * 37 ) % 93 );

	$product = new WC_Product_Simple();
	$product->set_name( $name );
	$product->set_slug( sanitize_title( $name . '-' . $sku ) );
	$product->set_sku( $sku );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_regular_price( (string) $regular );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( $stock );
	$product->set_stock_status( 'instock' );
	$product->set_backorders( 'no' );
	$product->set_weight( '0.8' );
	$product->set_category_ids(
		array_values(
			array_filter(
				array(
					$category_ids[ $category_key ],
					$number > 900 ? $category_ids['new-arrivals'] : 0,
					0 === $number % 25 ? $category_ids['best-sellers'] : 0,
				)
			)
		)
	);
	$image_candidate = $image_candidates[ $number - 1 ];
	$image_id        = paxig_seed_commons_image_id( $image_candidate );
	$product->set_image_id( $image_id );
	$product->set_featured( 0 === $number % 20 );
	$product->set_short_description( $category['name'] . ' footwear in the ' . $color['name'] . ' colorway.' );
	$product->set_description( 'A PAXIG demo ' . strtolower( $category['name'] ) . ' shoe in the ' . $color['name'] . ' colorway. Product details, stock, and prices are synthetic catalog data for preview.' );
	$product->set_attributes(
		array(
			paxig_seed_product_attribute( $gender_attribute, $gender, 0 ),
			paxig_seed_product_attribute( $sport_attribute, $sport_terms[ $category_key ], 1 ),
			paxig_seed_product_attribute( $color_attribute, $color['name'], 2 ),
		)
	);
	if ( 0 === $number % 10 ) {
		$product->set_sale_price( (string) ( $regular - (int) round( $regular * 0.15 / 1000 ) * 1000 ) );
	}
	$product->update_meta_data( '_paxig_demo_seed', 'demo-catalog-v1' );
	$product->update_meta_data( '_paxig_demo_image_title', $image_candidate['title'] );
	$product->update_meta_data( '_paxig_demo_image_author', $image_candidate['artist'] );
	$product->update_meta_data( '_paxig_demo_image_license', $image_candidate['license'] );
	$product->update_meta_data( '_paxig_demo_image_license_url', $image_candidate['license_url'] );
	$product->update_meta_data( '_paxig_demo_image_source_url', $image_candidate['page_url'] );
	$product_id = $product->save();
	if ( ! $product_id ) {
		throw new RuntimeException( 'WooCommerce could not save product ' . $sku . '.' );
	}
	wp_update_post(
		array(
			'ID'          => $image_id,
			'post_parent' => $product_id,
		)
	);
	$created++;

	if ( 0 === $created % 100 ) {
		echo 'Created ' . $created . ' / ' . count( $missing ) . ' products...' . PHP_EOL;
	}
}

echo 'PAXIG demo seeder: created ' . $created . ' WooCommerce products, each with a distinct Wikimedia Commons image and stored attribution.' . PHP_EOL;
echo 'Reruns skip existing ' . $sku_prefix . ' SKUs.' . PHP_EOL;
