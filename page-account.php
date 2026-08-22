<?php
/**
 * Account page (page slug: account) — profile, booking history, and a
 * booking detail view (?booking=ID) with self-service retry payment and
 * cancellation requests. Booking data is read directly from the plugin's
 * Booking_Service since this is server-rendered in the same request; no
 * REST round-trip needed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( nefertari_account_url( 'account' ) ), nefertari_account_url( 'login' ) ) );
	exit;
}

get_header();

$current_user     = wp_get_current_user();
$plugin_active    = nefertari_booking_plugin_active();
$view_booking_id  = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0;
$booking          = ( $plugin_active && $view_booking_id ) ? ( new \Nefertari\Booking\Services\Booking_Service() )->for_customer_detail( $view_booking_id, $current_user->ID ) : null;
$reviewable       = $plugin_active ? nefertari_reviewable_excursions( $current_user->ID ) : array();
?>
<div class="nx-account-page">

<?php if ( $booking ) : ?>

	<div class="nx-account-head">
		<div>
			<a href="<?php echo esc_url( nefertari_account_url( 'account' ) ); ?>" class="nx-back-link">← Back to My Account</a>
			<h1><?php echo esc_html( get_the_title( (int) $booking['excursion_id'] ) ); ?></h1>
		</div>
		<div style="display:flex;align-items:center;gap:10px">
			<?php if ( isset( $reviewable[ (int) $booking['excursion_id'] ] ) ) : ?>
				<button type="button" class="nx-btn nx-btn--outline nx-btn--sm" data-review-trigger data-review-excursion="<?php echo esc_attr( $booking['excursion_id'] ); ?>" data-review-title="<?php echo esc_attr( $reviewable[ (int) $booking['excursion_id'] ] ); ?>">★ Leave a review</button>
			<?php endif; ?>
			<span class="nx-status-pill nx-status-pill--<?php echo esc_attr( nefertari_booking_status_tone( $booking['status'] ) ); ?>"><?php echo esc_html( nefertari_booking_status_label( $booking['status'] ) ); ?></span>
		</div>
	</div>

	<?php if ( isset( $_GET['retry_error'] ) ) : ?>
		<div class="nx-form-error"><?php echo esc_html( nefertari_retry_error_message( sanitize_key( wp_unslash( $_GET['retry_error'] ) ) ) ); ?></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['cancel_requested'] ) ) : ?>
		<div class="nx-form-success">Cancellation requested — we'll follow up by email shortly.</div>
	<?php endif; ?>
	<?php if ( isset( $_GET['cancel_error'] ) ) : ?>
		<div class="nx-form-error">That cancellation request couldn't be submitted. Please try again or contact us.</div>
	<?php endif; ?>

	<div class="nx-account-grid">
		<div class="nx-panel">
			<h2>Booking details</h2>
			<div class="nx-summary-box">
				<div class="nx-summary-row"><span>Reference</span><span><?php echo esc_html( $booking['booking_ref'] ); ?></span></div>
				<div class="nx-summary-row"><span>Departure</span><span><?php echo esc_html( $booking['departure_start'] ? mysql2date( 'M j, Y g:ia', $booking['departure_start'] ) : 'Not yet scheduled' ); ?></span></div>
				<div class="nx-summary-row"><span>Guests</span><span><?php echo esc_html( $booking['adult_count'] . ' adult(s)' . ( $booking['child_count'] > 0 ? ', ' . $booking['child_count'] . ' child(ren)' : '' ) ); ?></span></div>
				<div class="nx-summary-row"><span>Booked on</span><span><?php echo esc_html( mysql2date( 'M j, Y', $booking['created_at'] ) ); ?></span></div>
				<div class="nx-summary-total"><span>Total</span><span class="nx-total-big">$<?php echo esc_html( number_format( (float) $booking['total_usd'], 2 ) ); ?></span></div>
			</div>

			<?php if ( nefertari_booking_is_retryable( $booking['status'] ) ) : ?>
				<h3 class="nx-subheading" style="margin-top:22px">Finish paying for this booking</h3>
				<p style="font-size:13.5px;color:var(--nx-brown);margin:-4px 0 14px">Your spot isn't confirmed until payment goes through. Pick a payment method to continue.</p>
				<div style="display:flex;gap:10px;flex-wrap:wrap">
					<form method="post">
						<?php wp_nonce_field( 'nefertari_retry_payment_' . $booking['id'], 'nefertari_retry_nonce' ); ?>
						<input type="hidden" name="nefertari_retry_payment" value="<?php echo esc_attr( $booking['id'] ); ?>">
						<input type="hidden" name="payment_method" value="kashier">
						<button type="submit" class="nx-btn nx-btn--primary">💳 Pay with card</button>
					</form>
					<?php if ( nefertari_paypal_enabled() ) : ?>
						<form method="post">
							<?php wp_nonce_field( 'nefertari_retry_payment_' . $booking['id'], 'nefertari_retry_nonce' ); ?>
							<input type="hidden" name="nefertari_retry_payment" value="<?php echo esc_attr( $booking['id'] ); ?>">
							<input type="hidden" name="payment_method" value="paypal">
							<button type="submit" class="nx-btn nx-btn--outline">🅿️ Pay with PayPal</button>
						</form>
					<?php endif; ?>
				</div>
			<?php elseif ( nefertari_booking_is_cancellable( $booking['status'] ) ) : ?>
				<h3 class="nx-subheading" style="margin-top:22px">Need to cancel?</h3>
				<?php if ( $booking['cancellation_policy_snapshot'] ) : ?>
					<p style="font-size:13.5px;color:var(--nx-brown);margin:-4px 0 14px"><?php echo esc_html( $booking['cancellation_policy_snapshot'] ); ?></p>
				<?php endif; ?>
				<form method="post">
					<?php wp_nonce_field( 'nefertari_request_cancellation_' . $booking['id'], 'nefertari_cancel_nonce' ); ?>
					<input type="hidden" name="nefertari_request_cancellation" value="<?php echo esc_attr( $booking['id'] ); ?>">
					<textarea name="reason" class="nx-input" rows="2" placeholder="Reason (optional)"></textarea>
					<button type="submit" class="nx-btn nx-btn--outline" onclick="return confirm('Request cancellation for this booking?');">Request cancellation</button>
				</form>
			<?php elseif ( 'cancellation_requested' === $booking['status'] ) : ?>
				<div class="nx-form-success" style="margin-top:22px">Cancellation requested — we're reviewing it and will follow up by email.</div>
			<?php endif; ?>
		</div>

		<div class="nx-panel">
			<h2>Travelers</h2>
			<?php if ( empty( $booking['passengers'] ) ) : ?>
				<p class="nx-bookings-empty">No passenger details recorded.</p>
			<?php else : ?>
				<div class="nx-table-scroll">
				<table class="nx-bookings-table">
					<thead><tr><th>Name</th><th>Type</th><th>Nationality</th><th>Passport</th></tr></thead>
					<tbody>
						<?php foreach ( $booking['passengers'] as $passenger ) : ?>
							<tr>
								<td><?php echo esc_html( trim( $passenger['first_name'] . ' ' . $passenger['last_name'] ) ); ?></td>
								<td><?php echo esc_html( ucfirst( $passenger['passenger_type'] ) ); ?></td>
								<td><?php echo esc_html( $passenger['nationality'] ); ?></td>
								<td><?php echo esc_html( $passenger['passport_number'] ?: '—' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				</div>
			<?php endif; ?>

			<h2 style="margin-top:26px">Payment history</h2>
			<?php if ( empty( $booking['payments'] ) ) : ?>
				<p class="nx-bookings-empty">No payment attempts yet.</p>
			<?php else : ?>
				<div class="nx-table-scroll">
				<table class="nx-bookings-table">
					<thead><tr><th>Date</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead>
					<tbody>
						<?php foreach ( $booking['payments'] as $payment ) : ?>
							<tr>
								<td><?php echo esc_html( mysql2date( 'M j, Y g:ia', $payment['created_at'] ) ); ?></td>
								<td><?php echo esc_html( ucfirst( $payment['provider'] ?: 'kashier' ) ); ?></td>
								<td>$<?php echo esc_html( number_format( (float) $payment['amount_usd'], 2 ) ); ?></td>
								<td><span class="nx-status-pill nx-status-pill--<?php echo esc_attr( nefertari_booking_status_tone( $payment['status'] ) ); ?>"><?php echo esc_html( nefertari_booking_status_label( $payment['status'] ) ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				</div>
			<?php endif; ?>
		</div>
	</div>

<?php else : ?>

	<?php
	$phone    = get_user_meta( $current_user->ID, 'nefertari_phone', true );
	$updated  = isset( $_GET['updated'] );
	$bookings = $plugin_active ? ( new \Nefertari\Booking\Services\Booking_Service() )->for_customer( $current_user->ID ) : array();
	?>
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
			<?php if ( ! $plugin_active ) : ?>
				<p class="nx-bookings-empty">Booking history isn't available right now.</p>
			<?php elseif ( empty( $bookings ) ) : ?>
				<p class="nx-bookings-empty">You haven't booked an excursion yet.</p>
				<a href="<?php echo esc_url( home_url( '/#excursions' ) ); ?>" class="nx-btn nx-btn--primary" style="margin-top:14px">Explore excursions →</a>
			<?php else : ?>
				<div class="nx-table-scroll">
				<table class="nx-bookings-table">
					<thead>
						<tr><th>Excursion</th><th>Departure</th><th>Guests</th><th>Total</th><th>Status</th><th></th></tr>
					</thead>
					<tbody>
						<?php foreach ( $bookings as $row ) : ?>
							<tr>
								<td>
									<strong><?php echo esc_html( get_the_title( (int) $row['excursion_id'] ) ); ?></strong><br>
									<span style="color:var(--nx-brown);font-size:12.5px"><?php echo esc_html( $row['booking_ref'] ); ?></span>
								</td>
								<td><?php echo $row['departure_start'] ? esc_html( mysql2date( 'M j, Y', $row['departure_start'] ) ) : '—'; ?></td>
								<td><?php echo esc_html( $row['adult_count'] . ' adult(s)' . ( $row['child_count'] > 0 ? ', ' . $row['child_count'] . ' child(ren)' : '' ) ); ?></td>
								<td>$<?php echo esc_html( number_format( (float) $row['total_usd'], 2 ) ); ?></td>
								<td><span class="nx-status-pill nx-status-pill--<?php echo esc_attr( nefertari_booking_status_tone( $row['status'] ) ); ?>"><?php echo esc_html( nefertari_booking_status_label( $row['status'] ) ); ?></span></td>
								<td>
									<div class="nx-bookings-actions">
									<a href="<?php echo esc_url( add_query_arg( 'booking', $row['id'], nefertari_account_url( 'account' ) ) ); ?>" class="nx-btn nx-btn--outline nx-btn--sm">View</a>
									<?php if ( nefertari_booking_is_retryable( $row['status'] ) ) : ?>
										<form method="post">
											<?php wp_nonce_field( 'nefertari_retry_payment_' . $row['id'], 'nefertari_retry_nonce' ); ?>
											<input type="hidden" name="nefertari_retry_payment" value="<?php echo esc_attr( $row['id'] ); ?>">
											<input type="hidden" name="payment_method" value="kashier">
											<button type="submit" class="nx-btn nx-btn--primary nx-btn--sm">Retry payment</button>
										</form>
								<?php elseif ( isset( $reviewable[ (int) $row['excursion_id'] ] ) ) : ?>
									<button type="button" class="nx-btn nx-btn--primary nx-btn--sm" data-review-trigger data-review-excursion="<?php echo esc_attr( $row['excursion_id'] ); ?>" data-review-title="<?php echo esc_attr( $reviewable[ (int) $row['excursion_id'] ] ); ?>">★ Review</button>
									<?php endif; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				</div>
			<?php endif; ?>
		</div>

	</div>

<?php endif; ?>

<div class="nx-modal" id="nx-review-modal" data-reviewable="<?php echo esc_attr( wp_json_encode( $reviewable ) ); ?>">
	<button type="button" class="nx-modal-backdrop" data-review-close aria-label="Close"></button>
	<div class="nx-modal-box">
		<div class="nx-modal-head">
			<div>
				<div class="nx-modal-eyebrow">Share your trip</div>
				<div class="nx-modal-title" id="nx-review-modal-excursion">Leave a review</div>
			</div>
			<button type="button" class="nx-modal-close" data-review-close aria-label="Close">✕</button>
		</div>
		<div class="nx-modal-body">
			<?php if ( isset( $_GET['review_submitted'] ) ) : ?>
				<div class="nx-form-success">Thanks! Your review is in — it'll appear on the site once we've had a quick look.</div>
			<?php elseif ( isset( $_GET['review_error'] ) ) : ?>
				<div class="nx-form-error"><?php echo esc_html( nefertari_review_error_message( sanitize_key( wp_unslash( $_GET['review_error'] ) ) ) ); ?></div>
			<?php endif; ?>

			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'nefertari_submit_review', 'nefertari_review_nonce' ); ?>
				<input type="hidden" name="nefertari_submit_review" value="1">
				<input type="hidden" name="excursion_id" id="nx-review-excursion-id" value="">

				<label class="nx-field-label">Your rating</label>
				<div class="nx-rating-input">
					<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
						<input type="radio" id="review-rating-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>"<?php checked( 5, $i ); ?>>
						<label for="review-rating-<?php echo $i; ?>">★</label>
					<?php endfor; ?>
				</div>

				<label class="nx-field-label" for="review-text">Your review</label>
				<textarea id="review-text" name="review_text" class="nx-input" rows="3" required placeholder="Tell other travellers about your trip…"></textarea>

				<label class="nx-field-label" for="review-images">Add photos (optional, up to 6)</label>
				<input type="file" id="review-images" name="review_images[]" accept="image/jpeg,image/png,image/webp" multiple>
				<div class="nx-review-upload-preview" id="nx-review-upload-preview"></div>

				<button type="submit" class="nx-btn nx-btn--primary nx-btn--block">Submit review</button>
			</form>
		</div>
	</div>
</div>

</div>
<?php
get_footer();
