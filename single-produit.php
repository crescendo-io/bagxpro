<?php
/**
 * Fiche produit big bag : aperçu logo, couleur sangles, quantité, commande.
 */

get_header();

while ( have_posts() ) :
	the_post();

	$fallback_bag = get_stylesheet_directory_uri() . '/images/big-bag-produit.png';
	$bag_src        = $fallback_bag;
	$bag_alt        = get_the_title();

	if ( has_post_thumbnail() ) {
		$bag_src = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		$thumb_id = get_post_thumbnail_id();
		if ( $thumb_id ) {
			$bag_alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
			if ( '' === $bag_alt ) {
				$bag_alt = get_the_title();
			}
		}
	}

	$product_color_rows = array();
	$couleurs_acf        = function_exists( 'get_field' ) ? get_field( 'product_colors' ) : null;
	if ( ! empty( $couleurs_acf ) && is_array( $couleurs_acf ) ) {
		foreach ( $couleurs_acf as $row ) {
			$hex         = isset( $row['product_color'] ) ? $row['product_color'] : '';
			$image_field = isset( $row['product_color_image'] ) ? $row['product_color_image'] : '';
			$image_url   = '';
			if ( is_array( $image_field ) && ! empty( $image_field['url'] ) ) {
				$image_url = $image_field['url'];
			} elseif ( is_numeric( $image_field ) ) {
				$image_url = wp_get_attachment_image_url( (int) $image_field, 'large' );
			} elseif ( is_string( $image_field ) && filter_var( $image_field, FILTER_VALIDATE_URL ) ) {
				$image_url = $image_field;
			}
			if ( ! $image_url ) {
				$image_url = $bag_src;
			}
			$strap_label = isset( $row['product_color_label'] ) ? trim( (string) $row['product_color_label'] ) : '';
			$color_name  = isset( $row['product_color_name'] ) ? trim( (string) $row['product_color_name'] ) : '';
			if ( '' === $color_name ) {
				$color_name = $strap_label;
			}
			if ( '' === $strap_label ) {
				$strap_label = $color_name;
			}
			$product_color_rows[] = array(
				'hex'   => $hex,
				'url'   => $image_url,
				'label' => $strap_label,
				'name'  => $color_name,
			);
		}
	}

	$bag_layers = array();
	if ( ! empty( $product_color_rows ) ) {
		foreach ( $product_color_rows as $row ) {
			$bag_layers[] = $row['url'];
		}
	} else {
		$bag_layers[] = $bag_src;
	}

	$first_strap_label = '';
	if ( ! empty( $product_color_rows[0] ) ) {
		$first_row = $product_color_rows[0];
		$first_strap_label = ! empty( $first_row['label'] ) ? $first_row['label'] : '';
		if ( '' === $first_strap_label && ! empty( $first_row['name'] ) ) {
			$first_strap_label = $first_row['name'];
		}
		if ( '' === $first_strap_label ) {
			$first_strap_label = __( 'Couleur 1', 'bagxpro' );
		}
	}
	?>


<main class="bagxpro-single-produit" id="bagxpro-produit-<?php echo (int) get_the_ID(); ?>" data-bagxpro-product-page>
	<div class="container">
		<div class="row bagxpro-single-produit__row">
			<div class="col-sm-6 mx-auto bagxpro-preview-col">
				<div class="bagxpro-preview-column">
					<div class="bagxpro-bag-preview-container">
						<div class="bagxpro-bag-preview" id="bagxpro-bag-preview">
							<div class="bagxpro-bag-preview__stack" data-bagxpro-bag-stack>
								<img
									class="bagxpro-bag-preview__size-ref"
									src="<?php echo esc_url( $bag_layers[0] ); ?>"
									alt=""
									loading="eager"
									decoding="async"
									draggable="false"
									aria-hidden="true"
								>
								<?php
								foreach ( $bag_layers as $layer_i => $layer_url ) :
									$is_first_layer = ( 0 === (int) $layer_i );
									?>
								<img
									class="bagxpro-bag-layer<?php echo $is_first_layer ? ' is-visible' : ''; ?>"
									src="<?php echo esc_url( $layer_url ); ?>"
									alt="<?php echo esc_attr( $bag_alt ); ?>"
									data-layer-index="<?php echo (int) $layer_i; ?>"
									loading="eager"
									decoding="async"
									fetchpriority="<?php echo $is_first_layer ? 'high' : 'low'; ?>"
									draggable="false"
								>
									<?php
								endforeach;
								?>
							</div>
							<div class="bagxpro-bag-preview__logo" id="bagxpro-bag-logo" aria-hidden="true" hidden></div>
						</div>
					</div>
					<div class="bagxpro-bag-preview__accent-line" aria-hidden="true"></div>
					<p class="bagxpro-bag-preview__legal"><?php esc_html_e( '• VISUEL NON CONTRACTUEL', 'bagxpro' ); ?></p>
				</div>
			</div>
			<div class="col-sm-5 mx-auto">
				<div class="bagxpro-produit-panel">
					<?php if ( isset( $_GET['commande'] ) && 'merci' === sanitize_text_field( wp_unslash( $_GET['commande'] ) ) ) : ?>
						<div class="bagxpro-notice bagxpro-notice--success" role="status">
							<?php esc_html_e( 'Merci : votre demande a bien été envoyée.', 'bagxpro' ); ?>
						</div>
					<?php elseif ( isset( $_GET['commande'] ) && 'incomplet' === sanitize_text_field( wp_unslash( $_GET['commande'] ) ) ) : ?>
						<div class="bagxpro-notice bagxpro-notice--error" role="alert">
							<?php esc_html_e( 'Merci de renseigner tous les champs obligatoires (société, nom, prénom, e-mail, quantité, type d’impression).', 'bagxpro' ); ?>
						</div>
					<?php elseif ( isset( $_GET['commande'] ) && 'nonce' === sanitize_text_field( wp_unslash( $_GET['commande'] ) ) ) : ?>
						<div class="bagxpro-notice bagxpro-notice--error" role="alert">
							<?php esc_html_e( 'Session expirée. Réessayez.', 'bagxpro' ); ?>
						</div>
					<?php elseif ( isset( $_GET['commande'] ) && 'fichier' === sanitize_text_field( wp_unslash( $_GET['commande'] ) ) ) : ?>
						<div class="bagxpro-notice bagxpro-notice--error" role="alert">
							<?php esc_html_e( 'Le fichier logo est invalide ou trop volumineux (max. 8 Mo).', 'bagxpro' ); ?>
						</div>
					<?php elseif ( isset( $_GET['commande'] ) && 'erreur' === sanitize_text_field( wp_unslash( $_GET['commande'] ) ) ) : ?>
						<div class="bagxpro-notice bagxpro-notice--error" role="alert">
							<?php esc_html_e( 'L’envoi a échoué. Réessayez ou contactez-nous.', 'bagxpro' ); ?>
						</div>
					<?php elseif ( isset( $_GET['commande'] ) && 'limite' === sanitize_text_field( wp_unslash( $_GET['commande'] ) ) ) : ?>
						<div class="bagxpro-notice bagxpro-notice--error" role="alert">
							<?php esc_html_e( 'Trop de demandes envoyées récemment. Patientez quelques minutes avant de réessayer.', 'bagxpro' ); ?>
						</div>
					<?php elseif ( isset( $_GET['commande'] ) && 'rgpd' === sanitize_text_field( wp_unslash( $_GET['commande'] ) ) ) : ?>
						<div class="bagxpro-notice bagxpro-notice--error" role="alert">
							<?php esc_html_e( 'Merci d’accepter l’information sur les données personnelles avant d’envoyer votre demande.', 'bagxpro' ); ?>
						</div>
					<?php endif; ?>

					<header class="bagxpro-produit-panel__header">
						<h1><?php the_title(); ?></h1>
						<p>
							<div class="description-produit">	
								<?= get_field('description_product'); ?>
							</div>
						</p>
					</header>

					<form
						class="bagxpro-produit-form bagxpro-no-scroll-reveal"
						method="post"
						action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
						enctype="multipart/form-data"
					>
						<input type="hidden" name="action" value="bagxpro_produit_commande">
						<?php wp_nonce_field( 'bagxpro_produit_form', 'bagxpro_form_nonce' ); ?>
						<input type="hidden" name="bagxpro_product_id" value="<?php echo (int) get_the_ID(); ?>">
						<input type="hidden" name="bagxpro_strap_index" id="bagxpro-strap-index" value="0">
						<input type="hidden" name="bagxpro_strap_label" id="bagxpro-strap-label" value="<?php echo esc_attr( $first_strap_label ); ?>">
						<div class="bagxpro-visually-hidden" aria-hidden="true">
							<label for="bagxpro-hp"><?php esc_html_e( 'Laisser vide', 'bagxpro' ); ?></label>
							<input type="text" name="bagxpro_hp" id="bagxpro-hp" value="" tabindex="-1" autocomplete="off">
						</div>
						<input
							type="file"
							name="bagxpro_preview_capture"
							id="bagxpro-preview-capture"
							class="bagxpro-visually-hidden"
							accept="image/jpeg"
							tabindex="-1"
							aria-hidden="true"
						>

					<div class="bagxpro-logo-zone" data-bagxpro-logo-zone>
						<input
							type="file"
							id="bagxpro-logo"
							class="bagxpro-logo-zone__input"
							name="bagxpro_logo"
							accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp"
							data-bagxpro-logo-input
						>
						<div class="bagxpro-logo-zone__visual">
							<p class="bagxpro-logo-zone__title"><?php esc_html_e( 'AJOUTEZ VOTRE LOGO', 'bagxpro' ); ?></p>
							<p class="bagxpro-logo-zone__hint"><?php esc_html_e( 'Drag ou clique pour uploader', 'bagxpro' ); ?></p>
							<span class="bagxpro-logo-zone__cta" tabindex="-1">
								<svg class="bagxpro-logo-zone__cta-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
									<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
								</svg>
								<?php esc_html_e( 'IMPORTER MON LOGO', 'bagxpro' ); ?>
							</span>
						</div>
					</div>
					<button type="button" class="bagxpro-logo-clear" data-bagxpro-logo-clear hidden>
						<?php esc_html_e( 'Retirer le logo', 'bagxpro' ); ?>
					</button>

					<div class="bagxpro-field bagxpro-field--straps" data-bagxpro-straps>
						<span class="bagxpro-field__label" id="bagxpro-straps-label"><?php esc_html_e( 'Couleur des sangles', 'bagxpro' ); ?></span>
						<div class="bagxpro-swatches" role="radiogroup" aria-labelledby="bagxpro-straps-label">
						<?php
						if ( ! empty( $product_color_rows ) ) :
							foreach ( $product_color_rows as $idx => $crow ) {
								$hex            = isset( $crow['hex'] ) ? $crow['hex'] : '';
								$color_name     = ! empty( $crow['name'] ) ? $crow['name'] : '';
								$strap_label    = ! empty( $crow['label'] ) ? $crow['label'] : $color_name;
								if ( '' === $color_name ) {
									$color_name = $strap_label;
								}
								if ( '' === $strap_label ) {
									$strap_label = sprintf( /* translators: %d: index */ __( 'Couleur %d', 'bagxpro' ), $idx + 1 );
								}
								if ( '' === $color_name ) {
									$color_name = $strap_label;
								}
								$is_first       = ( 0 === $idx );
								$selected_class = $is_first ? ' is-selected' : '';
								$aria_pressed   = $is_first ? 'true' : 'false';
								$style_bg       = $hex ? 'background-color:' . esc_attr( $hex ) . ';' : '';
								?>
								<button
									type="button"
									class="bagxpro-swatch<?php echo esc_attr( $selected_class ); ?>"
									style="<?php echo esc_attr( $style_bg ); ?>"
									data-layer-index="<?php echo (int) $idx; ?>"
									data-strap-label="<?php echo esc_attr( $strap_label ); ?>"
									data-bagxpro-color-name="<?php echo esc_attr( $color_name ); ?>"
									aria-pressed="<?php echo esc_attr( $aria_pressed ); ?>"
									aria-label="<?php echo esc_attr( $color_name ); ?>"
								></button>
								<?php
							}
						endif;
						?>
						</div>
					</div>


					<?php
					$bagxpro_quantity_tier_options = function_exists( 'bagxpro_produit_quantity_tier_options' )
						? bagxpro_produit_quantity_tier_options()
						: array(
							'500'  => __( '500 pièces', 'bagxpro' ),
							'1000' => __( '1 000 pièces', 'bagxpro' ),
							'libre'  => __( 'Quantité libre', 'bagxpro' ),
						);
					$bagxpro_print_faces_options = function_exists( 'bagxpro_produit_print_faces_options' )
						? bagxpro_produit_print_faces_options()
						: array(
							'2' => __( 'Impression 2 faces', 'bagxpro' ),
							'4' => __( 'Impression 4 faces', 'bagxpro' ),
						);
					$bagxpro_quantity_custom_min = max( 1, (int) apply_filters( 'bagxpro_produit_quantity_custom_min', 500 ) );
					?>
					<div class="bagxpro-field bagxpro-field--quantity">
						<label class="bagxpro-field__label" for="bagxpro-quantity-select"><?php esc_html_e( 'Nombre de sacs', 'bagxpro' ); ?></label>
						<div class="bagxpro-select-wrap">
							<select
								id="bagxpro-quantity-select"
								class="bagxpro-select"
								name="bagxpro_quantity_tier"
								data-bagxpro-quantity-select
								required
							>
								<?php foreach ( $bagxpro_quantity_tier_options as $tier_value => $tier_label ) : ?>
									<option value="<?php echo esc_attr( $tier_value ); ?>"<?php selected( $tier_value, '500' ); ?>>
										<?php echo esc_html( $tier_label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<fieldset class="bagxpro-field bagxpro-field--print-faces">
							<legend class="bagxpro-field__label"><?php esc_html_e( 'Type d’impression', 'bagxpro' ); ?></legend>
							<div class="bagxpro-radio-group" role="radiogroup" aria-label="<?php esc_attr_e( 'Type d’impression', 'bagxpro' ); ?>">
								<?php
								$print_faces_first = true;
								foreach ( $bagxpro_print_faces_options as $faces_value => $faces_label ) :
									$faces_id = 'bagxpro-print-faces-' . esc_attr( $faces_value );
									?>
									<label class="bagxpro-radio" for="<?php echo esc_attr( $faces_id ); ?>">
										<input
											type="radio"
											class="bagxpro-radio__input"
											name="bagxpro_print_faces"
											id="<?php echo esc_attr( $faces_id ); ?>"
											value="<?php echo esc_attr( $faces_value ); ?>"
											<?php checked( $print_faces_first ); ?>
											required
										>
										<span class="bagxpro-radio__label"><?php echo esc_html( $faces_label ); ?></span>
									</label>
									<?php
									$print_faces_first = false;
								endforeach;
								?>
							</div>
						</fieldset>

						<div class="bagxpro-quantity-custom" id="bagxpro-quantity-custom" hidden>
							<label class="bagxpro-quantity-custom__label" for="bagxpro-quantity-custom-input">
								<?php esc_html_e( 'Quantité souhaitée (pièces)', 'bagxpro' ); ?>
							</label>
							<input
								type="number"
								class="bagxpro-input bagxpro-quantity-custom__input"
								id="bagxpro-quantity-custom-input"
								name="bagxpro_quantity_custom"
								min="<?php echo (int) $bagxpro_quantity_custom_min; ?>"
								step="1"
								inputmode="numeric"
								placeholder="<?php echo esc_attr( sprintf( /* translators: %d: minimum pieces */ __( 'À partir de %d', 'bagxpro' ), $bagxpro_quantity_custom_min ) ); ?>"
							>
						</div>
					</div>

					<div class="bagxpro-produit-panel__contact" data-bagxpro-contact>
						<div class="bagxpro-field">
							<label class="bagxpro-field__label" for="bagxpro-societe"><?php esc_html_e( 'Nom de la société', 'bagxpro' ); ?></label>
							<input
								type="text"
								class="bagxpro-input"
								id="bagxpro-societe"
								name="bagxpro_societe"
								autocomplete="organization"
								maxlength="120"
								required
							>
						</div>
						<div class="bagxpro-field">
							<label class="bagxpro-field__label" for="bagxpro-nom"><?php esc_html_e( 'Nom', 'bagxpro' ); ?></label>
							<input
								type="text"
								class="bagxpro-input"
								id="bagxpro-nom"
								name="bagxpro_nom"
								autocomplete="family-name"
								required
							>
						</div>
						<div class="bagxpro-field">
							<label class="bagxpro-field__label" for="bagxpro-prenom"><?php esc_html_e( 'Prénom', 'bagxpro' ); ?></label>
							<input
								type="text"
								class="bagxpro-input"
								id="bagxpro-prenom"
								name="bagxpro_prenom"
								autocomplete="given-name"
								required
							>
						</div>
						<div class="bagxpro-field">
							<label class="bagxpro-field__label" for="bagxpro-email"><?php esc_html_e( 'E-mail', 'bagxpro' ); ?></label>
							<input
								type="email"
								class="bagxpro-input"
								id="bagxpro-email"
								name="bagxpro_email"
								autocomplete="email"
								inputmode="email"
								required
							>
						</div>
						<div class="bagxpro-field">
							<label class="bagxpro-field__label" for="bagxpro-telephone"><?php esc_html_e( 'Téléphone', 'bagxpro' ); ?></label>
							<input
								type="tel"
								class="bagxpro-input"
								id="bagxpro-telephone"
								name="bagxpro_telephone"
								autocomplete="tel"
								inputmode="tel"
							>
						</div>
					</div>

					<div class="bagxpro-field bagxpro-rgpd">
						<p class="bagxpro-rgpd__text">
							<?php esc_html_e( 'Les informations collectées (identité, coordonnées, fichiers joints du configurateur) sont nécessaires au traitement de votre demande. Elles sont conservées sans limite de durée, jusqu’à exercice de vos droits (accès, rectification, effacement, limitation du traitement, opposition, portabilité) ou obligation légale, conformément au règlement (UE) 2016/679 (RGPD).', 'bagxpro' ); ?>
						</p>
						<?php
						$bagxpro_privacy_url = function_exists( 'wp_get_privacy_policy_url' ) ? wp_get_privacy_policy_url() : '';
						if ( $bagxpro_privacy_url ) :
							?>
							<p class="bagxpro-rgpd__privacy">
								<a class="bagxpro-rgpd__privacy-link" href="<?php echo esc_url( $bagxpro_privacy_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Politique de confidentialité', 'bagxpro' ); ?></a>
							</p>
						<?php endif; ?>
						<label class="bagxpro-rgpd__consent" for="bagxpro-rgpd-consent">
							<input
								type="checkbox"
								name="bagxpro_rgpd_consent"
								id="bagxpro-rgpd-consent"
								class="bagxpro-rgpd__checkbox"
								value="1"
								required
							>
							<span class="bagxpro-rgpd__consent-text"><?php esc_html_e( 'Je confirme avoir pris connaissance de l’information ci-dessus et accepter le traitement de mes données personnelles dans ce cadre.', 'bagxpro' ); ?></span>
						</label>
					</div>

					<button type="submit" class="bagxpro-btn-order">
						<?php esc_html_e( 'COMMANDER MON BIG BAG', 'bagxpro' ); ?>
					</button>
					</form>

					<?php if ( get_the_content() ) : ?>
						<div class="bagxpro-produit-panel__content entry-content">
							<?php the_content(); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</main>

	<?php
endwhile;
?>

<?php
if ( have_rows( 'page' ) ) :
	while ( have_rows( 'page' ) ) :
		the_row();
		get_template_part( 'template-parts/strates/' . get_row_layout() );
	endwhile;
endif;
?>

<?php
get_footer();
