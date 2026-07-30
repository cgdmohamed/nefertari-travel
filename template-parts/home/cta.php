<?php
/**
 * Contact / booking CTA band.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="contact" class="nx-cta-section">
	<div class="nx-cta">
		<h2><?php echo esc_html( nefertari_option( 'cta_heading', 'Ready to explore?' ) ); ?></h2>
		<p><?php echo esc_html( nefertari_option( 'cta_subheading', "Message us on WhatsApp and we'll tailor the perfect program for your dates." ) ); ?></p>
		<?php nefertari_book_button( nefertari_option( 'cta_button', 'Book an excursion →' ), 'nx-btn nx-btn--dark' ); ?>
	</div>
</section>
