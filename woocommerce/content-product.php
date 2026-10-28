<?php
/**
 * The template for displaying product content within loops
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

get_template_part( 'template-parts/product-card', null, [ 'product' => $product ] );
