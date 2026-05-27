<?php
/**
 * Sidebar configurateur : liste des produits (CPT produit).
 */

function bagxpro_configurator_sidebar_query_args() {
	return apply_filters(
		'bagxpro_configurator_sidebar_query_args',
		array(
			'post_type'              => 'produit',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'date',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
}

function bagxpro_should_render_configurator_sidebar() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}

	return true;
}

function bagxpro_render_configurator_sidebar() {
	if ( ! bagxpro_should_render_configurator_sidebar() ) {
		return;
	}

	$bagxpro_products = new WP_Query( bagxpro_configurator_sidebar_query_args() );
	$bagxpro_fallback = get_stylesheet_directory_uri() . '/images/product.png';

	get_template_part(
		'template-parts/general/configurator-sidebar',
		null,
		array(
			'query'        => $bagxpro_products,
			'fallback_img' => $bagxpro_fallback,
		)
	);
}
