<?php
/**
 * "Why choose us" feature tiles.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tiles = array(
	array( 'icon' => 'shuttle', 'grad' => 'linear-gradient(135deg,#FBBA6A,#F5786D)', 'title' => 'Free hotel pickup', 'text' => 'Door-to-door transfers from all major resorts, included on every excursion.' ),
	array( 'icon' => 'compass', 'grad' => 'linear-gradient(135deg,#F5786D,#D93A7C)', 'title' => 'Expert local guides', 'text' => 'Licensed Egyptologists and dive masters who bring every site to life.' ),
	array( 'icon' => 'whatsapp', 'grad' => 'linear-gradient(135deg,#36A9E1,#1AA7A0)', 'title' => 'Book in seconds', 'text' => 'No deposit needed — reserve your spot directly over WhatsApp.' ),
	array( 'icon' => 'star-burst', 'grad' => 'linear-gradient(135deg,#FBBA6A,#D93A7C)', 'title' => 'Loved by travellers', 'text' => 'A ' . nefertari_option( 'rating', '4.9' ) . '-star average from thousands of guests across the Red Sea.' ),
);
?>
<section id="why" class="nx-why">
	<div class="nx-why-inner">
		<h2>Travel with people who know Egypt</h2>
		<p class="nx-why-lede">Licensed guides, comfortable air-conditioned transfers and small groups — so every trip feels personal.</p>
		<div class="nx-grid-4">
			<?php foreach ( $tiles as $tile ) : ?>
				<div class="nx-why-card">
					<div class="nx-why-icon" style="background:<?php echo esc_attr( $tile['grad'] ); ?>"><?php echo nefertari_icon( $tile['icon'], '#fff', 24 ); ?></div>
					<div class="nx-why-title"><?php echo esc_html( $tile['title'] ); ?></div>
					<div class="nx-why-text"><?php echo esc_html( $tile['text'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
