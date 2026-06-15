<?php
/**
 * Template Name: Home
 *
 * Contenus pilotés par ACF sur la page définie comme page de front (Réglages → Lecture).
 *
 * @package bagxpro
 */

get_header();

$bagxpro_home_id = bagxpro_get_front_page_id();
$bagxpro_gf       = function_exists( 'get_field' );
$bagxpro_theme_uri = get_stylesheet_directory_uri();
$bagxpro_theme_dir = get_stylesheet_directory();

/**
 * Image de secours si fichier présent dans le thème.
 *
 * @param string $relative ex. images/bag.png
 */
$bagxpro_img_fallback = static function ( $relative ) use ( $bagxpro_theme_dir, $bagxpro_theme_uri ) {
	$path = $bagxpro_theme_dir . '/' . ltrim( $relative, '/' );
	return file_exists( $path ) ? $bagxpro_theme_uri . '/' . ltrim( $relative, '/' ) : '';
};

/* Hero */
$bagxpro_hero_bg = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_hero_background', $bagxpro_home_id ) : false;
$bagxpro_hero_bg_url = ( is_array( $bagxpro_hero_bg ) && ! empty( $bagxpro_hero_bg['url'] ) )
	? $bagxpro_hero_bg['url']
	: apply_filters( 'bagxpro_home_default_hero_background_url', '' );

$bagxpro_hero_title = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_hero_title', $bagxpro_home_id ) : '';
if ( ! is_string( $bagxpro_hero_title ) || '' === trim( $bagxpro_hero_title ) ) {
	$bagxpro_hero_title = "Personnalisez votre Big Bag,\nBoostez votre business";
}

$bagxpro_hero_lead = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_hero_lead', $bagxpro_home_id ) : '';
if ( ! is_string( $bagxpro_hero_lead ) || '' === trim( $bagxpro_hero_lead ) ) {
	$bagxpro_hero_lead = "Vos Big Bags deviennent votre meilleure publicité :\naffichez fièrement les couleurs de votre entreprise";
}

$bagxpro_hero_btn = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_hero_button', $bagxpro_home_id ) : false;
if ( ! is_array( $bagxpro_hero_btn ) ) {
	$bagxpro_hero_btn = array();
}
$bagxpro_hero_btn = wp_parse_args(
	$bagxpro_hero_btn,
	array(
		'url'    => '#',
		'title'  => __( 'NOUS DECOUVRIR', 'bagxpro' ),
		'target' => '',
	)
);

$bagxpro_hero_bag = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_hero_bag_image', $bagxpro_home_id ) : false;
$bagxpro_hero_bag_url = ( is_array( $bagxpro_hero_bag ) && ! empty( $bagxpro_hero_bag['url'] ) )
	? $bagxpro_hero_bag['url']
	: $bagxpro_img_fallback( 'images/bag.png' );
if ( '' === $bagxpro_hero_bag_url ) {
	$bagxpro_hero_bag_url = $bagxpro_theme_uri . '/images/placeholder.svg';
}

$bagxpro_hero_ph = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_hero_placeholder_image', $bagxpro_home_id ) : false;
$bagxpro_hero_ph_url = ( is_array( $bagxpro_hero_ph ) && ! empty( $bagxpro_hero_ph['url'] ) )
	? $bagxpro_hero_ph['url']
	: $bagxpro_theme_uri . '/images/placeholder.svg';

/* Logos */
$bagxpro_logos_heading = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_logos_heading', $bagxpro_home_id ) : '';
if ( ! is_string( $bagxpro_logos_heading ) || '' === trim( $bagxpro_logos_heading ) ) {
	$bagxpro_logos_heading = __( 'Ils nous font déjà confiance', 'bagxpro' );
}
$bagxpro_logos = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_logos', $bagxpro_home_id ) : false;
if ( ! is_array( $bagxpro_logos ) ) {
	$bagxpro_logos = array();
}
if ( empty( $bagxpro_logos ) ) {
	$fallback_logo = $bagxpro_img_fallback( 'images/logo-partenaire.png' );
	if ( '' === $fallback_logo ) {
		$fallback_logo = $bagxpro_theme_uri . '/images/placeholder.svg';
	}
	$bagxpro_logos = array_fill( 0, 5, array( 'image' => array( 'url' => $fallback_logo, 'alt' => '' ) ) );
}

/* Stats */
$bagxpro_merge_stat = static function ( $acf_group, $defaults ) {
	if ( ! is_array( $acf_group ) ) {
		$acf_group = array();
	}
	return wp_parse_args( $acf_group, $defaults );
};

$bagxpro_s1 = $bagxpro_merge_stat(
	( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_stat_card_1', $bagxpro_home_id ) : array(),
	array(
		'number'      => '3000',
		'label'       => __( 'vues par jour', 'bagxpro' ),
		'description' => __( 'Visibilité d’une marque sur un chantier', 'bagxpro' ),
	)
);
$bagxpro_s2 = $bagxpro_merge_stat(
	( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_stat_card_2', $bagxpro_home_id ) : array(),
	array(
		'number'      => '5700',
		'label'       => __( 'heures sur votre chantier', 'bagxpro' ),
		'description' => __( 'Un commercial H24 qui ne vous coute rien', 'bagxpro' ),
	)
);
$bagxpro_s3 = $bagxpro_merge_stat(
	( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_stat_card_3', $bagxpro_home_id ) : array(),
	array(
		'number'      => '1500',
		'label'       => __( 'économisez sur l’évacuation de vos déchets par chantier', 'bagxpro' ),
		'description' => __( 'Grace à l’Eco-contribution déjà payée à vos partenaires fournisseurs', 'bagxpro' ),
	)
);

$bagxpro_stat_center = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_stat_center_image', $bagxpro_home_id ) : false;
$bagxpro_stat_center_url = ( is_array( $bagxpro_stat_center ) && ! empty( $bagxpro_stat_center['url'] ) )
	? $bagxpro_stat_center['url']
	: $bagxpro_img_fallback( 'images/placeholder-home-cards.png' );
if ( '' === $bagxpro_stat_center_url ) {
	$bagxpro_stat_center_url = $bagxpro_theme_uri . '/images/placeholder.svg';
}

/* Présentation */
$bagxpro_pres_img = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_presentation_image', $bagxpro_home_id ) : false;
$bagxpro_pres_img_url = ( is_array( $bagxpro_pres_img ) && ! empty( $bagxpro_pres_img['url'] ) )
	? $bagxpro_pres_img['url']
	: $bagxpro_img_fallback( 'images/bag.png' );
if ( '' === $bagxpro_pres_img_url ) {
	$bagxpro_pres_img_url = $bagxpro_theme_uri . '/images/placeholder.svg';
}

$bagxpro_pres_title = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_presentation_title', $bagxpro_home_id ) : '';
if ( ! is_string( $bagxpro_pres_title ) || '' === trim( $bagxpro_pres_title ) ) {
	$bagxpro_pres_title = __( 'Comment ça marche', 'bagxpro' );
}

$bagxpro_steps = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_presentation_steps', $bagxpro_home_id ) : false;
if ( ! is_array( $bagxpro_steps ) || empty( $bagxpro_steps ) ) {
	$bagxpro_steps = array(
		array(
			'title' => __( 'Choisissez votre produit suivant votre environnement', 'bagxpro' ),
			'text'  => __( 'BTP, Agriculture, Industrie, Artisans, Jardinage, Bois, Déchets, etc...', 'bagxpro' ),
		),
		array(
			'title' => __( 'Personnalisez-le', 'bagxpro' ),
			'text'  => __( 'Chargez votre logo dans notre simulateur, choisissez vos modèles et vos quantités', 'bagxpro' ),
		),
		array(
			'title' => __( 'Commandez-le', 'bagxpro' ),
			'text'  => __( 'Contactez-nous dès maintenant : nos équipes vous envoient rapidement un devis sur mesure ainsi que les délais de livraison', 'bagxpro' ),
		),
	);
}

$bagxpro_pres_cta = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_presentation_cta', $bagxpro_home_id ) : false;
if ( ! is_array( $bagxpro_pres_cta ) ) {
	$bagxpro_pres_cta = array();
}
$bagxpro_archive = get_post_type_archive_link( 'produit' );
$bagxpro_pres_cta = wp_parse_args(
	$bagxpro_pres_cta,
	array(
		'url'    => $bagxpro_archive ? $bagxpro_archive : '#',
		'title'  => __( 'Voir nos produits', 'bagxpro' ),
		'target' => '',
	)
);

/* Liste produits (strate) */
$bagxpro_lp_title = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_products_intro_title', $bagxpro_home_id ) : '';
$bagxpro_lp_text  = ( $bagxpro_gf && $bagxpro_home_id ) ? get_field( 'home_products_intro_text', $bagxpro_home_id ) : '';
$bagxpro_list_args = array();
if ( is_string( $bagxpro_lp_title ) && '' !== trim( $bagxpro_lp_title ) ) {
	$bagxpro_list_args['intro_title'] = $bagxpro_lp_title;
}
if ( is_string( $bagxpro_lp_text ) && '' !== trim( $bagxpro_lp_text ) ) {
	$bagxpro_list_args['intro_text'] = $bagxpro_lp_text;
}

/**
 * Extrait un entier depuis un chiffre affiché (ex. "3 000" → 3000).
 * Conservé pour compatibilité ; préférer bagxpro_parse_stat_number().
 *
 * @param mixed $raw Valeur ACF / texte.
 * @return int
 */
$bagxpro_stat_to_int = static function ( $raw ) {
	return bagxpro_parse_stat_number( $raw )['value'];
};
?>

<div class="strate-hero full home">
	<?php if ( $bagxpro_hero_bg_url ) : ?>
		<img src="<?php echo esc_url( $bagxpro_hero_bg_url ); ?>" class="strate-hero_image" alt="" width="1728" height="1110">
	<?php endif; ?>
	<div class="strate-hero_inner">
		<h1><?php echo nl2br( esc_html( $bagxpro_hero_title ) ); ?></h1>
		<p><?php echo nl2br( esc_html( $bagxpro_hero_lead ) ); ?></p>

		<?php if ( ! empty( $bagxpro_hero_btn['url'] ) && ! empty( $bagxpro_hero_btn['title'] ) ) : ?>
			<div class="container-buttons">
				<a
					href="<?php echo esc_url( $bagxpro_hero_btn['url'] ); ?>"
					class="button primary"
					<?php echo ! empty( $bagxpro_hero_btn['target'] ) ? ' target="' . esc_attr( $bagxpro_hero_btn['target'] ) . '" rel="noopener noreferrer"' : ''; ?>
				><?php echo esc_html( $bagxpro_hero_btn['title'] ); ?></a>
			</div>
		<?php endif; ?>
	</div>

	<div class="container-bag-home">
		<?php if ( $bagxpro_hero_bag_url ) : ?>
			<img src="<?php echo esc_url( $bagxpro_hero_bag_url ); ?>" class="bag-img" alt="">
		<?php endif; ?>
		<?php if ( $bagxpro_hero_ph_url ) : ?>
			<img
				src="<?php echo esc_url( $bagxpro_hero_ph_url ); ?>"
				class="placeholder bagxpro-hero-placeholder-trigger"
				alt=""
				role="button"
				tabindex="0"
				aria-label="<?php echo esc_attr__( 'Ouvrir le configurateur', 'bagxpro' ); ?>"
			>
		<?php endif; ?>
	</div>
</div>

<main id="bagxpro-home-main" class="bagxpro-home-main">

<div class="list-logo">
	<ul>
		<li class="label">
			<?php echo esc_html( $bagxpro_logos_heading ); ?>
		</li>
		<?php foreach ( $bagxpro_logos as $bagxpro_logo_row ) : ?>
			<?php
			$bagxpro_li_img = '';
			$bagxpro_li_alt = '';
			if ( isset( $bagxpro_logo_row['image'] ) && is_array( $bagxpro_logo_row['image'] ) && ! empty( $bagxpro_logo_row['image']['url'] ) ) {
				$bagxpro_li_img = $bagxpro_logo_row['image']['url'];
				$bagxpro_li_alt = ! empty( $bagxpro_logo_row['image']['alt'] ) ? $bagxpro_logo_row['image']['alt'] : '';
			}
			if ( '' === $bagxpro_li_img ) {
				continue;
			}
			?>
			<li>
				<img src="<?php echo esc_url( $bagxpro_li_img ); ?>" alt="<?php echo esc_attr( $bagxpro_li_alt ); ?>" loading="lazy" decoding="async">
			</li>
		<?php endforeach; ?>
	</ul>
</div>

<div class="section-cards" data-bagxpro-stats>
	<div class="container">
		<div class="row">
			<div class="col-sm-3">
				<div class="card-border">
					<?php bagxpro_render_card_number( $bagxpro_s1['number'] ); ?>
					<div class="card-label"><?php echo esc_html( $bagxpro_s1['label'] ); ?></div>
					<div class="card-description">
						<?php echo esc_html( $bagxpro_s1['description'] ); ?>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="card-border">
					<?php bagxpro_render_card_number( $bagxpro_s2['number'] ); ?>
					<div class="card-label"><?php echo esc_html( $bagxpro_s2['label'] ); ?></div>
					<div class="card-description">
						<?php echo esc_html( $bagxpro_s2['description'] ); ?>
					</div>
				</div>
			</div>

			<div class="col-sm-3">
				<div class="container-image-cards-placeholder">
					<img src="<?php echo esc_url( $bagxpro_stat_center_url ); ?>" class="img-card" alt="" loading="lazy" decoding="async">
				</div>
			</div>
			<div class="col-sm-3">
				<div class="card-border">
					<?php bagxpro_render_card_number( $bagxpro_s3['number'] ); ?>
					<div class="card-label"><?php echo esc_html( $bagxpro_s3['label'] ); ?></div>
					<div class="card-description">
						<?php echo esc_html( $bagxpro_s3['description'] ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="strate-presentation">
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="container-white">
					<div class="row">
						<div class="col-sm-6">
							<img src="<?php echo esc_url( $bagxpro_pres_img_url ); ?>" class="img" alt="" loading="lazy" decoding="async">
						</div>
						<div class="col-sm-6">
							<div class="container-text">
								<h2><?php echo esc_html( $bagxpro_pres_title ); ?></h2>

								<ul>
									<?php foreach ( $bagxpro_steps as $bagxpro_step ) : ?>
										<?php
										if ( empty( $bagxpro_step['title'] ) ) {
											continue;
										}
										?>
										<li>
											<h3><?php echo esc_html( $bagxpro_step['title'] ); ?></h3>
											<?php if ( ! empty( $bagxpro_step['text'] ) ) : ?>
												<p><?php echo esc_html( $bagxpro_step['text'] ); ?></p>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
									<?php if ( ! empty( $bagxpro_pres_cta['url'] ) && ! empty( $bagxpro_pres_cta['title'] ) ) : ?>
										<li>
											<a
												href="<?php echo esc_url( $bagxpro_pres_cta['url'] ); ?>"
												class="button primary"
												<?php echo ! empty( $bagxpro_pres_cta['target'] ) ? ' target="' . esc_attr( $bagxpro_pres_cta['target'] ) . '" rel="noopener noreferrer"' : ''; ?>
											><?php echo esc_html( $bagxpro_pres_cta['title'] ); ?></a>
										</li>
									<?php endif; ?>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php get_template_part( 'template-parts/strates/list_products', null, $bagxpro_list_args ); ?>

<?php
if ( $bagxpro_home_id && function_exists( 'have_rows' ) && have_rows( 'page', $bagxpro_home_id ) ) :
	while ( have_rows( 'page', $bagxpro_home_id ) ) :
		the_row();
		get_template_part( 'template-parts/strates/' . get_row_layout() );
	endwhile;
endif;
?>

</main>

<?php get_footer(); ?>
