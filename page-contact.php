<?php
/**
 * Contact page (page slug: contact). The form itself is Fluent Forms —
 * paste its shortcode into Customize → Contact Page once built; until
 * then this shows a WhatsApp/phone/email fallback so the page is never
 * just an empty box.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$form_shortcode = nefertari_option( 'contact_form_shortcode', '' );
$wa_link         = nefertari_whatsapp_link( "Hi " . get_bloginfo( 'name' ) . ", I have a question." );
?>
<section class="nx-page-hero">
	<div class="nx-page-hero-blob"></div>
	<div class="nx-page-hero-inner">
		<?php nefertari_render_breadcrumbs( array( array( 'label' => 'Contact', 'url' => null ) ) ); ?>
		<div class="nx-eyebrow">Get in touch</div>
		<h1><?php echo esc_html( nefertari_option( 'contact_heading', "Let's talk about your trip" ) ); ?></h1>
		<p><?php echo esc_html( nefertari_option( 'contact_subheading', "Questions about an excursion, a group booking, or something else — reach us however's easiest." ) ); ?></p>
	</div>
</section>

<section class="nx-section" style="padding-top:8px">
	<div class="nx-contact-grid">
		<div class="nx-panel">
			<h2>Send a message</h2>
			<?php if ( $form_shortcode ) : ?>
				<?php echo do_shortcode( $form_shortcode ); ?>
			<?php else : ?>
				<p class="nx-bookings-empty">Our contact form is being set up — meanwhile, reach us directly below and we'll get right back to you.</p>
				<a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener" class="nx-btn nx-btn--primary nx-btn--block" style="margin-top:14px"><?php echo nefertari_icon( 'whatsapp', 'currentColor', 16 ); ?> Message us on WhatsApp</a>
			<?php endif; ?>
		</div>

		<div class="nx-panel">
			<h2>Contact details</h2>
			<div class="nx-foot-contact nx-contact-details">
				<div class="nx-foot-contact-row"><?php echo nefertari_icon( 'pin', '#B07A55', 16 ); ?> <?php echo esc_html( nefertari_option( 'address', 'Sheraton Road, Hurghada, Red Sea Governorate, Egypt' ) ); ?></div>
				<div class="nx-foot-contact-row"><a href="tel:<?php echo esc_attr( nefertari_phone_tel() ); ?>" class="is-strong"><?php echo nefertari_icon( 'phone', '#B07A55', 16 ); ?> <?php echo esc_html( nefertari_option( 'phone', '+20 111 202 6922' ) ); ?></a></div>
				<div class="nx-foot-contact-row"><a href="mailto:<?php echo esc_attr( nefertari_option( 'email', 'hello@nefertaritravel.com' ) ); ?>"><?php echo nefertari_icon( 'mail', '#B07A55', 16 ); ?> <?php echo esc_html( nefertari_option( 'email', 'hello@nefertaritravel.com' ) ); ?></a></div>
				<div class="nx-foot-contact-row"><?php echo nefertari_icon( 'clock', '#B07A55', 16 ); ?> <?php echo esc_html( nefertari_option( 'hours', 'Open daily · 8:00am – 10:00pm' ) ); ?></div>
			</div>

			<a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener" class="nx-btn nx-btn--outline nx-btn--block" style="margin-top:18px"><?php echo nefertari_icon( 'whatsapp', 'currentColor', 16 ); ?> Chat on WhatsApp</a>

			<?php if ( nefertari_option( 'social_facebook' ) || nefertari_option( 'social_instagram' ) || nefertari_option( 'social_x' ) ) : ?>
				<div class="nx-contact-socials">
					<?php if ( nefertari_option( 'social_facebook' ) ) : ?><a href="<?php echo esc_url( nefertari_option( 'social_facebook' ) ); ?>" target="_blank" rel="noopener" aria-label="Facebook"><?php echo nefertari_icon( 'facebook', 'currentColor', 17 ); ?></a><?php endif; ?>
					<?php if ( nefertari_option( 'social_instagram' ) ) : ?><a href="<?php echo esc_url( nefertari_option( 'social_instagram' ) ); ?>" target="_blank" rel="noopener" aria-label="Instagram"><?php echo nefertari_icon( 'instagram', 'currentColor', 17 ); ?></a><?php endif; ?>
					<?php if ( nefertari_option( 'social_x' ) ) : ?><a href="<?php echo esc_url( nefertari_option( 'social_x' ) ); ?>" target="_blank" rel="noopener" aria-label="X"><?php echo nefertari_icon( 'x-twitter', 'currentColor', 15 ); ?></a><?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php
get_footer();
