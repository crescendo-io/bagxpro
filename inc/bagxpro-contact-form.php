<?php
/**
 * Formulaire page Contact — envoi Mailjet (même destinataire que le formulaire produit).
 *
 * @package bagxpro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expéditeur Mailjet par défaut.
 *
 * @return array{email: string, name: string}
 */
function bagxpro_get_mailjet_from() {
	$from = apply_filters(
		'bagxpro_mailjet_from',
		array(
			'email' => 'b.vidal@crescendo-studio.io',
			'name'  => 'BAG x PRO',
		)
	);

	return array(
		'email' => isset( $from['email'] ) ? (string) $from['email'] : 'b.vidal@crescendo-studio.io',
		'name'  => isset( $from['name'] ) ? (string) $from['name'] : 'BAG x PRO',
	);
}

/**
 * Traitement POST du formulaire contact.
 */
function bagxpro_handle_contact_form_submit() {
	if ( ! isset( $_POST['bagxpro_contact_nonce'] ) ) {
		wp_safe_redirect( wp_get_referer() ?: home_url( '/' ) );
		exit;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bagxpro_contact_nonce'] ) ), 'bagxpro_contact_form' ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'nonce', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	$page_id = isset( $_POST['bagxpro_contact_page_id'] ) ? absint( $_POST['bagxpro_contact_page_id'] ) : 0;
	$back    = $page_id ? get_permalink( $page_id ) : ( wp_get_referer() ?: home_url( '/' ) );

	if ( isset( $_POST['bagxpro_contact_hp'] ) && '' !== trim( (string) wp_unslash( $_POST['bagxpro_contact_hp'] ) ) ) {
		wp_safe_redirect( $back );
		exit;
	}

	$societe   = isset( $_POST['bagxpro_contact_societe'] ) ? sanitize_text_field( wp_unslash( $_POST['bagxpro_contact_societe'] ) ) : '';
	$nom       = isset( $_POST['bagxpro_contact_nom'] ) ? sanitize_text_field( wp_unslash( $_POST['bagxpro_contact_nom'] ) ) : '';
	$prenom    = isset( $_POST['bagxpro_contact_prenom'] ) ? sanitize_text_field( wp_unslash( $_POST['bagxpro_contact_prenom'] ) ) : '';
	$email     = isset( $_POST['bagxpro_contact_email'] ) ? sanitize_email( wp_unslash( $_POST['bagxpro_contact_email'] ) ) : '';
	$telephone = isset( $_POST['bagxpro_contact_telephone'] ) ? sanitize_text_field( wp_unslash( $_POST['bagxpro_contact_telephone'] ) ) : '';
	$message   = isset( $_POST['bagxpro_contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bagxpro_contact_message'] ) ) : '';

	if ( '' === $nom || '' === $prenom || ! is_email( $email ) || '' === trim( $message ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'incomplet', $back ) );
		exit;
	}

	$rgpd_consent = isset( $_POST['bagxpro_contact_rgpd_consent'] ) ? sanitize_text_field( wp_unslash( $_POST['bagxpro_contact_rgpd_consent'] ) ) : '';
	if ( '1' !== $rgpd_consent ) {
		wp_safe_redirect( add_query_arg( 'contact', 'rgpd', $back ) );
		exit;
	}

	if ( ! bagxpro_produit_form_rate_limit_tick( $email ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'limite', $back ) );
		exit;
	}

	$page_title = $page_id ? get_the_title( $page_id ) : __( 'Contact', 'bagxpro' );

	$lines_text = array(
		__( 'Origine', 'bagxpro' ) . ': ' . $page_title . ( $page_id ? ' (ID ' . $page_id . ')' : '' ),
		__( 'Nom de la société', 'bagxpro' ) . ': ' . $societe,
		__( 'Nom', 'bagxpro' ) . ': ' . $nom,
		__( 'Prénom', 'bagxpro' ) . ': ' . $prenom,
		__( 'E-mail', 'bagxpro' ) . ': ' . $email,
		__( 'Téléphone', 'bagxpro' ) . ': ' . $telephone,
		__( 'Message', 'bagxpro' ) . ':',
		$message,
		'',
		__( 'Consentement RGPD', 'bagxpro' ) . ': ' . __( 'oui', 'bagxpro' ) . ' — ' . wp_date( __( 'd/m/Y à H:i', 'bagxpro' ) ),
	);
	$text_body = implode( "\n", $lines_text );

	$html_rows = '';
	foreach ( array(
		array( __( 'Origine', 'bagxpro' ), esc_html( $page_title ) . ( $page_id ? ' <small>(ID ' . (int) $page_id . ')</small>' : '' ) ),
		array( __( 'Nom de la société', 'bagxpro' ), esc_html( $societe ) ),
		array( __( 'Nom', 'bagxpro' ), esc_html( $nom ) ),
		array( __( 'Prénom', 'bagxpro' ), esc_html( $prenom ) ),
		array( __( 'E-mail', 'bagxpro' ), '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' ),
		array( __( 'Téléphone', 'bagxpro' ), esc_html( $telephone ) ),
		array( __( 'Message', 'bagxpro' ), nl2br( esc_html( $message ) ) ),
		array(
			__( 'Consentement RGPD', 'bagxpro' ),
			esc_html__( 'Oui', 'bagxpro' ) . ' <small>(' . esc_html( wp_date( __( 'd/m/Y à H:i', 'bagxpro' ) ) ) . ')</small>',
		),
	) as $row ) {
		$html_rows .= '<tr><th style="text-align:left;padding:8px 12px;border:1px solid #ddd;background:#f5f5f5;vertical-align:top;">' . esc_html( $row[0] ) . '</th><td style="padding:8px 12px;border:1px solid #ddd;">' . $row[1] . '</td></tr>';
	}

	$html  = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body style="font-family:sans-serif;font-size:15px;">';
	$html .= '<h1 style="font-size:18px;">' . esc_html__( 'Nouveau message — page contact', 'bagxpro' ) . '</h1>';
	$html .= '<table style="border-collapse:collapse;max-width:640px;">' . $html_rows . '</table>';
	$html .= '</body></html>';

	$from       = bagxpro_get_mailjet_from();
	$to_emails  = bagxpro_get_mail_notification_recipients( $page_id );
	$to_name    = bagxpro_get_mail_notification_name( $page_id );
	$subject   = apply_filters(
		'bagxpro_contact_mail_subject',
		sprintf(
			'[%s] %s — %s %s',
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			__( 'Contact', 'bagxpro' ),
			$prenom,
			$nom
		),
		$page_id
	);

	$result = bagxpro_mailjet_send_message(
		array(
			'to_emails'   => $to_emails,
			'to_name'     => $to_name,
			'subject'     => $subject,
			'html'        => $html,
			'text'        => $text_body,
			'from_email'  => $from['email'],
			'from_name'   => $from['name'],
			'reply_email' => $email,
			'reply_name'  => trim( $prenom . ' ' . $nom ),
		)
	);

	if ( is_wp_error( $result ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'erreur', $back ) );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'contact', 'merci', $back ) );
	exit;
}

add_action( 'admin_post_nopriv_bagxpro_contact', 'bagxpro_handle_contact_form_submit' );
add_action( 'admin_post_bagxpro_contact', 'bagxpro_handle_contact_form_submit' );
