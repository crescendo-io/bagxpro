<?php
get_header();

?>

    <!-- Slider main container -->


<?php if( have_rows('page') ):
    while ( have_rows('page') ) : the_row();
        get_template_part('template-parts/strates/' . get_row_layout());
    endwhile;
endif; ?>


<?php get_footer();
