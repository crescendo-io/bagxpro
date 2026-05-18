<?php
/**
 * Champs ACF — Page d’accueil (page définie comme page de front).
 *
 * @package bagxpro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ID de la page utilisée comme page de front (Réglages → Lecture).
 *
 * @return int
 */
function bagxpro_get_front_page_id() {
	if ( 'page' === get_option( 'show_on_front' ) ) {
		return (int) get_option( 'page_on_front' );
	}
	return (int) get_queried_object_id();
}

add_action( 'acf/init', 'bagxpro_register_acf_front_page_fields' );
function bagxpro_register_acf_front_page_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_bagxpro_home',
			'title'                 => __( 'Accueil — contenus', 'bagxpro' ),
			'fields'                => array(
				array(
					'key'   => 'field_bagxpro_home_tab_hero',
					'label' => __( 'Hero', 'bagxpro' ),
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_bagxpro_home_hero_bg',
					'label'         => __( 'Image de fond', 'bagxpro' ),
					'name'          => 'home_hero_background',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'large',
					'library'       => 'all',
				),
				array(
					'key'          => 'field_bagxpro_home_hero_title',
					'label'        => __( 'Titre principal', 'bagxpro' ),
					'name'         => 'home_hero_title',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => __( 'Sauts de ligne autorisés.', 'bagxpro' ),
				),
				array(
					'key'   => 'field_bagxpro_home_hero_lead',
					'label' => __( 'Sous-titre / accroche', 'bagxpro' ),
					'name'  => 'home_hero_lead',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'   => 'field_bagxpro_home_hero_button',
					'label' => __( 'Bouton principal', 'bagxpro' ),
					'name'  => 'home_hero_button',
					'type'  => 'link',
				),
				array(
					'key'           => 'field_bagxpro_home_hero_bag',
					'label'         => __( 'Image sac (devant)', 'bagxpro' ),
					'name'          => 'home_hero_bag_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
				array(
					'key'           => 'field_bagxpro_home_hero_placeholder',
					'label'         => __( 'Image placeholder / second plan', 'bagxpro' ),
					'name'          => 'home_hero_placeholder_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),

				array(
					'key'   => 'field_bagxpro_home_tab_logos',
					'label' => __( 'Logos partenaires', 'bagxpro' ),
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_bagxpro_home_logos_heading',
					'label' => __( 'Texte à gauche de la liste', 'bagxpro' ),
					'name'  => 'home_logos_heading',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_bagxpro_home_logos',
					'label'        => __( 'Logos', 'bagxpro' ),
					'name'         => 'home_logos',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Ajouter un logo', 'bagxpro' ),
					'sub_fields'   => array(
						array(
							'key'           => 'field_bagxpro_home_logo_img',
							'label'         => __( 'Image', 'bagxpro' ),
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
							'required'      => 1,
						),
					),
				),

				array(
					'key'   => 'field_bagxpro_home_tab_stats',
					'label' => __( 'Bloc chiffres', 'bagxpro' ),
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'        => 'field_bagxpro_home_stat_1',
					'label'      => __( 'Carte — ligne 1, colonne 1', 'bagxpro' ),
					'name'       => 'home_stat_card_1',
					'type'       => 'group',
					'sub_fields' => array(
						array(
							'key'   => 'field_bagxpro_home_s1_num',
							'label' => __( 'Chiffre', 'bagxpro' ),
							'name'  => 'number',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_bagxpro_home_s1_lab',
							'label' => __( 'Libellé court', 'bagxpro' ),
							'name'  => 'label',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_bagxpro_home_s1_desc',
							'label' => __( 'Description', 'bagxpro' ),
							'name'  => 'description',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
				array(
					'key'        => 'field_bagxpro_home_stat_2',
					'label'      => __( 'Carte — ligne 2, colonne 1', 'bagxpro' ),
					'name'       => 'home_stat_card_2',
					'type'       => 'group',
					'sub_fields' => array(
						array(
							'key'   => 'field_bagxpro_home_s2_num',
							'label' => __( 'Chiffre', 'bagxpro' ),
							'name'  => 'number',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_bagxpro_home_s2_lab',
							'label' => __( 'Libellé court', 'bagxpro' ),
							'name'  => 'label',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_bagxpro_home_s2_desc',
							'label' => __( 'Description', 'bagxpro' ),
							'name'  => 'description',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
				array(
					'key'           => 'field_bagxpro_home_stat_center',
					'label'         => __( 'Image centrale (entre les cartes)', 'bagxpro' ),
					'name'          => 'home_stat_center_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
				array(
					'key'        => 'field_bagxpro_home_stat_3',
					'label'      => __( 'Carte — colonne droite', 'bagxpro' ),
					'name'       => 'home_stat_card_3',
					'type'       => 'group',
					'sub_fields' => array(
						array(
							'key'   => 'field_bagxpro_home_s3_num',
							'label' => __( 'Chiffre', 'bagxpro' ),
							'name'  => 'number',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_bagxpro_home_s3_lab',
							'label' => __( 'Libellé court', 'bagxpro' ),
							'name'  => 'label',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_bagxpro_home_s3_desc',
							'label' => __( 'Description', 'bagxpro' ),
							'name'  => 'description',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),

				array(
					'key'   => 'field_bagxpro_home_tab_pres',
					'label' => __( 'Comment ça marche', 'bagxpro' ),
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_bagxpro_home_pres_img',
					'label'         => __( 'Image à gauche', 'bagxpro' ),
					'name'          => 'home_presentation_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium_large',
				),
				array(
					'key'   => 'field_bagxpro_home_pres_title',
					'label' => __( 'Titre de section', 'bagxpro' ),
					'name'  => 'home_presentation_title',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_bagxpro_home_pres_steps',
					'label'        => __( 'Étapes', 'bagxpro' ),
					'name'         => 'home_presentation_steps',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => __( 'Ajouter une étape', 'bagxpro' ),
					'sub_fields'   => array(
						array(
							'key'      => 'field_bagxpro_home_step_title',
							'label'    => __( 'Titre', 'bagxpro' ),
							'name'     => 'title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'   => 'field_bagxpro_home_step_text',
							'label' => __( 'Texte', 'bagxpro' ),
							'name'  => 'text',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
				array(
					'key'   => 'field_bagxpro_home_pres_cta',
					'label' => __( 'Bouton « Voir nos produits »', 'bagxpro' ),
					'name'  => 'home_presentation_cta',
					'type'  => 'link',
				),

				array(
					'key'   => 'field_bagxpro_home_tab_products',
					'label' => __( 'Liste produits', 'bagxpro' ),
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_bagxpro_home_prod_intro_title',
					'label' => __( 'Titre colonne gauche', 'bagxpro' ),
					'name'  => 'home_products_intro_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_bagxpro_home_prod_intro_text',
					'label' => __( 'Texte colonne gauche', 'bagxpro' ),
					'name'  => 'home_products_intro_text',
					'type'  => 'textarea',
					'rows'  => 4,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
