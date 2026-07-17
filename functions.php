<?php

require_once get_stylesheet_directory() . '/inc/lead-form.php';
require_once get_stylesheet_directory() . '/inc/bagxpro-produit-form-mailjet.php';
require_once get_stylesheet_directory() . '/inc/acf-front-page.php';
require_once get_stylesheet_directory() . '/inc/acf-list-products.php';
require_once get_stylesheet_directory() . '/inc/bagxpro-page-loader.php';
require_once get_stylesheet_directory() . '/inc/bagxpro-configurator-sidebar.php';
require_once get_stylesheet_directory() . '/inc/bagxpro-related-products.php';
require_once get_stylesheet_directory() . '/inc/bagxpro-contact-form.php';
require_once get_stylesheet_directory() . '/inc/acf-contact-page.php';

/**
 * Astérisque « champ obligatoire » (sans infobulle au survol).
 */
function bagxpro_the_required_field_mark() {
	echo '<span class="bagxpro-field__req" aria-hidden="true">*</span>';
}

/**
 * Légende « * Champs obligatoires » au-dessus d’un formulaire.
 */
function bagxpro_the_required_fields_legend() {
	echo '<p class="bagxpro-form-required-note">';
	echo '<span class="bagxpro-field__req" aria-hidden="true">*</span>';
	echo ' <span class="bagxpro-form-required-note__text">';
	esc_html_e( 'Champs obligatoires', 'bagxpro' );
	echo '</span></p>';
}

add_action( 'after_setup_theme', 'bagxpro_theme_support' );
function bagxpro_theme_support() {
	add_theme_support( 'post-thumbnails' );
}

/**
 * Désactive les archives auteur (/author/…) : redirection 301 vers l’accueil.
 */
add_action( 'template_redirect', 'bagxpro_disable_author_archives' );
function bagxpro_disable_author_archives() {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}

add_filter( 'author_rewrite_rules', '__return_empty_array' );

add_action( 'init', 'bagxpro_register_commande_post_type', 9 );
function bagxpro_register_commande_post_type() {
	$labels = array(
		'name'               => __( 'Commandes', 'bagxpro' ),
		'singular_name'      => __( 'Commande', 'bagxpro' ),
		'menu_name'          => __( 'Commandes', 'bagxpro' ),
		'add_new'            => __( 'Ajouter', 'bagxpro' ),
		'add_new_item'       => __( 'Ajouter une commande', 'bagxpro' ),
		'edit_item'          => __( 'Modifier la commande', 'bagxpro' ),
		'new_item'           => __( 'Nouvelle commande', 'bagxpro' ),
		'view_item'          => __( 'Voir la commande', 'bagxpro' ),
		'search_items'       => __( 'Rechercher des commandes', 'bagxpro' ),
		'not_found'          => __( 'Aucune commande', 'bagxpro' ),
		'not_found_in_trash' => __( 'Aucune commande dans la corbeille', 'bagxpro' ),
	);

	register_post_type(
		'commande',
		array(
			'labels'              => $labels,
			'description'         => __( 'Demandes envoyées depuis le formulaire produit.', 'bagxpro' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'menu_icon'           => 'dashicons-clipboard',
			'menu_position'       => 26,
			'supports'            => array( 'title', 'editor' ),
		)
	);
}

add_action( 'init', 'bagxpro_register_produit_post_type' );
function bagxpro_register_produit_post_type() {
	$labels = array(
		'name'               => __( 'Produits', 'bagxpro' ),
		'singular_name'      => __( 'Produit', 'bagxpro' ),
		'menu_name'          => __( 'Produits', 'bagxpro' ),
		'add_new'            => __( 'Ajouter', 'bagxpro' ),
		'add_new_item'       => __( 'Ajouter un produit', 'bagxpro' ),
		'edit_item'          => __( 'Modifier le produit', 'bagxpro' ),
		'new_item'           => __( 'Nouveau produit', 'bagxpro' ),
		'view_item'          => __( 'Voir le produit', 'bagxpro' ),
		'search_items'       => __( 'Rechercher des produits', 'bagxpro' ),
		'not_found'          => __( 'Aucun produit', 'bagxpro' ),
		'not_found_in_trash' => __( 'Aucun produit dans la corbeille', 'bagxpro' ),
	);

	register_post_type(
		'produit',
		array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'has_archive'         => 'produits',
			'rewrite'             => array( 'slug' => 'produit' ),
			'capability_type'     => 'post',
			'menu_icon'           => 'dashicons-cart',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		)
	);
}

add_filter( 'manage_edit-commande_posts_columns', 'bagxpro_commande_posts_columns' );
function bagxpro_commande_posts_columns( $columns ) {
	$new = array();
	if ( isset( $columns['cb'] ) ) {
		$new['cb'] = $columns['cb'];
	}
	$new['title'] = isset( $columns['title'] ) ? $columns['title'] : __( 'Titre', 'bagxpro' );
	$new['bagxpro_product'] = __( 'Produit', 'bagxpro' );
	$new['bagxpro_email']   = __( 'E-mail client', 'bagxpro' );
	$new['bagxpro_mailjet'] = __( 'Notification', 'bagxpro' );
	if ( isset( $columns['date'] ) ) {
		$new['date'] = $columns['date'];
	}
	return $new;
}

add_action( 'manage_commande_posts_custom_column', 'bagxpro_commande_posts_custom_column', 10, 2 );
function bagxpro_commande_posts_custom_column( $column, $post_id ) {
	if ( 'bagxpro_product' === $column ) {
		$pid = (int) get_post_meta( $post_id, '_bagxpro_product_id', true );
		if ( $pid && 'produit' === get_post_type( $pid ) ) {
			$link = get_edit_post_link( $pid );
			$name = get_the_title( $pid );
			if ( $link ) {
				echo '<a href="' . esc_url( $link ) . '">' . esc_html( $name ) . '</a>';
			} else {
				echo esc_html( $name );
			}
		} else {
			echo '—';
		}
		return;
	}
	if ( 'bagxpro_email' === $column ) {
		$em = get_post_meta( $post_id, '_bagxpro_email', true );
		echo $em ? '<a href="mailto:' . esc_attr( $em ) . '">' . esc_html( $em ) . '</a>' : '—';
		return;
	}
	if ( 'bagxpro_mailjet' === $column ) {
		$sent = get_post_meta( $post_id, '_bagxpro_mailjet_sent', true );
		if ( '1' === $sent || 1 === $sent || true === $sent ) {
			echo '<span class="dashicons dashicons-yes-alt" style="color:#008a20;" aria-hidden="true"></span> ';
			esc_html_e( 'Envoyé', 'bagxpro' );
		} elseif ( '0' === $sent || 0 === $sent ) {
			$err = get_post_meta( $post_id, '_bagxpro_mailjet_error', true );
			$tip = $err ? ' title="' . esc_attr( $err ) . '"' : '';
			echo '<span class="dashicons dashicons-warning" style="color:#dba617;" aria-hidden="true"' . $tip . '></span> ';
			esc_html_e( 'Échec envoi', 'bagxpro' );
		} else {
			echo '—';
		}
	}
}

/**
 * Formulaire devis (Brevo + e-mail) — constantes wp-config.php recommandées :
 *
 * define( 'IMPACTEXPO_BREVO_API_KEY', 'xkeysib-...' );  // API HTTP (liste contacts)
 * define( 'IMPACTEXPO_BREVO_LEAD_LIST_ID', 32 );        // Liste Brevo (defaut 32 si omis)
 * define( 'IMPACTEXPO_LEAD_NOTIFICATION_EMAIL', '...' ); // Destinataire du recap (defaut admin_email)
 * define( 'IMPACTEXPO_LEAD_PUBLIC_CONTACT_EMAIL', '...' ); // E-mail affiche sous les PJ (defaut admin_email)
 *
 * Mapping Brevo (cles exactes) : voir impactexpo_lead_send_brevo() dans inc/lead-form.php
 * — PRENOM, NOM, SMS, COMPANY_ADDRESS_LINE_1 (nom societe), ADRESSE_SOCIETE, VILLE, etc.
 */

function add_hreflang_tags() {
    // Définir l'URL de la version par défaut (x-default) du site
    $default_url = get_home_url(); // ou mettre une URL spécifique

    // Obtenir l'URL actuelle
    $current_url = home_url( add_query_arg( NULL, NULL ) );

    // Si la langue est française
    if ( get_locale() == 'fr_FR' ) {
        echo '<link rel="alternate" hreflang="fr" href="' . esc_url( $current_url ) . '" />' . "\n";
    }

    // Pour la version x-default
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $default_url ) . '" />' . "\n";
}
add_action( 'wp_head', 'add_hreflang_tags' );


function add_self_canonical_tag() {
    // Obtenir l'URL de la page actuelle
    $current_url = home_url( add_query_arg( NULL, NULL ) );

    // Ajouter la balise canonical
    echo '<link rel="canonical" href="' . esc_url( $current_url ) . '" />' . "\n";
}
add_action( 'wp_head', 'add_self_canonical_tag' );

add_action( 'wp_enqueue_scripts', 'wpm_enqueue_styles' );
function wpm_enqueue_styles(){
    //wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/styles/theme.css' );
    wp_enqueue_style('lightbox', get_stylesheet_directory_uri() . '/styles/lightbox.css', array(), filemtime(get_template_directory() . '/styles/theme.css'));
    wp_enqueue_style('theme', get_stylesheet_directory_uri() . '/styles/theme.css', array(), filemtime(get_template_directory() . '/styles/theme.css'));
    $responsive_css = get_stylesheet_directory() . '/styles/responsive.css';
    wp_enqueue_style(
        'bagxpro-responsive',
        get_stylesheet_directory_uri() . '/styles/responsive.css',
        array( 'theme' ),
        file_exists( $responsive_css ) ? filemtime( $responsive_css ) : null
    );
    wp_enqueue_script(
        'beforeafter', // Identifiant unique du script
        get_stylesheet_directory_uri() . '/js/beforeafter.js', // URL du fichier JS
        array( 'jquery' ), // Dépendances (si besoin, ici 'jquery')
        null, // Version du script (null pour désactiver la gestion des versions)
        true // Charger dans le footer (true) ou dans le header (false)
    );

    wp_enqueue_script(
        'script', // Identifiant unique du script
        get_stylesheet_directory_uri() . '/js/script.js', // URL du fichier JS
        array( 'jquery' ), // Dépendances (si besoin, ici 'jquery')
        filemtime( get_stylesheet_directory() . '/js/script.js' ),
        true // Charger dans le footer (true) ou dans le header (false)
    );

    wp_localize_script(
        'script',
        'impactexpoLead',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        )
    );

	$page_loader_js = get_stylesheet_directory() . '/js/bagxpro-page-loader.js';
	wp_enqueue_script(
		'bagxpro-page-loader',
		get_stylesheet_directory_uri() . '/js/bagxpro-page-loader.js',
		array(),
		file_exists( $page_loader_js ) ? filemtime( $page_loader_js ) : null,
		true
	);

	$scroll_reveal_js = get_stylesheet_directory() . '/js/bagxpro-scroll-reveal.js';
	wp_enqueue_script(
		'bagxpro-scroll-reveal',
		get_stylesheet_directory_uri() . '/js/bagxpro-scroll-reveal.js',
		array(),
		file_exists( $scroll_reveal_js ) ? filemtime( $scroll_reveal_js ) : null,
		true
	);

	$configurator_sidebar_js = get_stylesheet_directory() . '/js/bagxpro-configurator-sidebar.js';
	wp_enqueue_script(
		'bagxpro-configurator-sidebar',
		get_stylesheet_directory_uri() . '/js/bagxpro-configurator-sidebar.js',
		array(),
		file_exists( $configurator_sidebar_js ) ? filemtime( $configurator_sidebar_js ) : null,
		true
	);

	$mobile_nav_js = get_stylesheet_directory() . '/js/bagxpro-mobile-nav.js';
	wp_enqueue_script(
		'bagxpro-mobile-nav',
		get_stylesheet_directory_uri() . '/js/bagxpro-mobile-nav.js',
		array( 'jquery', 'app' ),
		file_exists( $mobile_nav_js ) ? filemtime( $mobile_nav_js ) : null,
		true
	);

	if ( is_front_page() ) {
		$home_stats_js = get_stylesheet_directory() . '/js/home-stats-count.js';
		wp_enqueue_script(
			'bagxpro-home-stats-count',
			get_stylesheet_directory_uri() . '/js/home-stats-count.js',
			array(),
			file_exists( $home_stats_js ) ? filemtime( $home_stats_js ) : null,
			true
		);
	}

	$bagxpro_load_product_bag_css = is_singular( 'produit' ) || bagxpro_is_nos_solutions_child_page();

	if ( $bagxpro_load_product_bag_css ) {
		$css_path = get_stylesheet_directory() . '/styles/product-bag.css';
		wp_enqueue_style(
			'bagxpro-product-bag',
			get_stylesheet_directory_uri() . '/styles/product-bag.css',
			array( 'theme' ),
			file_exists( $css_path ) ? filemtime( $css_path ) : null
		);
	}

	if ( is_page_template( 'page-contact.php' ) ) {
		$product_bag_css = get_stylesheet_directory() . '/styles/product-bag.css';
		wp_enqueue_style(
			'bagxpro-product-bag',
			get_stylesheet_directory_uri() . '/styles/product-bag.css',
			array( 'theme' ),
			file_exists( $product_bag_css ) ? filemtime( $product_bag_css ) : null
		);

		$contact_css = get_stylesheet_directory() . '/styles/contact.css';
		wp_enqueue_style(
			'bagxpro-contact',
			get_stylesheet_directory_uri() . '/styles/contact.css',
			array( 'bagxpro-product-bag' ),
			file_exists( $contact_css ) ? filemtime( $contact_css ) : null
		);
	}

	if ( is_singular( 'produit' ) ) {
		$html2_path = get_stylesheet_directory() . '/js/vendor/html2canvas.min.js';
		wp_enqueue_script(
			'html2canvas',
			get_stylesheet_directory_uri() . '/js/vendor/html2canvas.min.js',
			array(),
			file_exists( $html2_path ) ? filemtime( $html2_path ) : '1.4.1',
			true
		);

		$js_path = get_stylesheet_directory() . '/js/product-bag-preview.js';
		wp_enqueue_script(
			'bagxpro-product-bag-preview',
			get_stylesheet_directory_uri() . '/js/product-bag-preview.js',
			array( 'html2canvas' ),
			file_exists( $js_path ) ? filemtime( $js_path ) : null,
			true
		);
	}
}


function hide_post_type_from_frontend($args, $post_type) {
    if ($post_type === 'post') {  // Remplacez 'post' par le post type que vous voulez masquer
        $args['public'] = false;  // Rend le post type privé
        $args['publicly_queryable'] = false;  // Empêche les requêtes sur le front-end
        $args['show_ui'] = false;  // Masque du menu d'administration
        $args['exclude_from_search'] = true;  // Exclut des résultats de recherche
    }
    return $args;
}
add_filter('register_post_type_args', 'hide_post_type_from_frontend', 10, 2);


// Fil d'ariane

function custom_breadcrumb() {
    // Start the breadcrumb with a link to the home page
    if (!is_front_page()) {
        echo '<nav class="breadcrumb">';
        echo '<a href="' . home_url() . '">Accueil</a> ';

        // If we're on a single post, custom post type or page
        if (is_singular()) {
            global $post;
            $post_type = get_post_type_object(get_post_type());

            // If the post type is not 'post', show the post type archive link
            if ($post_type && $post_type->has_archive) {
                echo '<a href="' . get_post_type_archive_link($post_type->name) . '">' . $post_type->labels->name . '</a> ';
            }

            // Get ancestors of the current post to show hierarchy
            $ancestors = array_reverse(get_post_ancestors($post));

            foreach ($ancestors as $ancestor) {
                echo '<a href="' . get_permalink($ancestor) . '">' . get_the_title($ancestor) . '</a> ';
            }

            // Finally, the current post title
            echo '<span>' . get_the_title() . '</span>';
        }
        // If we're on a post type archive page
        elseif (is_post_type_archive()) {
            $post_type = get_post_type_object(get_post_type());
            if ($post_type) {
                echo '<span>' . $post_type->labels->name . '</span>';
            }
        }
        // If we're on a category or custom taxonomy archive page
        elseif (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            echo '<span>' . $term->name . '</span>';
        }
        // If we're on an archive page like date, author, etc.
        elseif (is_archive()) {
            if (is_date()) {
                if (is_day()) {
                    echo '<span>' . get_the_date() . '</span>';
                } elseif (is_month()) {
                    echo '<span>' . get_the_date('F Y') . '</span>';
                } elseif (is_year()) {
                    echo '<span>' . get_the_date('Y') . '</span>';
                }
            } elseif (is_author()) {
                echo '<span>' . get_the_author() . '</span>';
            }
        }
        // For 404 pages
        elseif (is_404()) {
            echo '<span>Erreur 404</span>';
        }
    }

    // Close nav tag
    echo '</nav>';
}

add_image_size( 'relsize', 1920, 1080, true );
add_image_size( 'crosslink', 900, 900, true );

/**
 * Ajoute un selecteur de styles dans tous les TinyMCE/WYSIWYG.
 */
function impactexpo_add_tinymce_style_select( $buttons ) {
    if ( ! in_array( 'styleselect', $buttons, true ) ) {
        array_unshift( $buttons, 'styleselect' );
    }

    return $buttons;
}
add_filter( 'mce_buttons_2', 'impactexpo_add_tinymce_style_select' );

/**
 * Definit les styles editor qui inserent des div avec classes "style-H*".
 */
function impactexpo_register_tinymce_style_formats( $init_array ) {
    $style_formats = array(
        array(
            'title'   => 'Style H1',
            'block'   => 'div',
            'classes' => 'style-H1',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Style H2',
            'block'   => 'div',
            'classes' => 'style-H2',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Style H3',
            'block'   => 'div',
            'classes' => 'style-H3',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Style H4',
            'block'   => 'div',
            'classes' => 'style-H4',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Style H5',
            'block'   => 'div',
            'classes' => 'style-H5',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Style H6',
            'block'   => 'div',
            'classes' => 'style-H6',
            'wrapper' => true,
        ),
    );

    $init_array['style_formats'] = wp_json_encode( $style_formats );
    $init_array['style_formats_merge'] = true;

    return $init_array;
}
add_filter( 'tiny_mce_before_init', 'impactexpo_register_tinymce_style_formats' );

/**
 * Charge les styles front dans les editeurs du back-office.
 */
function impactexpo_setup_editor_styles() {
    add_theme_support( 'editor-styles' );
    add_editor_style( 'styles/theme.css' );
}
add_action( 'after_setup_theme', 'impactexpo_setup_editor_styles' );

/**
 * Force TinyMCE (notamment ACF WYSIWYG) a charger le CSS du theme.
 */
function impactexpo_add_mce_css( $mce_css ) {
    $theme_css_uri = get_stylesheet_directory_uri() . '/styles/theme.css';
    $theme_css_path = get_stylesheet_directory() . '/styles/theme.css';

    if ( file_exists( $theme_css_path ) ) {
        $theme_css_uri = add_query_arg( 'ver', filemtime( $theme_css_path ), $theme_css_uri );
    }

    if ( ! empty( $mce_css ) ) {
        $mce_css .= ',';
    }

    $mce_css .= $theme_css_uri;

    return $mce_css;
}
add_filter( 'mce_css', 'impactexpo_add_mce_css' );



