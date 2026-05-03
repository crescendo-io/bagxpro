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
			$product_color_rows[] = array(
				'hex'   => $hex,
				'url'   => $image_url,
				'label' => isset( $row['product_color_label'] ) ? $row['product_color_label'] : '',
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
					<header class="bagxpro-produit-panel__header">
						<h1><?php the_title(); ?></h1>
						<?php if ( has_excerpt() ) : ?>
							<p class="bagxpro-produit-panel__specs"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
						<?php endif; ?>
					</header>

					<div class="bagxpro-logo-zone" data-bagxpro-logo-zone>
						<input
							type="file"
							id="bagxpro-logo"
							class="bagxpro-logo-zone__input"
							name="bagxpro_logo"
							accept="image/*"
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
								$label          = ! empty( $crow['label'] ) ? $crow['label'] : sprintf( /* translators: %d: index */ __( 'Couleur %d', 'bagxpro' ), $idx + 1 );
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
									aria-pressed="<?php echo esc_attr( $aria_pressed ); ?>"
									aria-label="<?php echo esc_attr( $label ); ?>"
								></button>
								<?php
							}
						endif;
						?>
						</div>
					</div>

					<div class="bagxpro-field bagxpro-field--quantity">
						<label class="bagxpro-field__label" for="bagxpro-quantity-select"><?php esc_html_e( 'Nombre de sacs', 'bagxpro' ); ?></label>
						<div class="bagxpro-select-wrap">
							<select id="bagxpro-quantity-select" class="bagxpro-select" name="bagxpro_quantity_tier">
								<option value="100"><?php esc_html_e( '100 sacs (à partir de 120€ H.T)', 'bagxpro' ); ?></option>
								<option value="500" selected><?php esc_html_e( '500 sacs (à partir de 400€ H.T)', 'bagxpro' ); ?></option>
								<option value="1000"><?php esc_html_e( '1 000 sacs (sur devis)', 'bagxpro' ); ?></option>
							</select>
						</div>
					</div>

					<div class="bagxpro-produit-panel__contact" data-bagxpro-contact>
						<div class="bagxpro-field">
							<label class="bagxpro-field__label" for="bagxpro-nom"><?php esc_html_e( 'Nom', 'bagxpro' ); ?></label>
							<input
								type="text"
								class="bagxpro-input"
								id="bagxpro-nom"
								name="bagxpro_nom"
								autocomplete="family-name"
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

					<button type="button" class="bagxpro-btn-order">
						<?php esc_html_e( 'COMMANDER MON BIG BAG', 'bagxpro' ); ?>
					</button>

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
