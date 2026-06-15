<?php
/**
 * Formulaire page contact.
 *
 * @package bagxpro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_id = get_queried_object_id();
$status  = isset( $_GET['contact'] ) ? sanitize_text_field( wp_unslash( $_GET['contact'] ) ) : '';
?>
<div class="bagxpro-contact-form-wrap">
	<?php if ( 'merci' === $status ) : ?>
		<div class="bagxpro-notice bagxpro-notice--success" role="status">
			<?php esc_html_e( 'Merci : votre message a bien été envoyé. Nous vous répondrons sous 24h.', 'bagxpro' ); ?>
		</div>
	<?php elseif ( 'incomplet' === $status ) : ?>
		<div class="bagxpro-notice bagxpro-notice--error" role="alert">
			<?php esc_html_e( 'Merci de renseigner le nom, le prénom, une adresse e-mail valide, votre téléphone et votre message.', 'bagxpro' ); ?>
		</div>
	<?php elseif ( 'nonce' === $status ) : ?>
		<div class="bagxpro-notice bagxpro-notice--error" role="alert">
			<?php esc_html_e( 'Session expirée. Réessayez.', 'bagxpro' ); ?>
		</div>
	<?php elseif ( 'erreur' === $status ) : ?>
		<div class="bagxpro-notice bagxpro-notice--error" role="alert">
			<?php esc_html_e( 'L’envoi a échoué. Réessayez ou contactez-nous directement.', 'bagxpro' ); ?>
		</div>
	<?php elseif ( 'limite' === $status ) : ?>
		<div class="bagxpro-notice bagxpro-notice--error" role="alert">
			<?php esc_html_e( 'Trop de messages envoyés récemment. Patientez quelques minutes avant de réessayer.', 'bagxpro' ); ?>
		</div>
	<?php elseif ( 'rgpd' === $status ) : ?>
		<div class="bagxpro-notice bagxpro-notice--error" role="alert">
			<?php esc_html_e( 'Merci d’accepter l’information sur les données personnelles avant d’envoyer votre message.', 'bagxpro' ); ?>
		</div>
	<?php endif; ?>

	<form
		class="bagxpro-contact-form bagxpro-no-scroll-reveal"
		method="post"
		action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
	>
		<input type="hidden" name="action" value="bagxpro_contact">
		<?php wp_nonce_field( 'bagxpro_contact_form', 'bagxpro_contact_nonce' ); ?>
		<input type="hidden" name="bagxpro_contact_page_id" value="<?php echo (int) $page_id; ?>">

		<div class="bagxpro-visually-hidden" aria-hidden="true">
			<label for="bagxpro-contact-hp"><?php esc_html_e( 'Laisser vide', 'bagxpro' ); ?></label>
			<input type="text" name="bagxpro_contact_hp" id="bagxpro-contact-hp" value="" tabindex="-1" autocomplete="off">
		</div>

		<?php bagxpro_the_required_fields_legend(); ?>

		<div class="bagxpro-field">
			<label class="bagxpro-field__label" for="bagxpro-contact-societe"><?php esc_html_e( 'Nom de la société', 'bagxpro' ); ?></label>
			<input type="text" class="bagxpro-input" id="bagxpro-contact-societe" name="bagxpro_contact_societe" autocomplete="organization" maxlength="120">
		</div>

		<div class="bagxpro-contact-form__row">
			<div class="bagxpro-field">
				<label class="bagxpro-field__label" for="bagxpro-contact-nom"><?php esc_html_e( 'Nom', 'bagxpro' ); ?> <?php bagxpro_the_required_field_mark(); ?></label>
				<input type="text" class="bagxpro-input" id="bagxpro-contact-nom" name="bagxpro_contact_nom" autocomplete="family-name" required>
			</div>
			<div class="bagxpro-field">
				<label class="bagxpro-field__label" for="bagxpro-contact-prenom"><?php esc_html_e( 'Prénom', 'bagxpro' ); ?> <?php bagxpro_the_required_field_mark(); ?></label>
				<input type="text" class="bagxpro-input" id="bagxpro-contact-prenom" name="bagxpro_contact_prenom" autocomplete="given-name" required>
			</div>
		</div>

		<div class="bagxpro-contact-form__row">
			<div class="bagxpro-field">
				<label class="bagxpro-field__label" for="bagxpro-contact-email"><?php esc_html_e( 'E-mail', 'bagxpro' ); ?> <?php bagxpro_the_required_field_mark(); ?></label>
				<input type="email" class="bagxpro-input" id="bagxpro-contact-email" name="bagxpro_contact_email" autocomplete="email" inputmode="email" required>
			</div>
			<div class="bagxpro-field">
				<label class="bagxpro-field__label" for="bagxpro-contact-telephone"><?php esc_html_e( 'Téléphone', 'bagxpro' ); ?> <?php bagxpro_the_required_field_mark(); ?></label>
				<input type="tel" class="bagxpro-input" id="bagxpro-contact-telephone" name="bagxpro_contact_telephone" autocomplete="tel" inputmode="tel" required>
			</div>
		</div>

		<div class="bagxpro-field">
			<label class="bagxpro-field__label" for="bagxpro-contact-message"><?php esc_html_e( 'Votre message', 'bagxpro' ); ?> <?php bagxpro_the_required_field_mark(); ?></label>
			<textarea class="bagxpro-input bagxpro-textarea" id="bagxpro-contact-message" name="bagxpro_contact_message" rows="6" required></textarea>
		</div>

		<div class="bagxpro-field bagxpro-rgpd">
			<p class="bagxpro-rgpd__text">
				<?php esc_html_e( 'Les informations collectées sont nécessaires au traitement de votre demande. Elles sont conservées sans limite de durée, jusqu’à exercice de vos droits ou obligation légale, conformément au règlement (UE) 2016/679 (RGPD).', 'bagxpro' ); ?>
			</p>
			<?php
			$privacy_url = function_exists( 'wp_get_privacy_policy_url' ) ? wp_get_privacy_policy_url() : '';
			if ( $privacy_url ) :
				?>
				<p class="bagxpro-rgpd__privacy">
					<a class="bagxpro-rgpd__privacy-link" href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Politique de confidentialité', 'bagxpro' ); ?></a>
				</p>
			<?php endif; ?>
			<label class="bagxpro-rgpd__consent" for="bagxpro-contact-rgpd-consent">
				<input type="checkbox" name="bagxpro_contact_rgpd_consent" id="bagxpro-contact-rgpd-consent" class="bagxpro-rgpd__checkbox" value="1" required>
				<span class="bagxpro-rgpd__consent-text"><?php esc_html_e( 'Je confirme avoir pris connaissance de l’information ci-dessus et accepter le traitement de mes données personnelles dans ce cadre.', 'bagxpro' ); ?> <?php bagxpro_the_required_field_mark(); ?></span>
			</label>
		</div>

		<button type="submit" class="bagxpro-btn-order">
			<span class="bagxpro-btn-order__main"><?php esc_html_e( 'Envoyer mon message', 'bagxpro' ); ?></span>
			<span class="bagxpro-btn-order__sub"><?php esc_html_e( 'Devis sous 24h', 'bagxpro' ); ?></span>
		</button>
	</form>
</div>
