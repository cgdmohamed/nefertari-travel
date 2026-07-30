<?php
/**
 * Trust strip: rating, license, TripAdvisor, secure booking.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="nx-trust-section">
	<div class="nx-trust">
		<div class="nx-trust-rating">
			<div class="nx-stars">★★★★★</div>
			<div>
				<div class="nx-trust-rating-title"><?php echo esc_html( nefertari_option( 'rating', '4.9' ) ); ?> / 5 · <?php echo esc_html( nefertari_option( 'reviews_count', '2,400+' ) ); ?> reviews</div>
				<div class="nx-trust-rating-sub">on TripAdvisor &amp; Google</div>
			</div>
		</div>
		<div class="nx-trust-item"><?php echo nefertari_icon( 'shield-check', '#D93A7C', 22 ); ?> Licensed by the Egyptian Ministry of Tourism</div>
		<div class="nx-trust-item"><?php echo nefertari_icon( 'medal', '#D93A7C', 22 ); ?> TripAdvisor Travellers' Choice 2025</div>
		<div class="nx-trust-item"><?php echo nefertari_icon( 'card', '#D93A7C', 22 ); ?> Secure booking · No deposit</div>
	</div>
</section>
