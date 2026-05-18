<?php
/**
 * ACF — Grille liste produits : intro (options) + description carte par produit.
 *
 * @package bagxpro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'bagxpro_register_acf_list_products' );
function bagxpro_register_acf_list_products() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page(
			array(
				'page_title'  => __( 'Grille « Nos produits »', 'bagxpro' ),
				'menu_title'  => __( 'Grille produits', 'bagxpro' ),
				'menu_slug'   => 'bagxpro-list-products',
				'parent_slug' => 'themes.php',
				'capability'  => 'manage_options',
				'redirect'    => false,
				'position'    => 61,
			)
		);
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_bagxpro_list_products_options',
			'title'    => __( 'Textes colonne gauche (archive + défaut)', 'bagxpro' ),
			'fields'   => array(
				array(
					'key'          => 'field_bagxpro_lp_opt_title',
					'label'        => __( 'Titre', 'bagxpro' ),
					'name'         => 'bagxpro_lp_options_intro_title',
					'type'         => 'text',
					'instructions' => __( 'Utilisé sur l’archive produits et comme valeur par défaut si aucune page ne surcharge la grille.', 'bagxpro' ),
				),
				array(
					'key'          => 'field_bagxpro_lp_opt_text',
					'label'        => __( 'Texte descriptif', 'bagxpro' ),
					'name'         => 'bagxpro_lp_options_intro_text',
					'type'         => 'textarea',
					'rows'         => 4,
					'instructions' => __( 'Sauts de ligne autorisés. Affiché sous le titre à gauche de la grille.', 'bagxpro' ),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'bagxpro-list-products',
					),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_bagxpro_product_card',
			'title'    => __( 'Affichage en liste / grille', 'bagxpro' ),
			'fields'   => array(
				array(
					'key'          => 'field_bagxpro_product_card_desc',
					'label'        => __( 'Description courte (carte)', 'bagxpro' ),
					'name'         => 'bagxpro_product_card_description',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => __( 'Texte sous le titre sur les grilles (accueil, archive…). Si vide : extrait ou début du contenu.', 'bagxpro' ),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'produit',
					),
				),
			),
			'position' => 'side',
			'style'    => 'default',
		)
	);
}
