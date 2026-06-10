<?php
/**
 * Formulaire fiche produit : Mailjet + CPT commande + ACF. Sécurité : honeypot, rate limit IP/e-mail,
 * sangles recalculées serveur (ACF), logos raster uniquement (pas SVG), html2canvas en local, journaux sans fuite détail.
 *
 * Préférez dans wp-config.php :
 * define( 'BAGXPRO_MAILJET_API_KEY', '...' );
 * define( 'BAGXPRO_MAILJET_API_SECRET', '...' );
 *
 * @package bagxpro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adresse IP du client (REMOTE_ADDR). X-Forwarded-For seulement si le filtre de confiance est activé.
 *
 * @return string
 */
function bagxpro_produit_get_request_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( '' === $ip ) {
		return '0.0.0.0';
	}
	$ip = sanitize_text_field( $ip );
	if ( (bool) apply_filters( 'bagxpro_produit_trust_x_forwarded_for', false ) && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$xff   = trim( (string) wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		$parts = explode( ',', $xff );
		$cand  = isset( $parts[0] ) ? trim( $parts[0] ) : '';
		if ( '' !== $cand && filter_var( $cand, FILTER_VALIDATE_IP ) ) {
			$ip = sanitize_text_field( $cand );
		}
	}
	return $ip;
}

/**
 * Libellés des couleurs de sangles issus du produit (ACF), pour ne pas faire confiance au POST.
 *
 * @param int $product_id ID post produit.
 * @return array{labels: array<int, string>, count: int}
 */
function bagxpro_produit_get_strap_palette( $product_id ) {
	$product_id = (int) $product_id;
	$labels     = array();

	if ( $product_id > 0 && function_exists( 'get_field' ) ) {
		$couleurs_acf = get_field( 'product_colors', $product_id );
		if ( ! empty( $couleurs_acf ) && is_array( $couleurs_acf ) ) {
			foreach ( $couleurs_acf as $i => $row ) {
				$lbl = '';
				if ( isset( $row['product_color_name'] ) && '' !== trim( (string) $row['product_color_name'] ) ) {
					$lbl = (string) $row['product_color_name'];
				} elseif ( isset( $row['product_color_label'] ) ) {
					$lbl = (string) $row['product_color_label'];
				}
				$lbl = sanitize_text_field( $lbl );
				if ( '' === $lbl ) {
					$lbl = sprintf( /* translators: %d: color index */ __( 'Couleur %d', 'bagxpro' ), $i + 1 );
				}
				$labels[] = $lbl;
			}
		}
	}

	if ( empty( $labels ) ) {
		$labels[] = __( 'Couleur 1', 'bagxpro' );
	}

	return array(
		'labels' => $labels,
		'count'  => count( $labels ),
	);
}

/**
 * Options du select quantité (fiche produit, valeurs fixes — sans impression).
 *
 * @return array<string, string>
 */
function bagxpro_produit_quantity_tier_options() {
	$options = array(
		'500'  => __( '500 pièces', 'bagxpro' ),
		'1000' => __( '1 000 pièces', 'bagxpro' ),
		'libre'  => __( 'Quantité libre', 'bagxpro' ),
	);

	/**
	 * Filtre les lignes du select « Nombre de sacs ».
	 *
	 * @param array<string, string> $options Clé => libellé affiché.
	 */
	return (array) apply_filters( 'bagxpro_produit_quantity_tier_options', $options );
}

/**
 * Valide la valeur POST du select quantité.
 *
 * @param string $value Valeur brute.
 * @return string Clé valide ou chaîne vide.
 */
function bagxpro_produit_sanitize_quantity_tier( $value ) {
	$value = sanitize_text_field( (string) $value );
	$opts  = bagxpro_produit_quantity_tier_options();
	return isset( $opts[ $value ] ) ? $value : '';
}

/**
 * Décompose quantité (select), impression (radios) et libellés pour e-mail / commande.
 *
 * @param string $tier_key    Clé du select (500, 1000, libre).
 * @param string $custom_qty  Quantité saisie si « libre ».
 * @param string $print_faces « 2 » ou « 4 ».
 * @return array{
 *   tier_key: string,
 *   tier_label: string,
 *   qty_base: string,
 *   print_faces: string,
 *   print_faces_label: string,
 *   quantity_custom: string
 * }
 */
function bagxpro_produit_resolve_quantity_choice( $tier_key, $custom_qty = '', $print_faces = '' ) {
	$tier_key     = sanitize_text_field( (string) $tier_key );
	$custom_qty   = sanitize_text_field( (string) $custom_qty );
	$print_faces  = bagxpro_produit_sanitize_print_faces( $print_faces );
	$options      = bagxpro_produit_quantity_tier_options();
	$label        = isset( $options[ $tier_key ] ) ? $options[ $tier_key ] : '—';

	$out = array(
		'tier_key'          => $tier_key,
		'tier_label'        => $label,
		'qty_base'          => $tier_key,
		'print_faces'       => $print_faces,
		'print_faces_label' => bagxpro_produit_print_faces_label( $print_faces ),
		'quantity_custom'   => '',
	);

	if ( 'libre' === $tier_key ) {
		$out['quantity_custom'] = $custom_qty;
		if ( '' !== $custom_qty ) {
			$out['tier_label'] = sprintf(
				/* translators: %s: number of bags */
				__( 'Quantité libre : %s pièces', 'bagxpro' ),
				$custom_qty
			);
		}
	}

	return $out;
}

/**
 * Options d’impression (2 ou 4 faces) pour le formulaire produit.
 *
 * @return array<string, string> Clé « 2 » ou « 4 » => libellé traduit.
 */
function bagxpro_produit_print_faces_options() {
	return array(
		'2' => __( 'Impression 2 faces', 'bagxpro' ),
		'4' => __( 'Impression 4 faces', 'bagxpro' ),
	);
}

/**
 * Valide et normalise la valeur POST du type d’impression.
 *
 * @param string $value Valeur brute.
 * @return string « 2 », « 4 » ou chaîne vide si invalide.
 */
function bagxpro_produit_sanitize_print_faces( $value ) {
	$value = sanitize_text_field( (string) $value );
	$opts  = bagxpro_produit_print_faces_options();
	return isset( $opts[ $value ] ) ? $value : '';
}

/**
 * Libellé affiché pour un type d’impression.
 *
 * @param string $value « 2 » ou « 4 ».
 * @return string
 */
function bagxpro_produit_print_faces_label( $value ) {
	$opts = bagxpro_produit_print_faces_options();
	return isset( $opts[ $value ] ) ? $opts[ $value ] : '—';
}

/**
 * Vérifie et incrémente le rate limiting (IP + e-mail). Appeler après validation des champs obligatoires.
 *
 * @param string $email E-mail déjà normalisé (sanitize_email).
 * @return bool False si la limite est dépassée.
 */
function bagxpro_produit_form_rate_limit_tick( $email ) {
	$window = max( 60, (int) apply_filters( 'bagxpro_produit_form_rate_limit_window', 15 * MINUTE_IN_SECONDS ) );
	$max_ip = max( 1, (int) apply_filters( 'bagxpro_produit_form_rate_limit_max_ip', 10 ) );
	$max_em = max( 1, (int) apply_filters( 'bagxpro_produit_form_rate_limit_max_email', 5 ) );

	$ip  = bagxpro_produit_get_request_ip();
	$em  = strtolower( trim( (string) $email ) );
	$k_i = 'bagxprl_ip_' . wp_hash( 'ip|' . $ip );
	$k_e = 'bagxprl_em_' . wp_hash( 'em|' . $em );

	$n_ip = (int) get_transient( $k_i );
	$n_em = (int) get_transient( $k_e );
	if ( $n_ip >= $max_ip || $n_em >= $max_em ) {
		return false;
	}

	set_transient( $k_i, $n_ip + 1, $window );
	set_transient( $k_e, $n_em + 1, $window );

	return true;
}

/**
 * Clés API Mailjet (constantes wp-config ou valeurs par défaut du thème).
 *
 * @return array{key: string, secret: string}
 */
function bagxpro_mailjet_get_credentials() {
	$key    = ( defined( 'BAGXPRO_MAILJET_API_KEY' ) && BAGXPRO_MAILJET_API_KEY ) ? (string) BAGXPRO_MAILJET_API_KEY : '';
	$secret = ( defined( 'BAGXPRO_MAILJET_API_SECRET' ) && BAGXPRO_MAILJET_API_SECRET ) ? (string) BAGXPRO_MAILJET_API_SECRET : '';


	return (array) apply_filters(
		'bagxpro_mailjet_credentials',
		array(
			'key'    => $key,
			'secret' => $secret,
		)
	);
}

/**
 * Destinataires des notifications Mailjet (formulaires produit + contact).
 *
 * @param int $context_id ID de contexte optionnel (produit, page…).
 * @return array<int, string>
 */
function bagxpro_get_mail_notification_recipients( $context_id = 0 ) {
	$emails = array(
		'b.vidal@crescendo-studio.io',
		'b.tillard@bag-x-pro.com',
		's.droneau@bag-x-pro.com',
	);

	$out = array();
	foreach ( $emails as $email ) {
		$email = sanitize_email( (string) $email );
		if ( is_email( $email ) ) {
			$out[] = $email;
		}
	}

	$out = array_values( array_unique( $out ) );

	/**
	 * Filtre la liste des destinataires notification.
	 *
	 * @param array<int, string> $out         E-mails validés.
	 * @param int                 $context_id ID de contexte.
	 */
	return (array) apply_filters( 'bagxpro_produit_mail_to_recipients', $out, (int) $context_id );
}

/**
 * Premier destinataire (affichage public, compatibilité).
 *
 * @param int $context_id ID de contexte optionnel.
 * @return string
 */
function bagxpro_get_mail_notification_email( $context_id = 0 ) {
	$recipients = bagxpro_get_mail_notification_recipients( $context_id );
	return ! empty( $recipients ) ? $recipients[0] : (string) get_option( 'admin_email' );
}

/**
 * Nom affiché pour les destinataires notification.
 *
 * @param int $context_id ID de contexte optionnel.
 * @return string
 */
function bagxpro_get_mail_notification_name( $context_id = 0 ) {
	return (string) apply_filters( 'bagxpro_produit_mail_to_name', get_bloginfo( 'name' ), (int) $context_id );
}

/**
 * Construit une pièce jointe Mailjet depuis un upload $_FILES (null si absent ou invalide).
 *
 * @param string        $field_name    Clé dans $_FILES.
 * @param array<string> $allowed_ext   Extensions autorisées (sans point).
 * @param int           $max_bytes     Taille max.
 * @param string        $fallback_name Nom de fichier si manquant.
 * @return array<string, string>|null
 */
function bagxpro_produit_upload_tmp_is_usable( array $file, $field_name = '' ) {
	if ( empty( $file['tmp_name'] ) ) {
		return false;
	}
	if ( isset( $file['error'] ) && UPLOAD_ERR_OK !== (int) $file['error'] ) {
		return false;
	}
	$tmp = $file['tmp_name'];
	if ( is_uploaded_file( $tmp ) ) {
		return true;
	}
	return (bool) apply_filters( 'bagxpro_upload_allow_non_uploaded_file_tmp', false, $file, $field_name )
		&& is_readable( $tmp )
		&& is_file( $tmp );
}

function bagxpro_produit_mailjet_attachment_from_upload( $field_name, array $allowed_ext, $max_bytes, $fallback_name ) {
	if ( empty( $_FILES[ $field_name ] ) || ! is_array( $_FILES[ $field_name ] ) ) {
		return null;
	}
	$file = $_FILES[ $field_name ];
	if ( ! bagxpro_produit_upload_tmp_is_usable( $file, $field_name ) ) {
		return null;
	}
	$tmp = $file['tmp_name'];
	$size = isset( $file['size'] ) ? (int) $file['size'] : 0;
	if ( $size < 1 ) {
		$fs = @filesize( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( false !== $fs ) {
			$size = (int) $fs;
		}
	}
	if ( $size < 1 || $size > $max_bytes ) {
		return null;
	}
	$filename   = isset( $file['name'] ) ? sanitize_file_name( wp_unslash( $file['name'] ) ) : '';
	$check_name = $filename ? $filename : $fallback_name;

	$checked = wp_check_filetype_and_ext( $tmp, $check_name );
	$ext     = ! empty( $checked['ext'] ) ? strtolower( $checked['ext'] ) : '';
	$mime    = ! empty( $checked['type'] ) ? $checked['type'] : '';

	if ( '' === $ext ) {
		$ft = wp_check_filetype( $check_name );
		if ( ! empty( $ft['ext'] ) ) {
			$ext  = strtolower( $ft['ext'] );
			$mime = $ft['type'] ? $ft['type'] : $mime;
		}
	}

	if ( '' === $ext && function_exists( 'wp_get_image_mime' ) ) {
		$img_mime = wp_get_image_mime( $tmp );
		$raster   = array(
			'image/jpeg' => 'jpeg',
			'image/png'  => 'png',
			'image/gif'  => 'gif',
			'image/webp' => 'webp',
		);
		if ( isset( $raster[ $img_mime ] ) && in_array( $raster[ $img_mime ], $allowed_ext, true ) ) {
			$ext  = $raster[ $img_mime ];
			$mime = $img_mime;
		}
	}

	if ( '' === $ext || ! in_array( $ext, $allowed_ext, true ) ) {
		return null;
	}

	if ( '' === $mime ) {
		$mime = 'image/' . ( 'jpeg' === $ext ? 'jpeg' : $ext );
	}

	$raw = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( false === $raw || '' === $raw ) {
		return null;
	}
	$final_name = $filename ? $filename : ( $fallback_name ? $fallback_name : 'fichier.' . $ext );

	return array(
		'Filename'      => $final_name,
		'ContentType'   => $mime,
		'Base64Content' => base64_encode( $raw ),
	);
}

/**
 * Envoie un message via l’API Mailjet v3.1 (HTML + texte + pièces jointes optionnelles).
 *
 * @param array $args {
 *   @type string|string[]   $to_email     Destinataire (legacy, un seul).
 *   @type array<int,string> $to_emails    Destinataires multiples.
 *   @type string             $to_name      Nom affiché du destinataire.
 *   @type string          $subject      Objet.
 *   @type string          $html         Corps HTML.
 *   @type string          $text         Corps texte brut (recommandé).
 *   @type string          $from_email   Expéditeur (domaine vérifié Mailjet).
 *   @type string          $from_name    Nom expéditeur.
 *   @type string          $reply_email  Réponse à (souvent l’e-mail client).
 *   @type string          $reply_name   Nom pour Reply-To.
 *   @type array<int,array<string,string>> $attachments Tableau de [ 'Filename', 'ContentType', 'Base64Content' ].
 * }
 * @return true|WP_Error
 */
function bagxpro_mailjet_send_message( array $args ) {
	$creds = bagxpro_mailjet_get_credentials();
	if ( '' === $creds['key'] || '' === $creds['secret'] ) {
		return new WP_Error( 'bagxpro_mailjet_no_creds', __( 'Clés Mailjet manquantes.', 'bagxpro' ) );
	}

	$defaults = array(
		'to_email'      => '',
		'to_emails'     => array(),
		'to_name'       => '',
		'subject'       => '',
		'html'          => '',
		'text'          => '',
		'from_email'    => 'b.vidal@crescendo-studio.io',
		'from_name'     => 'BAG x PRO',
		'reply_email'   => '',
		'reply_name'    => '',
		'attachments'   => array(),
	);
	$args     = wp_parse_args( $args, $defaults );

	$to_list = array();
	if ( ! empty( $args['to_emails'] ) && is_array( $args['to_emails'] ) ) {
		foreach ( $args['to_emails'] as $addr ) {
			$addr = sanitize_email( (string) $addr );
			if ( is_email( $addr ) ) {
				$to_list[] = array(
					'Email' => $addr,
					'Name'  => $args['to_name'],
				);
			}
		}
	} elseif ( is_email( $args['to_email'] ) ) {
		$to_list[] = array(
			'Email' => $args['to_email'],
			'Name'  => $args['to_name'],
		);
	}

	if ( empty( $to_list ) ) {
		return new WP_Error( 'bagxpro_mailjet_bad_to', __( 'Destinataire invalide.', 'bagxpro' ) );
	}

	$message = array(
		'From'        => array(
			'Email' => $args['from_email'],
			'Name'  => $args['from_name'],
		),
		'To'          => $to_list,
		'Subject'     => $args['subject'],
		'HTMLPart'    => $args['html'],
		'TextPart'    => $args['text'] ? $args['text'] : wp_strip_all_tags( $args['html'] ),
	);

	if ( is_email( $args['reply_email'] ) ) {
		$message['ReplyTo'] = array(
			'Email' => $args['reply_email'],
			'Name'  => $args['reply_name'] ? $args['reply_name'] : $args['reply_email'],
		);
	}

	if ( ! empty( $args['attachments'] ) && is_array( $args['attachments'] ) ) {
		$message['Attachments'] = array_values( $args['attachments'] );
	}

	$body = wp_json_encode(
		array(
			'Messages' => array( $message ),
		)
	);

	$response = wp_remote_post(
		'https://api.mailjet.com/v3.1/send',
		array(
			'timeout' => 30,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( $creds['key'] . ':' . $creds['secret'] ),
			),
			'body'    => $body,
		)
	);

	if ( is_wp_error( $response ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'bagxpro Mailjet: erreur transport (WP_Error).' );
		}
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( $code < 200 || $code >= 300 ) {
		$msg = isset( $data['Messages'][0]['Errors'][0]['ErrorMessage'] )
			? $data['Messages'][0]['Errors'][0]['ErrorMessage']
			: wp_remote_retrieve_body( $response );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'bagxpro Mailjet: HTTP ' . (int) $code );
		}
		return new WP_Error( 'bagxpro_mailjet_http', $msg ? $msg : __( 'Erreur Mailjet.', 'bagxpro' ) );
	}

	return true;
}

/**
 * Compatibilité avec l’ancienne fonction du thème.
 *
 * @param string $to_email    E-mail destinataire.
 * @param string $to_name     Nom.
 * @param string $subject     Objet.
 * @param string $html_content HTML.
 * @return bool
 */
function send_mailjet_email( $to_email, $to_name, $subject, $html_content ) {
	$r = bagxpro_mailjet_send_message(
		array(
			'to_email' => $to_email,
			'to_name'  => $to_name,
			'subject'  => $subject,
			'html'     => $html_content,
		)
	);
	return ! is_wp_error( $r );
}

/**
 * Enregistre une commande (CPT commande) depuis le formulaire produit.
 *
 * @param array $args {
 *   @type int    $product_id
 *   @type string $product_title
 *   @type string $societe
 *   @type string $nom
 *   @type string $prenom
 *   @type string $email
 *   @type string $telephone
 *   @type string $tier
 *   @type string $tier_label
 *   @type string $print_faces
 *   @type string $print_faces_label
 *   @type string $strap_idx
 *   @type string $strap_lbl
 *   @type string $text_body
 *   @type bool   $has_logo
 *   @type bool   $has_preview
 * }
 * @return int 0 en cas d’échec, sinon ID du post commande.
 */
function bagxpro_create_commande_record( array $args ) {
	if ( ! post_type_exists( 'commande' ) ) {
		return 0;
	}

	$defaults = array(
		'product_id'    => 0,
		'product_title' => '',
		'societe'       => '',
		'nom'           => '',
		'prenom'        => '',
		'email'         => '',
		'telephone'     => '',
		'tier'              => '',
		'tier_label'        => '',
		'print_faces'       => '',
		'print_faces_label' => '',
		'quantity_custom'   => '',
		'strap_idx'         => '',
		'strap_lbl'     => '',
		'text_body'     => '',
		'has_logo'      => false,
		'has_preview'   => false,
	);
	$args     = wp_parse_args( $args, $defaults );

	$title = sprintf(
		/* translators: 1: product title, 2: first name, 3: last name, 4: date/time */
		__( 'Commande — %1$s — %2$s %3$s — %4$s', 'bagxpro' ),
		$args['product_title'] ? $args['product_title'] : __( 'Produit', 'bagxpro' ),
		$args['prenom'],
		$args['nom'],
		wp_date( __( 'd/m/Y H:i', 'bagxpro' ) )
	);

	$author_id = (int) apply_filters( 'bagxpro_commande_author_user_id', 1 );
	if ( $author_id < 1 ) {
		$author_id = 1;
	}

	$prev_user = get_current_user_id();
	wp_set_current_user( $author_id );

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'commande',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => sanitize_textarea_field( $args['text_body'] ),
			'post_author'  => $author_id,
		),
		true
	);

	wp_set_current_user( $prev_user );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_wp_error( $post_id ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'bagxpro_create_commande_record: échec wp_insert_post.' );
		}
		return 0;
	}

	update_post_meta( $post_id, '_bagxpro_product_id', absint( $args['product_id'] ) );
	update_post_meta( $post_id, '_bagxpro_societe', $args['societe'] );
	update_post_meta( $post_id, '_bagxpro_nom', $args['nom'] );
	update_post_meta( $post_id, '_bagxpro_prenom', $args['prenom'] );
	update_post_meta( $post_id, '_bagxpro_email', $args['email'] );
	update_post_meta( $post_id, '_bagxpro_telephone', $args['telephone'] );
	update_post_meta( $post_id, '_bagxpro_quantity_tier', $args['tier'] );
	update_post_meta( $post_id, '_bagxpro_quantity_label', $args['tier_label'] );
	update_post_meta( $post_id, '_bagxpro_print_faces', $args['print_faces'] );
	update_post_meta( $post_id, '_bagxpro_print_faces_label', $args['print_faces_label'] );
	update_post_meta( $post_id, '_bagxpro_quantity_custom', $args['quantity_custom'] );
	update_post_meta( $post_id, '_bagxpro_strap_index', $args['strap_idx'] );
	update_post_meta( $post_id, '_bagxpro_strap_label', $args['strap_lbl'] );
	update_post_meta( $post_id, '_bagxpro_has_logo', ! empty( $args['has_logo'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_bagxpro_has_preview', ! empty( $args['has_preview'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_bagxpro_rgpd_consent', '1' );
	update_post_meta( $post_id, '_bagxpro_rgpd_consent_at', current_time( 'mysql', true ) );
	update_post_meta( $post_id, '_bagxpro_mailjet_sent', '' );

	/**
	 * Après création d’une commande en base.
	 *
	 * @param int   $post_id ID CPT commande.
	 * @param array $args    Données passées à bagxpro_create_commande_record.
	 */
	do_action( 'bagxpro_commande_created', $post_id, $args );

	return (int) $post_id;
}

/**
 * Renseigne un champ image ACF (ID attachment) ou post_meta si ACF est indisponible.
 *
 * @param int    $post_id         Post commande.
 * @param string $field_name      Nom du champ ACF.
 * @param int    $attachment_id   ID pièce jointe.
 */
function bagxpro_commande_set_acf_image_field( $post_id, $field_name, $attachment_id ) {
	$post_id         = (int) $post_id;
	$attachment_id   = (int) $attachment_id;
	$field_name      = sanitize_key( (string) $field_name );
	if ( $post_id < 1 || $attachment_id < 1 || '' === $field_name ) {
		return;
	}
	if ( function_exists( 'update_field' ) ) {
		update_field( $field_name, $attachment_id, $post_id );
		return;
	}
	update_post_meta( $post_id, $field_name, $attachment_id );
}

/**
 * Importe logo + capture vers la médiathèque et renseigne les champs ACF image du post commande.
 * À appeler alors que current_user peut uploader (ex. auteur filtre bagxpro_commande_author_user_id).
 *
 * Noms de champs ACF par défaut : client_logo, preview_client.
 *
 * @param int $commande_post_id ID du CPT commande.
 */
function bagxpro_commande_save_acf_uploads( $commande_post_id ) {
	$commande_post_id = (int) $commande_post_id;
	if ( $commande_post_id < 1 || 'commande' !== get_post_type( $commande_post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'upload_files' ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$field_logo    = sanitize_key( (string) apply_filters( 'bagxpro_commande_acf_field_logo', 'client_logo', $commande_post_id ) );
	$field_preview = sanitize_key( (string) apply_filters( 'bagxpro_commande_acf_field_preview', 'preview_client', $commande_post_id ) );
	if ( '' === $field_logo ) {
		$field_logo = 'client_logo';
	}
	if ( '' === $field_preview ) {
		$field_preview = 'preview_client';
	}

	if ( ! empty( $_FILES['bagxpro_logo'] ) && is_array( $_FILES['bagxpro_logo'] ) && bagxpro_produit_upload_tmp_is_usable( $_FILES['bagxpro_logo'], 'bagxpro_logo' ) ) {
		$logo_id = media_handle_upload( 'bagxpro_logo', $commande_post_id );
		if ( ! is_wp_error( $logo_id ) ) {
			bagxpro_commande_set_acf_image_field( $commande_post_id, $field_logo, (int) $logo_id );
		}
	}

	if ( ! empty( $_FILES['bagxpro_preview_capture'] ) && is_array( $_FILES['bagxpro_preview_capture'] ) && bagxpro_produit_upload_tmp_is_usable( $_FILES['bagxpro_preview_capture'], 'bagxpro_preview_capture' ) ) {
		$shot_id = media_handle_upload( 'bagxpro_preview_capture', $commande_post_id );
		if ( ! is_wp_error( $shot_id ) ) {
			bagxpro_commande_set_acf_image_field( $commande_post_id, $field_preview, (int) $shot_id );
		}
	}
}

/**
 * Traitement POST du formulaire produit.
 */
function bagxpro_handle_produit_form_submit() {
	if ( ! isset( $_POST['bagxpro_form_nonce'] ) ) {
		wp_safe_redirect( wp_get_referer() ?: home_url( '/' ) );
		exit;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bagxpro_form_nonce'] ) ), 'bagxpro_produit_form' ) ) {
		wp_safe_redirect( add_query_arg( 'commande', 'nonce', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	$product_id = isset( $_POST['bagxpro_product_id'] ) ? absint( $_POST['bagxpro_product_id'] ) : 0;
	if ( ! $product_id || 'produit' !== get_post_type( $product_id ) ) {
		wp_safe_redirect( add_query_arg( 'commande', 'erreur', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	if ( isset( $_POST['bagxpro_hp'] ) && '' !== trim( (string) wp_unslash( $_POST['bagxpro_hp'] ) ) ) {
		wp_safe_redirect( get_permalink( $product_id ) );
		exit;
	}

	$societe   = isset( $_POST['bagxpro_societe'] ) ? sanitize_text_field( wp_unslash( $_POST['bagxpro_societe'] ) ) : '';
	$nom       = isset( $_POST['bagxpro_nom'] ) ? sanitize_text_field( wp_unslash( $_POST['bagxpro_nom'] ) ) : '';
	$prenom    = isset( $_POST['bagxpro_prenom'] ) ? sanitize_text_field( wp_unslash( $_POST['bagxpro_prenom'] ) ) : '';
	$email     = isset( $_POST['bagxpro_email'] ) ? sanitize_email( wp_unslash( $_POST['bagxpro_email'] ) ) : '';
	$telephone = isset( $_POST['bagxpro_telephone'] ) ? sanitize_text_field( wp_unslash( $_POST['bagxpro_telephone'] ) ) : '';
	$tier_key       = isset( $_POST['bagxpro_quantity_tier'] ) ? bagxpro_produit_sanitize_quantity_tier( wp_unslash( $_POST['bagxpro_quantity_tier'] ) ) : '';
	$print_faces    = isset( $_POST['bagxpro_print_faces'] ) ? bagxpro_produit_sanitize_print_faces( wp_unslash( $_POST['bagxpro_print_faces'] ) ) : '';
	$custom_qty_raw = isset( $_POST['bagxpro_quantity_custom'] ) ? wp_unslash( $_POST['bagxpro_quantity_custom'] ) : '';
	$custom_qty     = '' !== $custom_qty_raw && is_numeric( $custom_qty_raw ) ? (string) absint( $custom_qty_raw ) : '';

	if ( 'libre' === $tier_key ) {
		$custom_min = max( 1, (int) apply_filters( 'bagxpro_produit_quantity_custom_min', 500 ) );
		if ( '' === $custom_qty || (int) $custom_qty < $custom_min ) {
			$tier_key = '';
		}
	}

	if ( '' === $societe || '' === $nom || '' === $prenom || ! is_email( $email ) || '' === $tier_key || '' === $print_faces ) {
		wp_safe_redirect( add_query_arg( 'commande', 'incomplet', get_permalink( $product_id ) ) );
		exit;
	}

	$rgpd_consent = isset( $_POST['bagxpro_rgpd_consent'] ) ? sanitize_text_field( wp_unslash( $_POST['bagxpro_rgpd_consent'] ) ) : '';
	if ( '1' !== $rgpd_consent ) {
		wp_safe_redirect( add_query_arg( 'commande', 'rgpd', get_permalink( $product_id ) ) );
		exit;
	}

	if ( ! bagxpro_produit_form_rate_limit_tick( $email ) ) {
		wp_safe_redirect( add_query_arg( 'commande', 'limite', get_permalink( $product_id ) ) );
		exit;
	}

	$palette    = bagxpro_produit_get_strap_palette( $product_id );
	$strap_i    = isset( $_POST['bagxpro_strap_index'] ) ? (int) wp_unslash( $_POST['bagxpro_strap_index'] ) : 0;
	if ( $strap_i < 0 ) {
		$strap_i = 0;
	}
	$max_idx = max( 0, $palette['count'] - 1 );
	if ( $strap_i > $max_idx ) {
		$strap_i = $max_idx;
	}
	$strap_idx = (string) $strap_i;
	$strap_lbl = isset( $palette['labels'][ $strap_i ] ) ? $palette['labels'][ $strap_i ] : $palette['labels'][0];

	$product_title   = get_the_title( $product_id );
	$quantity_choice   = bagxpro_produit_resolve_quantity_choice( $tier_key, $custom_qty, $print_faces );
	$tier_label        = $quantity_choice['tier_label'];
	$print_faces       = $quantity_choice['print_faces'];
	$print_faces_label = $quantity_choice['print_faces_label'];

	$lines_text = array(
		__( 'Produit', 'bagxpro' ) . ': ' . $product_title . ' (ID ' . $product_id . ')',
		__( 'Nom de la société', 'bagxpro' ) . ': ' . $societe,
		__( 'Nom', 'bagxpro' ) . ': ' . $nom,
		__( 'Prénom', 'bagxpro' ) . ': ' . $prenom,
		__( 'E-mail', 'bagxpro' ) . ': ' . $email,
		__( 'Téléphone', 'bagxpro' ) . ': ' . $telephone,
		__( 'Nombre de sacs', 'bagxpro' ) . ': ' . $tier_label,
		__( 'Impression', 'bagxpro' ) . ': ' . $print_faces_label,
		__( 'Couleur des sangles (index)', 'bagxpro' ) . ': ' . $strap_idx,
		__( 'Couleur des sangles (libellé)', 'bagxpro' ) . ': ' . $strap_lbl,
		__( 'Consentement RGPD (traitement des données, conservation sans limite de durée)', 'bagxpro' ) . ': ' . __( 'oui', 'bagxpro' ) . ' — ' . wp_date( __( 'd/m/Y à H:i', 'bagxpro' ) ),
		'',
		__( 'Pièces jointes : capture JPEG de l’aperçu (si jointe) et logo client (si fourni).', 'bagxpro' ),
	);
	$text_body  = implode( "\n", $lines_text );

	$html_rows = '';
	foreach ( array(
		array( __( 'Produit', 'bagxpro' ), esc_html( $product_title ) . ' <small>(ID ' . (int) $product_id . ')</small>' ),
		array( __( 'Nom de la société', 'bagxpro' ), esc_html( $societe ) ),
		array( __( 'Nom', 'bagxpro' ), esc_html( $nom ) ),
		array( __( 'Prénom', 'bagxpro' ), esc_html( $prenom ) ),
		array( __( 'E-mail', 'bagxpro' ), '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' ),
		array( __( 'Téléphone', 'bagxpro' ), esc_html( $telephone ) ),
		array( __( 'Nombre de sacs', 'bagxpro' ), esc_html( $tier_label ) ),
		array( __( 'Impression', 'bagxpro' ), esc_html( $print_faces_label ) ),
		array( __( 'Couleur des sangles', 'bagxpro' ), esc_html( $strap_lbl ) . ' <small>(#' . esc_html( $strap_idx ) . ')</small>' ),
		array(
			__( 'Consentement RGPD', 'bagxpro' ),
			esc_html__( 'Oui — conservation des données sans limite de durée (information portée à la connaissance sur le formulaire).', 'bagxpro' )
				. ' <small>(' . esc_html( wp_date( __( 'd/m/Y à H:i', 'bagxpro' ) ) ) . ')</small>',
		),
	) as $row ) {
		$html_rows .= '<tr><th style="text-align:left;padding:8px 12px;border:1px solid #ddd;background:#f5f5f5;">' . esc_html( $row[0] ) . '</th><td style="padding:8px 12px;border:1px solid #ddd;">' . $row[1] . '</td></tr>';
	}

	$attachments = array();
	$max_bytes   = (int) apply_filters( 'bagxpro_produit_logo_max_bytes', 8 * 1024 * 1024 );
	$logo_exts   = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );

	if ( ! empty( $_FILES['bagxpro_logo'] ) && is_array( $_FILES['bagxpro_logo'] ) && bagxpro_produit_upload_tmp_is_usable( $_FILES['bagxpro_logo'], 'bagxpro_logo' ) ) {
		$logo_att = bagxpro_produit_mailjet_attachment_from_upload( 'bagxpro_logo', $logo_exts, $max_bytes, 'logo.png' );
		if ( null === $logo_att ) {
			wp_safe_redirect( add_query_arg( 'commande', 'fichier', get_permalink( $product_id ) ) );
			exit;
		}
		$attachments[] = $logo_att;
	}

	$shot_exts = array( 'jpg', 'jpeg' );
	$shot_att  = bagxpro_produit_mailjet_attachment_from_upload( 'bagxpro_preview_capture', $shot_exts, $max_bytes, 'apercu-configurateur.jpg' );
	if ( is_array( $shot_att ) ) {
		$attachments[] = $shot_att;
	}

	$has_logo = ! empty( $_FILES['bagxpro_logo'] ) && is_array( $_FILES['bagxpro_logo'] ) && bagxpro_produit_upload_tmp_is_usable( $_FILES['bagxpro_logo'], 'bagxpro_logo' );
	$has_preview = is_array( $shot_att );

	$commande_id = bagxpro_create_commande_record(
		array(
			'product_id'    => $product_id,
			'product_title' => $product_title,
			'societe'       => $societe,
			'nom'           => $nom,
			'prenom'        => $prenom,
			'email'         => $email,
			'telephone'     => $telephone,
			'tier'              => $tier_key,
			'tier_label'        => $tier_label,
			'print_faces'       => $print_faces,
			'print_faces_label' => $print_faces_label,
			'quantity_custom'   => $quantity_choice['quantity_custom'],
			'strap_idx'         => $strap_idx,
			'strap_lbl'     => $strap_lbl,
			'text_body'     => $text_body,
			'has_logo'      => $has_logo,
			'has_preview'   => $has_preview,
		)
	);

	$text_body_mail = $text_body;
	if ( $commande_id ) {
		$text_with_ref = $text_body . "\n" . sprintf( /* translators: %d: commande post ID */ __( 'Réf. commande (admin) : #%d', 'bagxpro' ), $commande_id );
		$text_body_mail = $text_with_ref;

		$author_fix = (int) apply_filters( 'bagxpro_commande_author_user_id', 1 );
		if ( $author_fix < 1 ) {
			$author_fix = 1;
		}
		$prev_u = get_current_user_id();
		wp_set_current_user( $author_fix );
		wp_update_post(
			array(
				'ID'           => $commande_id,
				'post_content' => sanitize_textarea_field( $text_with_ref ),
			)
		);
		bagxpro_commande_save_acf_uploads( $commande_id );
		wp_set_current_user( $prev_u );

		$edit_link = get_edit_post_link( $commande_id, 'raw' );
		$ref_cell  = $edit_link
			? '<a href="' . esc_url( $edit_link ) . '">#' . (int) $commande_id . '</a>'
			: '#' . (int) $commande_id;
		$html_rows .= '<tr><th style="text-align:left;padding:8px 12px;border:1px solid #ddd;background:#f5f5f5;">' . esc_html__( 'Réf. commande', 'bagxpro' ) . '</th><td style="padding:8px 12px;border:1px solid #ddd;">' . $ref_cell . '</td></tr>';
	}

	$html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body style="font-family:sans-serif;font-size:15px;">';
	$html .= '<h1 style="font-size:18px;">' . esc_html__( 'Récapitulatif — demande produit', 'bagxpro' ) . '</h1>';
	$html .= '<table style="border-collapse:collapse;max-width:640px;">' . $html_rows . '</table>';
	$html .= '<p style="margin-top:16px;color:#666;font-size:13px;">' . esc_html__( 'Pièces jointes : capture JPEG de l’aperçu configurateur (si générée) et logo client (si fourni).', 'bagxpro' ) . '</p>';
	$html .= '</body></html>';

	$from = apply_filters(
		'bagxpro_mailjet_from',
		array(
			'email' => 'b.vidal@crescendo-studio.io',
			'name'  => 'BAG x PRO',
		)
	);

	$to_emails = bagxpro_get_mail_notification_recipients( $product_id );
	$to_name   = bagxpro_get_mail_notification_name( $product_id );

	$subject = apply_filters(
		'bagxpro_produit_mail_subject',
		sprintf( '[%s] %s — %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), __( 'Demande produit', 'bagxpro' ), $product_title ),
		$product_id
	);

	$result = bagxpro_mailjet_send_message(
		array(
			'to_emails'     => $to_emails,
			'to_name'       => $to_name,
			'subject'       => $subject,
			'html'          => $html,
			'text'          => $text_body_mail,
			'from_email'    => isset( $from['email'] ) ? $from['email'] : 'b.vidal@crescendo-studio.io',
			'from_name'     => isset( $from['name'] ) ? $from['name'] : 'BAG x PRO',
			'reply_email'   => $email,
			'reply_name'    => trim( $prenom . ' ' . $nom ),
			'attachments'   => $attachments,
		)
	);

	if ( $commande_id ) {
		$author_fix = (int) apply_filters( 'bagxpro_commande_author_user_id', 1 );
		if ( $author_fix < 1 ) {
			$author_fix = 1;
		}
		$prev_u = get_current_user_id();
		wp_set_current_user( $author_fix );
		if ( is_wp_error( $result ) ) {
			update_post_meta( $commande_id, '_bagxpro_mailjet_sent', '0' );
			$err_safe = sanitize_text_field( $result->get_error_message() );
			if ( strlen( $err_safe ) > 500 ) {
				$err_safe = substr( $err_safe, 0, 500 );
			}
			update_post_meta( $commande_id, '_bagxpro_mailjet_error', $err_safe );
		} else {
			update_post_meta( $commande_id, '_bagxpro_mailjet_sent', '1' );
			delete_post_meta( $commande_id, '_bagxpro_mailjet_error' );
		}
		wp_set_current_user( $prev_u );
	}

	if ( is_wp_error( $result ) ) {
		wp_safe_redirect( add_query_arg( 'commande', 'erreur', get_permalink( $product_id ) ) );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'commande', 'merci', get_permalink( $product_id ) ) );
	exit;
}

add_action( 'admin_post_nopriv_bagxpro_produit_commande', 'bagxpro_handle_produit_form_submit' );
add_action( 'admin_post_bagxpro_produit_commande', 'bagxpro_handle_produit_form_submit' );
