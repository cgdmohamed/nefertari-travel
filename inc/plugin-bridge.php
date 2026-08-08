<?php
/**
 * Integration with the Nefertari Booking Core plugin, which owns the
 * `excursion` post type, its taxonomy, real slots/capacity, and payments.
 *
 * This file adds only what the plugin doesn't model: marketing/trust-signal
 * fields (star rating, review count, "booked this month") shown on excursion
 * cards and detail pages. These live as plain post meta on the same
 * `excursion` post — a theme can always add a supplementary meta box to a
 * CPT it doesn't own.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_booking_plugin_active() {
	return class_exists( '\Nefertari\Booking\Plugin' );
}

/**
 * Whether the plugin has PayPal turned on (Settings > PayPal Settings). The
 * theme has no other visibility into plugin settings, so this is the one
 * bridge point the booking modal's payment-method choice depends on.
 */
function nefertari_paypal_enabled() {
	if ( ! nefertari_booking_plugin_active() || ! class_exists( '\Nefertari\Booking\Settings\Settings' ) ) {
		return false;
	}
	return (bool) ( new \Nefertari\Booking\Settings\Settings() )->get( 'paypal_enabled' );
}

function nefertari_booking_plugin_notice() {
	if ( nefertari_booking_plugin_active() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>' .
		esc_html__( 'Nefertari Travel theme: the "Nefertari Booking Core" plugin is not active. Excursions, bookings, and payments will not work until it is installed and activated.', 'nefertari-travel' ) .
		'</p></div>';
}
add_action( 'admin_notices', 'nefertari_booking_plugin_notice' );

/* -------------------------------------------------------------------------
 * Trust-signal meta box on the plugin's excursion post type
 * ---------------------------------------------------------------------- */

function nefertari_add_trust_signal_metabox() {
	if ( ! nefertari_booking_plugin_active() ) {
		return;
	}
	add_meta_box( 'nx_trust_signals', __( 'Trust Signals (theme)', 'nefertari-travel' ), 'nefertari_render_trust_signal_metabox', 'excursion', 'side', 'default' );
}
add_action( 'add_meta_boxes_excursion', 'nefertari_add_trust_signal_metabox' );

function nefertari_render_trust_signal_metabox( $post ) {
	wp_nonce_field( 'nefertari_save_trust_signals', 'nefertari_trust_signals_nonce' );
	$rating   = get_post_meta( $post->ID, '_nx_rating', true ) ?: '4.9';
	$reviews  = get_post_meta( $post->ID, '_nx_reviews_count', true ) ?: '500';
	$booked   = get_post_meta( $post->ID, '_nx_booked_count', true ) ?: '40';
	?>
	<p>
		<label for="nx_rating"><strong><?php esc_html_e( 'Rating', 'nefertari-travel' ); ?></strong></label><br>
		<input type="text" id="nx_rating" name="nx_rating" value="<?php echo esc_attr( $rating ); ?>" class="widefat">
	</p>
	<p>
		<label for="nx_reviews_count"><strong><?php esc_html_e( 'Review count', 'nefertari-travel' ); ?></strong></label><br>
		<input type="number" min="0" id="nx_reviews_count" name="nx_reviews_count" value="<?php echo esc_attr( $reviews ); ?>" class="widefat">
	</p>
	<p>
		<label for="nx_booked_count"><strong><?php esc_html_e( 'Booked this month', 'nefertari-travel' ); ?></strong></label><br>
		<input type="number" min="0" id="nx_booked_count" name="nx_booked_count" value="<?php echo esc_attr( $booked ); ?>" class="widefat">
	</p>
	<p class="description"><?php esc_html_e( 'Marketing display only — not tied to real review or booking data.', 'nefertari-travel' ); ?></p>
	<?php
}

function nefertari_save_trust_signal_meta( $post_id ) {
	if ( ! isset( $_POST['nefertari_trust_signals_nonce'] ) || ! wp_verify_nonce( $_POST['nefertari_trust_signals_nonce'], 'nefertari_save_trust_signals' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['nx_rating'] ) ) {
		update_post_meta( $post_id, '_nx_rating', sanitize_text_field( wp_unslash( $_POST['nx_rating'] ) ) );
	}
	if ( isset( $_POST['nx_reviews_count'] ) ) {
		update_post_meta( $post_id, '_nx_reviews_count', absint( $_POST['nx_reviews_count'] ) );
	}
	if ( isset( $_POST['nx_booked_count'] ) ) {
		update_post_meta( $post_id, '_nx_booked_count', absint( $_POST['nx_booked_count'] ) );
	}
}
add_action( 'save_post_excursion', 'nefertari_save_trust_signal_meta' );
