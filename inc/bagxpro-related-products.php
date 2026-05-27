<?php
/**
 * Produits similaires / suggestions (fiche produit + pages enfant « nos-solutions »).
 */

function bagxpro_get_nos_solutions_parent_id() {
	static $parent_id = null;

	if ( null !== $parent_id ) {
		return $parent_id;
	}

	$parent_id = 0;
	$slug      = apply_filters( 'bagxpro_nos_solutions_parent_slug', 'nos-solutions' );
	$page      = get_page_by_path( $slug );

	if ( $page instanceof WP_Post ) {
		$parent_id = (int) $page->ID;
	}

	return $parent_id;
}

function bagxpro_is_nos_solutions_child_page( $post_id = 0 ) {
	if ( ! is_page( $post_id ) ) {
		return false;
	}

	$post = get_post( $post_id ? $post_id : get_queried_object_id() );
	if ( ! $post instanceof WP_Post || ! $post->post_parent ) {
		return false;
	}

	$parent_id = bagxpro_get_nos_solutions_parent_id();
	if ( $parent_id ) {
		return (int) $post->post_parent === $parent_id;
	}

	$parent_post = get_post( (int) $post->post_parent );

	return $parent_post instanceof WP_Post && 'nos-solutions' === $parent_post->post_name;
}

function bagxpro_should_show_related_products() {
	if ( is_singular( 'produit' ) ) {
		return true;
	}

	if ( bagxpro_is_nos_solutions_child_page() ) {
		return true;
	}

	return (bool) apply_filters( 'bagxpro_show_related_products', false );
}

function bagxpro_get_related_products_exclude_id() {
	if ( is_singular( 'produit' ) ) {
		return (int) get_queried_object_id();
	}

	return 0;
}

function bagxpro_render_related_products( $exclude_product_id = null ) {
	if ( null === $exclude_product_id ) {
		$exclude_product_id = bagxpro_get_related_products_exclude_id();
	}

	get_template_part(
		'template-parts/produit/related-products',
		null,
		array(
			'product_id' => max( 0, (int) $exclude_product_id ),
		)
	);
}

function bagxpro_output_related_products_before_footer() {
	if ( ! bagxpro_should_show_related_products() ) {
		return;
	}

	static $rendered = false;
	if ( $rendered ) {
		return;
	}

	$rendered = true;
	bagxpro_render_related_products();
}
add_action( 'get_footer', 'bagxpro_output_related_products_before_footer', 5 );
