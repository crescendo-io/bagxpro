<?php

/*
Template Name: Home
*/


get_header();

?>

<div class="strate-hero full home">
    <img src="https://harmony-builder.code/wp-content/uploads/2026/04/bg.jpg" class="strate-hero_image" alt="" width="1728" height="1110">
    <div class="strate-hero_inner">
        <h1>Personnalisez votre Big Bag,<br>
            Boostez votre business</h1>
        <p>Vos Big Bags deviennent votre meilleure publicité :<br>
            affichez fièrement les couleurs de votre entreprise</p>

        <div class="container-buttons">
            <a href="#" target="" class="button primary ">
                NOUS DECOUVRIR
            </a>
        </div>
    </div>

    <div class="container-bag-home">
        <img src="<?= get_stylesheet_directory_uri(); ?>/images/bag.png" class="bag-img" alt="">
        <img src="<?= get_stylesheet_directory_uri(); ?>/images/placeholder.svg" class="placeholder" alt="">
    </div>
</div>
<div class="list-logo">
    <ul>
        <li class="label">
            Ils nous font déjà confiance
        </li>
        <li>
            <img src="<?= get_stylesheet_directory_uri(); ?>/images/logo-partenaire.png" alt="">
        </li>
        <li>
            <img src="<?= get_stylesheet_directory_uri(); ?>/images/logo-partenaire.png" alt="">
        </li>
        <li>
            <img src="<?= get_stylesheet_directory_uri(); ?>/images/logo-partenaire.png" alt="">
        </li>
        <li>
            <img src="<?= get_stylesheet_directory_uri(); ?>/images/logo-partenaire.png" alt="">
        </li>
        <li>
            <img src="<?= get_stylesheet_directory_uri(); ?>/images/logo-partenaire.png" alt="">
        </li>
    </ul>
</div>

<div class="section-cards">
    <div class="container">
        <div class="row">
            <div class="col-sm-3">
                <div class="card-border">
                    <div class="card-number">3000</div>
                    <div class="card-label">vues par jour</div>
                    <div class="card-description">
                        Visibilité d’une
                        marque sur un chantier
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card-border">
                    <div class="card-number">5700</div>
                    <div class="card-label">heures sur votre chantier</div>
                    <div class="card-description">
                        Un commercial H24 qui ne vous coute rien
                    </div>
                </div>
            </div>
            
            <div class="col-sm-3">
                <div class="container-image-cards-placeholder">
                    <img src="<?= get_stylesheet_directory_uri(); ?>/images/placeholder-home-cards.png" class="img-card" alt="">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card-border">
                    <div class="card-number">1500</div>
                    <div class="card-label">économisez sur l’évacuation de vos déchets par chantier</div>
                    <div class="card-description">
                        Grace à l’Eco-contribution déjà payée à vos partenaires fournisseurs
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
                            <img src="https://harmony-builder.code/wp-content/themes/bagxpro/images/bag.png" class="img" alt="">
                        </div>
                        <div class="col-sm-6">
                            <div class="container-text">
                                <h2>Comment ça marche</h2>

                                <ul>
                                    <li>
                                        <h3>Choisissez votre produit suivant votre environnement</h3>
                                        <p>
                                            BTP, Agriculture, Industrie, Artisans, Jardinage, Bois,
                                            Déchets, etc...
                                        </p>
                                    </li>
                                    <li>
                                        <h3>Personnalisez-le</h3>
                                        <p>
                                            Chargez votre logo dans notre simulateur, Choisissez
                                            vos modèles et vos quantités
                                        </p>
                                    </li>
                                    <li>
                                        <h3>Commandez-le</h3>
                                        <p>
                                            Contactez-nous dès maintenant : Nos équipes vous envoient rapidement un devis sur mesure ainsi que les délais de livraison
                                        </p>
                                    </li>
                                    <li>
                                        <a href="" class="button primary">Voir nos produits</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_template_part('template-parts/strates/list_products'); ?>


<?php if( have_rows('page') ):
    while ( have_rows('page') ) : the_row();
        get_template_part('template-parts/strates/' . get_row_layout());
    endwhile;
endif; ?>


<?php get_footer();
