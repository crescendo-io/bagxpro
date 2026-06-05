<?php
/**
 * Champs ACF — modèle de page Contact.
 *
 * @package bagxpro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'bagxpro_register_acf_contact_page_fields' );
function bagxpro_register_acf_contact_page_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_bagxpro_contact_page',
			'title'                 => __( 'Page contact — contenus', 'bagxpro' ),
			'fields'                => array(
				array(
					'key'   => 'field_bagxpro_contact_intro',
					'label' => __( 'Introduction', 'bagxpro' ),
					'name'  => 'contact_page_intro',
					'type'  => 'wysiwyg',
					'tabs'  => 'all',
					'toolbar' => 'basic',
					'media_upload' => 0,
				),
				array(
					'key'   => 'field_bagxpro_contact_email',
					'label' => __( 'E-mail affiché', 'bagxpro' ),
					'name'  => 'contact_email',
					'type'  => 'email',
				),
				array(
					'key'   => 'field_bagxpro_contact_phone',
					'label' => __( 'Téléphone', 'bagxpro' ),
					'name'  => 'contact_phone',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_bagxpro_contact_address',
					'label' => __( 'Adresse', 'bagxpro' ),
					'name'  => 'contact_address',
					'type'  => 'textarea',
					'rows'  => 4,
				),
				array(
					'key'          => 'field_bagxpro_contact_hours',
					'label'        => __( 'Horaires d’ouverture', 'bagxpro' ),
					'name'         => 'contact_hours',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Ajouter une ligne', 'bagxpro' ),
					'sub_fields'   => array(
						array(
							'key'   => 'field_bagxpro_contact_hours_day',
							'label' => __( 'Jour / période', 'bagxpro' ),
							'name'  => 'day',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_bagxpro_contact_hours_time',
							'label' => __( 'Horaires', 'bagxpro' ),
							'name'  => 'time',
							'type'  => 'text',
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-contact.php',
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

/**
 * Horaires par défaut si le repeater ACF est vide.
 *
 * @return array<int, array{day: string, time: string}>
 */
function bagxpro_contact_default_hours() {
	return array(
		array(
			'day'  => __( 'Lundi – Vendredi', 'bagxpro' ),
			'time' => __( '9h00 – 18h00', 'bagxpro' ),
		),
		array(
			'day'  => __( 'Samedi', 'bagxpro' ),
			'time' => __( 'Fermé', 'bagxpro' ),
		),
		array(
			'day'  => __( 'Dimanche', 'bagxpro' ),
			'time' => __( 'Fermé', 'bagxpro' ),
		),
	);
}

/**
 * Lignes d’horaires pour l’affichage (ACF ou défaut).
 *
 * @param int $page_id ID de la page contact.
 * @return array<int, array{day: string, time: string}>
 */
function bagxpro_get_contact_hours_rows( $page_id = 0 ) {
	$page_id = $page_id ? (int) $page_id : (int) get_queried_object_id();
	$rows    = function_exists( 'get_field' ) ? get_field( 'contact_hours', $page_id ) : null;

	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return bagxpro_contact_default_hours();
	}

	$out = array();
	foreach ( $rows as $row ) {
		$day  = isset( $row['day'] ) ? trim( (string) $row['day'] ) : '';
		$time = isset( $row['time'] ) ? trim( (string) $row['time'] ) : '';
		if ( '' === $day && '' === $time ) {
			continue;
		}
		$out[] = array(
			'day'  => $day,
			'time' => $time,
		);
	}

	return ! empty( $out ) ? $out : bagxpro_contact_default_hours();
}
