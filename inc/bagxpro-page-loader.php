<?php
/**
 * Loader plein écran (logo centré) au chargement des pages front.
 */

function bagxpro_get_page_loader_logo() {
	$url = '';
	$alt = get_bloginfo( 'name' );

	if ( function_exists( 'get_field' ) ) {
		$logo = get_field( 'option_logo_primary', 'option' );
		if ( $logo && function_exists( 'get_custom_thumb' ) ) {
			$logo_array = get_custom_thumb( $logo, 'full' );
			if ( is_array( $logo_array ) && ! empty( $logo_array['url'] ) ) {
				$url = $logo_array['url'];
				if ( ! empty( $logo_array['alt'] ) ) {
					$alt = $logo_array['alt'];
				}
			}
		}
	}

	if ( '' === $url ) {
		$url = get_stylesheet_directory_uri() . '/styles/img/logo-mansory.svg';
	}

	return array(
		'url' => $url,
		'alt' => $alt,
	);
}

function bagxpro_should_show_page_loader() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}

	return true;
}

function bagxpro_page_loader_body_class( $classes ) {
	if ( ! bagxpro_should_show_page_loader() ) {
		return $classes;
	}

	$classes[] = 'bagxpro-is-loading';

	return $classes;
}
add_filter( 'body_class', 'bagxpro_page_loader_body_class' );

function bagxpro_page_loader_critical_css() {
	if ( ! bagxpro_should_show_page_loader() ) {
		return;
	}
	?>
<style id="bagxpro-page-loader-critical">
body.bagxpro-is-loading {
	overflow: hidden;
}
.bagxpro-page-loader {
	position: fixed;
	inset: 0;
	z-index: 99999;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #f1f1f1;
	opacity: 1;
	visibility: visible;
	transition: opacity 0.45s ease, visibility 0.45s ease;
}
.bagxpro-page-loader.is-hidden {
	opacity: 0;
	visibility: hidden;
	pointer-events: none;
}
</style>
	<?php
}
add_action( 'wp_head', 'bagxpro_page_loader_critical_css', 1 );

function bagxpro_render_page_loader() {
	if ( ! bagxpro_should_show_page_loader() ) {
		return;
	}

	$logo = bagxpro_get_page_loader_logo();
	?>
<div id="bagxpro-page-loader" class="bagxpro-page-loader" role="presentation" aria-hidden="true">
	<div class="bagxpro-page-loader__inner">
		<span class="bagxpro-page-loader__ring" aria-hidden="true"></span>
		<img
			src="<?php echo esc_url( $logo['url'] ); ?>"
			class="bagxpro-page-loader__logo"
			alt=""
			width="160"
			height="48"
			decoding="async"
		>
	</div>
</div>
	<?php
}
