<?php
/**
 * Produits similaires (autres fiches produit).
 *
 * @package bagxpro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bagxpro_related_args = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$bagxpro_product_id   = ! empty( $bagxpro_related_args['product_id'] )
	? (int) $bagxpro_related_args['product_id']
	: bagxpro_get_related_products_exclude_id();

$bagxpro_query_args = array(
	'post_type'              => 'produit',
	'post_status'            => 'publish',
	'posts_per_page'         => (int) apply_filters( 'bagxpro_related_products_limit', 4 ),
	'orderby'                => 'rand',
	'no_found_rows'          => true,
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
);

if ( $bagxpro_product_id > 0 ) {
	$bagxpro_query_args['post__not_in'] = array( $bagxpro_product_id );
}

$bagxpro_related_query = new WP_Query(
	apply_filters(
		'bagxpro_related_products_query_args',
		$bagxpro_query_args,
		$bagxpro_product_id
	)
);

if ( ! $bagxpro_related_query->have_posts() ) {
	return;
}

$bagxpro_fallback_img = get_stylesheet_directory_uri() . '/images/product.png';
?>

<section class="strate-products bagxpro-related-products" aria-labelledby="bagxpro-related-products-title">
	<div class="container">
		<h2 id="bagxpro-related-products-title" class="bagxpro-related-products__title">
			<?php esc_html_e( 'Ces produits pourraient vous intéresser', 'bagxpro' ); ?>
		</h2>
		<div class="row bagxpro-related-products__grid">
			<?php
			while ( $bagxpro_related_query->have_posts() ) :
				$bagxpro_related_query->the_post();
				$bagxpro_pid   = get_the_ID();
				$bagxpro_thumb = get_the_post_thumbnail_url( $bagxpro_pid, 'medium_large' );
				if ( ! $bagxpro_thumb ) {
					$bagxpro_thumb = $bagxpro_fallback_img;
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
					$bagxpro_desc = has_excerpt( $bagxpro_pid )
						? get_the_excerpt( $bagxpro_pid )
						: wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $bagxpro_pid ) ), 22, '…' );
				}
				?>
				<div class="col-sm-3">
					<article class="card-product">
						<div class="img-product">
							<a href="<?php echo esc_url( get_permalink() ); ?>">
								<img
									src="<?php echo esc_url( $bagxpro_thumb ); ?>"
									alt="<?php echo esc_attr( $bagxpro_alt ); ?>"
									loading="lazy"
									decoding="async"
								>
							</a>
						</div>

						<div class="description-product">
							<h3><?php the_title(); ?></h3>
							<?php if ( '' !== $bagxpro_desc ) : ?>
								<p><?php echo nl2br( esc_html( $bagxpro_desc ) ); ?></p>
							<?php endif; ?>
							<a href="<?php echo esc_url( get_permalink() ); ?>" class="button primary">
								<?php esc_html_e( 'Choisir ce produit', 'bagxpro' ); ?>
							</a>
						</div>
					</article>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
