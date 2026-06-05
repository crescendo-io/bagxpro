<?php
/**
 * Template Name: Contact
 *
 * Page contact : coordonnées, horaires, formulaire (Mailjet).
 *
 * @package bagxpro
 */

get_header();

while ( have_posts() ) :
	the_post();

	$page_id           = get_the_ID();
	$intro             = function_exists( 'get_field' ) ? get_field( 'contact_page_intro', $page_id ) : '';
	$contact_email     = function_exists( 'get_field' ) ? get_field( 'contact_email', $page_id ) : '';
	$contact_phone     = function_exists( 'get_field' ) ? get_field( 'contact_phone', $page_id ) : '';
	$contact_address   = function_exists( 'get_field' ) ? get_field( 'contact_address', $page_id ) : '';
	$hours_rows        = function_exists( 'bagxpro_get_contact_hours_rows' ) ? bagxpro_get_contact_hours_rows( $page_id ) : array();
	$notification_mail = function_exists( 'bagxpro_get_mail_notification_email' ) ? bagxpro_get_mail_notification_email( $page_id ) : get_option( 'admin_email' );

	if ( ! $contact_email && is_email( $notification_mail ) ) {
		$contact_email = $notification_mail;
	}
	?>

<main class="bagxpro-contact-page" id="bagxpro-contact-<?php echo (int) $page_id; ?>">
	<div class="container">
		<header class="bagxpro-contact-page__header">
			<h1><?php the_title(); ?></h1>
			<?php if ( $intro ) : ?>
				<div class="bagxpro-contact-page__intro entry-content">
					<?php echo wp_kses_post( $intro ); ?>
				</div>
			<?php endif; ?>
		</header>

		<div class="row bagxpro-contact-page__grid">
			<aside class="col-lg-5 bagxpro-contact-page__aside">
				<section class="bagxpro-contact-card" aria-labelledby="bagxpro-contact-info-title">
					<h2 class="bagxpro-contact-card__title" id="bagxpro-contact-info-title"><?php esc_html_e( 'Nos coordonnées', 'bagxpro' ); ?></h2>
					<ul class="bagxpro-contact-info">
						<?php if ( $contact_email && is_email( $contact_email ) ) : ?>
							<li class="bagxpro-contact-info__item">
								<span class="bagxpro-contact-info__label"><?php esc_html_e( 'E-mail', 'bagxpro' ); ?></span>
								<a class="bagxpro-contact-info__value" href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( $contact_phone ) : ?>
							<li class="bagxpro-contact-info__item">
								<span class="bagxpro-contact-info__label"><?php esc_html_e( 'Téléphone', 'bagxpro' ); ?></span>
								<a class="bagxpro-contact-info__value" href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $contact_phone ) ); ?>"><?php echo esc_html( $contact_phone ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( $contact_address ) : ?>
							<li class="bagxpro-contact-info__item">
								<span class="bagxpro-contact-info__label"><?php esc_html_e( 'Adresse', 'bagxpro' ); ?></span>
								<span class="bagxpro-contact-info__value"><?php echo nl2br( esc_html( $contact_address ) ); ?></span>
							</li>
						<?php endif; ?>
					</ul>
				</section>

				<section class="bagxpro-contact-card bagxpro-contact-card--hours" aria-labelledby="bagxpro-contact-hours-title">
					<h2 class="bagxpro-contact-card__title" id="bagxpro-contact-hours-title"><?php esc_html_e( 'Horaires d’ouverture', 'bagxpro' ); ?></h2>
					<?php if ( ! empty( $hours_rows ) ) : ?>
						<dl class="bagxpro-contact-hours">
							<?php foreach ( $hours_rows as $hour_row ) : ?>
								<div class="bagxpro-contact-hours__row">
									<dt class="bagxpro-contact-hours__day"><?php echo esc_html( $hour_row['day'] ); ?></dt>
									<dd class="bagxpro-contact-hours__time"><?php echo esc_html( $hour_row['time'] ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</section>
			</aside>

			<div class="col-lg-7 bagxpro-contact-page__form-col">
				<section class="bagxpro-contact-card bagxpro-contact-card--form" aria-labelledby="bagxpro-contact-form-title">
					<h2 class="bagxpro-contact-card__title" id="bagxpro-contact-form-title"><?php esc_html_e( 'Écrivez-nous', 'bagxpro' ); ?></h2>
					<?php get_template_part( 'template-parts/contact/form' ); ?>
				</section>
			</div>
		</div>
	</div>
</main>

	<?php
endwhile;

get_footer();
