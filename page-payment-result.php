<?php
/**
 * Payment result page (page slug: payment-result) — Kashier redirects here
 * after checkout with ?booking_ref=X. The redirect itself proves nothing;
 * this page polls the plugin's booking-status endpoint since the webhook
 * (the actual source of truth) may land a moment after the redirect.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$booking_ref = isset( $_GET['booking_ref'] ) ? sanitize_text_field( wp_unslash( $_GET['booking_ref'] ) ) : '';

if ( nefertari_booking_plugin_active() ) {
	wp_enqueue_script( 'nefertari-payment-result', NEFERTARI_URI . '/assets/js/payment-result.js', array(), NEFERTARI_VERSION, true );
	wp_localize_script( 'nefertari-payment-result', 'nefertariPaymentResult', array(
		'restUrl'    => esc_url_raw( rest_url( 'nefertari/v1' ) ),
		'nonce'      => wp_create_nonce( 'wp_rest' ),
		'bookingRef' => $booking_ref,
		'accountUrl' => nefertari_account_url( 'account' ),
	) );
}
?>
<div class="nx-result-page" id="nx-payment-result" data-state="<?php echo $booking_ref ? 'pending' : 'missing'; ?>">
	<?php if ( ! $booking_ref ) : ?>
		<div class="nx-result-icon nx-result-icon--failed">✕</div>
		<h1>No booking reference</h1>
		<p>We couldn't find a booking to check. If you completed a payment, check "My Account" for its status.</p>
		<a href="<?php echo esc_url( nefertari_account_url( 'account' ) ); ?>" class="nx-btn nx-btn--primary">Go to My Account →</a>
	<?php else : ?>
		<div class="nx-result-icon nx-result-icon--pending" id="nx-result-icon">
			<span class="nx-spinner" style="width:32px;height:32px;border-width:4px"></span>
		</div>
		<h1 id="nx-result-title">Confirming your payment…</h1>
		<p id="nx-result-text">This usually takes a few seconds. Don't close this page.</p>
		<div id="nx-result-actions"></div>
	<?php endif; ?>
</div>
<?php
get_footer();
