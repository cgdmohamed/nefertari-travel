<?php
/**
 * Account page (page slug: account) — profile + booking history.
 * Booking history is read directly from the plugin's Booking_Service since
 * this is a server-rendered page in the same request; no REST round-trip needed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( nefertari_account_url( 'account' ) ), nefertari_account_url( 'login' ) ) );
	exit;
}

get_header();

$current_user = wp_get_current_user();
$phone        = get_user_meta( $current_user->ID, 'nefertari_phone', true );
$updated      = isset( $_GET['updated'] );

$bookings = array();
if ( nefertari_booking_plugin_active() ) {
	$bookings = ( new \Nefertari\Booking\Services\Booking_Service() )->for_customer( $current_user->ID );
}
?>
<div class="nx-account-page">
	<div class="nx-account-head">
		<h1>My Account</h1>
		<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="nx-btn nx-btn--outline nx-btn--sm">Log out</a>
	</div>

	<div class="nx-account-grid">
		<div class="nx-panel">
			<h2>Profile</h2>
			<?php if ( $updated ) : ?>
				<div class="nx-form-success">Profile updated.</div>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'nefertari_update_profile', 'nefertari_account_nonce' ); ?>
				<input type="hidden" name="nefertari_update_profile" value="1">

				<label class="nx-field-label" for="name">Full name</label>
				<input type="text" id="name" name="name" class="nx-input" value="<?php echo esc_attr( $current_user->display_name ); ?>">

				<label class="nx-field-label" for="phone">Phone</label>
				<input type="text" id="phone" name="phone" class="nx-input" value="<?php echo esc_attr( $phone ); ?>" placeholder="With country code">

				<label class="nx-field-label">Email</label>
				<input type="text" class="nx-input" value="<?php echo esc_attr( $current_user->user_email ); ?>" disabled style="background:#F4EADF">

				<button type="submit" class="nx-btn nx-btn--primary nx-btn--block">Save changes</button>
			</form>
		</div>

		<div class="nx-panel">
			<h2>My bookings</h2>
			<?php if ( ! nefertari_booking_plugin_active() ) : ?>
				<p class="nx-bookings-empty">Booking history isn't available right now.</p>
			<?php elseif ( empty( $bookings ) ) : ?>
				<p class="nx-bookings-empty">You haven't booked an excursion yet.</p>
				<a href="<?php echo esc_url( home_url( '/#excursions' ) ); ?>" class="nx-btn nx-btn--primary" style="margin-top:14px">Explore excursions →</a>
			<?php else : ?>
				<table class="nx-bookings-table">
					<thead>
						<tr><th>Excursion</th><th>Departure</th><th>Guests</th><th>Total</th><th>Status</th></tr>
					</thead>
					<tbody>
						<?php foreach ( $bookings as $booking ) : ?>
							<tr>
								<td>
									<strong><?php echo esc_html( get_the_title( (int) $booking['excursion_id'] ) ); ?></strong><br>
									<span style="color:var(--nx-brown);font-size:12.5px"><?php echo esc_html( $booking['booking_ref'] ); ?></span>
								</td>
								<td><?php echo $booking['departure_start'] ? esc_html( mysql2date( 'M j, Y', $booking['departure_start'] ) ) : '—'; ?></td>
								<td><?php echo esc_html( $booking['adult_count'] . ' adult(s)' . ( $booking['child_count'] > 0 ? ', ' . $booking['child_count'] . ' child(ren)' : '' ) ); ?></td>
								<td>$<?php echo esc_html( number_format( (float) $booking['total_usd'], 2 ) ); ?></td>
								<td><span class="nx-status-pill nx-status-pill--<?php echo esc_attr( nefertari_booking_status_tone( $booking['status'] ) ); ?>"><?php echo esc_html( nefertari_booking_status_label( $booking['status'] ) ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
