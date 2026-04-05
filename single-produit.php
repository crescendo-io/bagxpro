<?php
/**
 * Fiche produit big bag : aperçu logo, quantité, upload (client uniquement).
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
	?>

<main class="bagxpro-single-produit" id="bagxpro-produit-<?php echo (int) get_the_ID(); ?>" data-bagxpro-product-page>
	<div class="container">
		<?php if ( function_exists( 'custom_breadcrumb' ) ) : ?>
			<div class="bagxpro-single-produit__breadcrumb">
				<?php custom_breadcrumb(); ?>
			</div>
		<?php endif; ?>

		<div class="row bagxpro-single-produit__row">
			<div class="col-sm-6">
				<div class="bagxpro-bag-preview" id="bagxpro-bag-preview">
					<img
						class="bagxpro-bag-preview__bag"
						src="<?php echo esc_url( $bag_src ); ?>"
						alt="<?php echo esc_attr( $bag_alt ); ?>"
						loading="eager"
						decoding="async"
					>
					<div class="bagxpro-bag-preview__logo" id="bagxpro-bag-logo" aria-hidden="true" hidden></div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="bagxpro-produit-panel">
					<h1 class="bagxpro-produit-panel__title"><?php the_title(); ?></h1>

					<?php if ( has_excerpt() ) : ?>
						<div class="bagxpro-produit-panel__excerpt">
							<?php the_excerpt(); ?>
						</div>
					<?php endif; ?>

					<div class="bagxpro-produit-panel__fields">
						<div class="bagxpro-field">
							<label class="bagxpro-field__label" for="bagxpro-quantity"><?php esc_html_e( 'Quantité souhaitée', 'bagxpro' ); ?></label>
							<input
								class="bagxpro-field__input bagxpro-field__input--number"
								type="number"
								id="bagxpro-quantity"
								name="bagxpro_quantity"
								min="1"
								step="1"
								value="1"
							>
						</div>

						<div class="bagxpro-field">
							<label class="bagxpro-field__label" for="bagxpro-logo"><?php esc_html_e( 'Votre logo', 'bagxpro' ); ?></label>
							<input
								class="bagxpro-field__input bagxpro-field__input--file"
								type="file"
								id="bagxpro-logo"
								name="bagxpro_logo"
								accept="image/*"
								data-bagxpro-logo-input
							>
							<p class="bagxpro-field__hint">
								<?php esc_html_e( 'PNG, JPG ou SVG recommandés. L’aperçu est indicatif (rendu sur le sac simulé dans le navigateur).', 'bagxpro' ); ?>
							</p>
							<button type="button" class="bagxpro-btn bagxpro-btn--ghost" data-bagxpro-logo-clear hidden>
								<?php esc_html_e( 'Retirer le logo', 'bagxpro' ); ?>
							</button>
						</div>
					</div>

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

get_footer();
