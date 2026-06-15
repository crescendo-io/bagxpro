<?php
/**
 * Strate : grille produits (CPT produit).
 *
 * Usage :
 *   get_template_part( 'template-parts/strates/list_products' );
 *   get_template_part( 'template-parts/strates/list_products', null, array(
 *     'intro_title' => 'Mon titre',
 *     'intro_text'  => 'Mon paragraphe (HTML autorisé via wp_kses_post).',
 *   ) );
 *
 * Filtres :
 *   bagxpro_list_products_intro_title
 *   bagxpro_list_products_intro_text
 *   bagxpro_list_products_query_args
 *
 * ACF :
 *   Options Apparence → Grille produits : bagxpro_lp_options_intro_title, bagxpro_lp_options_intro_text
 *   Fiche produit (champ) : bagxpro_product_card_description
 *
 * @package bagxpro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bagxpro_lp_passed = ( isset( $args ) && is_array( $args ) ) ? $args : array();

$bagxpro_lp_defaults = array(
	'intro_title' => apply_filters(
		'bagxpro_list_products_intro_title',
		__( 'Nos big bags', 'bagxpro' )
	),
	'intro_text'  => apply_filters(
		'bagxpro_list_products_intro_text',
		__( 'Découvrez notre gamme et choisissez le Big Bag adapté à votre activité.', 'bagxpro' )
	),
);

if ( function_exists( 'get_field' ) ) {
	$bagxpro_opt_title = get_field( 'bagxpro_lp_options_intro_title', 'option' );
	if ( is_string( $bagxpro_opt_title ) && '' !== trim( $bagxpro_opt_title ) ) {
		$bagxpro_lp_defaults['intro_title'] = $bagxpro_opt_title;
	}
	$bagxpro_opt_text = get_field( 'bagxpro_lp_options_intro_text', 'option' );
	if ( is_string( $bagxpro_opt_text ) && '' !== trim( $bagxpro_opt_text ) ) {
		$bagxpro_lp_defaults['intro_text'] = $bagxpro_opt_text;
	}
}

$bagxpro_lp = wp_parse_args( $bagxpro_lp_passed, $bagxpro_lp_defaults );

$bagxpro_lp_query_args = apply_filters(
	'bagxpro_list_products_query_args',
		array(
		'post_type'              => 'produit',
		'post_status'            => 'publish',
		'posts_per_page'         => -1,
		'orderby'                => 'date',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	)
);

$bagxpro_products = new WP_Query( $bagxpro_lp_query_args );

$bagxpro_fallback_img = get_stylesheet_directory_uri() . '/images/product.png';
?>

<div class="strate-products">
	<div class="container">
		<div class="row">
			<div class="col-sm-6">
				<div class="card-intro-product">
					<?php if ( ! empty( $bagxpro_lp['intro_title'] ) ) : ?>
						<h2><?php echo esc_html( $bagxpro_lp['intro_title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $bagxpro_lp['intro_text'] ) ) : ?>
						<div class="card-intro-product__text">
							<?php echo wp_kses_post( wpautop( $bagxpro_lp['intro_text'] ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $bagxpro_products->have_posts() ) : ?>
				<?php
				while ( $bagxpro_products->have_posts() ) :
					$bagxpro_products->the_post();
					$bagxpro_pid = get_the_ID();
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
						$bagxpro_desc = has_excerpt()
							? get_the_excerpt()
							: wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $bagxpro_pid ) ), 22, '…' );
					}
					?>
					<div class="col-sm-3">
						<article class="card-product">
							<div class="img-product">
								<?php bagxpro_render_product_bestseller_tag( $bagxpro_pid ); ?>
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
			else :
				?>
				<div class="col-sm-6">
					<p class="card-intro-product card-intro-product--empty"><?php esc_html_e( 'Aucun produit à afficher pour le moment.', 'bagxpro' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
