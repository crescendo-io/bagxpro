<?php
/**
 * Panneau latéral : liste produits pour le configurateur.
 *
 * @package bagxpro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bagxpro_sidebar = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$bagxpro_query   = isset( $bagxpro_sidebar['query'] ) && $bagxpro_sidebar['query'] instanceof WP_Query
	? $bagxpro_sidebar['query']
	: null;
$bagxpro_fallback = ! empty( $bagxpro_sidebar['fallback_img'] )
	? $bagxpro_sidebar['fallback_img']
	: get_stylesheet_directory_uri() . '/images/product.png';
?>

<div
	id="modal-configurator"
	class="bagxpro-configurator"
	hidden
	aria-hidden="true"
>
	<div class="bagxpro-configurator__overlay" data-bagxpro-configurator-close></div>

	<aside
		class="bagxpro-configurator__panel"
		role="dialog"
		aria-modal="true"
		aria-labelledby="bagxpro-configurator-title"
	>
		<header class="bagxpro-configurator__header">
			<h2 id="bagxpro-configurator-title"><?php esc_html_e( 'Choisir un produit', 'bagxpro' ); ?></h2>
			<button
				type="button"
				class="bagxpro-configurator__close"
				data-bagxpro-configurator-close
				aria-label="<?php esc_attr_e( 'Fermer', 'bagxpro' ); ?>"
			>
				<span aria-hidden="true">&times;</span>
			</button>
		</header>

		<div class="bagxpro-configurator__body">
			<?php if ( $bagxpro_query && $bagxpro_query->have_posts() ) : ?>
				<ul class="bagxpro-configurator__list">
					<?php
					while ( $bagxpro_query->have_posts() ) :
						$bagxpro_query->the_post();
						$bagxpro_pid   = get_the_ID();
						$bagxpro_thumb = get_the_post_thumbnail_url( $bagxpro_pid, 'thumbnail' );
						if ( ! $bagxpro_thumb ) {
							$bagxpro_thumb = $bagxpro_fallback;
						}
						$bagxpro_thumb_id = get_post_thumbnail_id( $bagxpro_pid );
						$bagxpro_alt      = $bagxpro_thumb_id ? get_post_meta( $bagxpro_thumb_id, '_wp_attachment_image_alt', true ) : '';
						if ( '' === $bagxpro_alt ) {
							$bagxpro_alt = get_the_title();
						}
						$bagxpro_desc = '';
						if ( function_exists( 'get_field' ) ) {
							$bagxpro_card_txt = get_field( 'bagxpro_product_card_description', $bagxpro_pid );
							if ( is_string( $bagxpro_card_txt ) && '' !== trim( $bagxpro_card_txt ) ) {
								$bagxpro_desc = trim( $bagxpro_card_txt );
							}
						}
						if ( '' === $bagxpro_desc ) {
							$bagxpro_desc = has_excerpt()
								? get_the_excerpt()
								: wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $bagxpro_pid ) ), 16, '…' );
						}
						?>
						<li class="bagxpro-configurator__item">
							<a class="bagxpro-configurator__link" href="<?php echo esc_url( get_permalink() ); ?>">
								<span class="bagxpro-configurator__thumb">
									<img
										src="<?php echo esc_url( $bagxpro_thumb ); ?>"
										alt="<?php echo esc_attr( $bagxpro_alt ); ?>"
										loading="lazy"
										decoding="async"
									>
								</span>
								<span class="bagxpro-configurator__content">
									<span class="bagxpro-configurator__name"><?php the_title(); ?></span>
									<?php if ( '' !== $bagxpro_desc ) : ?>
										<span class="bagxpro-configurator__desc"><?php echo esc_html( $bagxpro_desc ); ?></span>
									<?php endif; ?>
								</span>
							</a>
						</li>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			<?php else : ?>
				<p class="bagxpro-configurator__empty"><?php esc_html_e( 'Aucun produit disponible pour le moment.', 'bagxpro' ); ?></p>
			<?php endif; ?>
		</div>
	</aside>
</div>
