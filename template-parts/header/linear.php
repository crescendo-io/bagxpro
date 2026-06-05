<?php
    $option_logo_primary = get_field('option_logo_primary', 'option');
    $option_logo_primary_array = get_custom_thumb($option_logo_primary, 'full');

    $option_logo_scroll = get_field('option_logo_scroll', 'option');
    $option_logo_scroll_array = get_custom_thumb($option_logo_scroll, 'full');
?>

<div class="bagxpro-site-header">
<div class="sentence-header">
Demandez votre devis pour vos Big Bags personnalisés, qualité professionnelle, fabrication sur mesure. 
</div>
<header class="burger linear">
    <div class="container-fluid">
        <div class="row align-items-center bagxpro-header-row">
            <div class="col-3 col-md-2 visible-xs">
                <div class="button-menu" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'bagxpro' ); ?>">
                    <div class="barre"></div>
                    <div class="text">
                        menu
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2 bagxpro-header-logo">
                <?php if(isset($option_logo_primary_array['url']) && $option_logo_primary_array['url']): ?>
                <a href="<?= get_site_url(); ?>">
                    <img src="<?= $option_logo_primary_array['url']; ?>" class="logo" alt="<?= $option_logo_primary_array['alt']; ?>">
                    <img src="<?= $option_logo_scroll_array['url']; ?>" class="logo-scroll" alt="<?= $option_logo_scroll_array['alt']; ?>">
                </a>
                <?php endif; ?>
            </div>

            <div class="col-md-8 hidden-xs text-center bagxpro-header-nav">
                <?= wp_nav_menu(array(
                    'menu'				=> "menu",
                    'menu_class'		=> "",
                    'container_class'	=> "menu",
                )); ?>
            </div>

            <div class="col-3 col-md-2 bagxpro-header-cta text-right">
                <a href="#modal-configurator" class="button primary modal-configurator-button">
                    <span class="bagxpro-header-cta__full hidden-xs">NOS PRODUITS</span>
                    <span class="bagxpro-header-cta__short visible-xs" aria-hidden="true">+</span>
                </a>
            </div>
        </div>
    </div>
    <?php custom_breadcrumb(); ?>
</header>

<div class="menu-navigation" aria-hidden="true">
    <button
        type="button"
        class="bagxpro-menu-close"
        data-bagxpro-menu-close
        aria-label="<?php esc_attr_e( 'Fermer le menu', 'bagxpro' ); ?>"
    >
        <span aria-hidden="true">&times;</span>
    </button>
    <?= wp_nav_menu(array(
        'menu'				=> "menu",
        'menu_class'		=> "",
        'container_class'	=> "menu",
    )); ?>
</div>
</div>
