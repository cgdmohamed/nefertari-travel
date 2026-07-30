<?php
/**
 * Sticky booking card on the excursion detail page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$excursion_id = get_the_ID();
$rating       = get_post_meta( $excursion_id, '_nx_rating', true ) ?: '4.9';
$reviews_n    = (int) get_post_meta( $excursion_id, '_nx_reviews_count', true );
?>
<div class="nx-book">
	<div class="nx-book-head" style="background:<?php echo esc_attr( nefertari_excursion_gradient_css( $excursion_id ) ); ?>">
		<div class="nx-book-head-label">Price per person from</div>
		<div class="nx-book-price"><?php echo esc_html( nefertari_excursion_price( $excursion_id ) ); ?></div>
		<div class="nx-book-urgency">⚡ Only a few spots left for this week</div>
	</div>
	<div class="nx-book-body">
		<div class="nx-book-perks">
			<div class="nx-book-perk">✅ Free hotel pickup &amp; drop-off</div>
			<div class="nx-book-perk">✅ Instant confirmation by email</div>
			<div class="nx-book-perk">✅ Pay securely online to confirm your spot</div>
			<div class="nx-book-perk">✅ Free cancellation up to 24h before</div>
		</div>
		<div style="margin-bottom:10px"><?php nefertari_book_button( 'Book this excursion', 'nx-btn nx-btn--primary nx-btn--block', $excursion_id ); ?></div>
		<a href="tel:<?php echo esc_attr( nefertari_phone_tel() ); ?>" class="nx-call-btn"><?php echo nefertari_icon( 'phone', '#2B1A1F', 16 ); ?> Call to book</a>
		<div class="nx-book-rating"><span class="nx-stars" style="color:var(--nx-gold)">★★★★★</span> <?php echo esc_html( $rating ); ?> from <?php echo esc_html( $reviews_n ); ?> travellers</div>
		<div class="nx-book-pay-row">
			<span>🔒 Secure payment</span><span>VISA</span><span>Mastercard</span><span>PayPal</span>
		</div>
	</div>
</div>
