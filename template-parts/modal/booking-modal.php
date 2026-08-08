<?php
/**
 * Booking modal: real flow via the Nefertari Booking Core plugin's REST API
 * (availability -> price -> create-payment-session -> Kashier redirect).
 * Booking requires a WordPress account; logged-out visitors see a login/
 * register prompt instead of the form. All behavior lives in assets/js/booking.js.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! nefertari_booking_plugin_active() ) {
	return;
}

$excursions = new WP_Query( array(
	'post_type'      => 'excursion',
	'posts_per_page' => -1,
	'orderby'        => 'menu_order title',
	'order'          => 'ASC',
	'no_found_rows'  => true,
) );

$is_logged_in = is_user_logged_in();
?>
<div class="nx-modal" id="nx-booking-modal">
	<button type="button" class="nx-modal-backdrop" data-close-booking aria-label="Close"></button>
	<div class="nx-modal-box">
		<div class="nx-modal-head">
			<div>
				<div class="nx-modal-eyebrow">Book &amp; pay online</div>
				<div class="nx-modal-title">Reserve your spot</div>
			</div>
			<button type="button" class="nx-modal-close" data-close-booking>✕</button>
		</div>
		<div class="nx-modal-body">

			<!-- GATE (logged out) -->
			<div class="nx-step-view<?php echo $is_logged_in ? '' : ' is-active'; ?>" data-step-view="gate">
				<p style="text-align:center;color:var(--nx-ink-soft);margin:6px 0 22px">Booking online requires a free account, so we can send your confirmation and keep your passenger details for check-in.</p>
				<a href="#" class="nx-btn nx-btn--primary nx-btn--block" id="nx-gate-login" style="margin-bottom:10px">Log in</a>
				<a href="#" class="nx-btn nx-btn--outline nx-btn--block" id="nx-gate-register">Create an account</a>
			</div>

			<!-- DETAILS -->
			<div class="nx-step-view<?php echo $is_logged_in ? ' is-active' : ''; ?>" data-step-view="details">

				<div class="nx-progress-track">
					<div class="nx-progress-step" data-progress-step="trip">
						<span class="nx-progress-dot">1</span><span class="nx-progress-label">Trip</span>
					</div>
					<div class="nx-progress-step" data-progress-step="travelers">
						<span class="nx-progress-dot">2</span><span class="nx-progress-label">Travelers</span>
					</div>
					<div class="nx-progress-step" data-progress-step="contact">
						<span class="nx-progress-dot">3</span><span class="nx-progress-label">Contact &amp; pay</span>
					</div>
				</div>

				<!-- SUB-STEP: trip & guests -->
				<div class="nx-sub-step" data-sub-step="trip">
					<div class="nx-trip-field" id="nx-trip-field">
						<div class="nx-trip-picker">
							<label class="nx-field-label" for="nx-trip-select">Excursion</label>
							<select class="nx-input" id="nx-trip-select">
								<option value="">Select an excursion…</option>
								<?php foreach ( $excursions->posts as $ex ) : ?>
									<option value="<?php echo esc_attr( $ex->ID ); ?>">
										<?php echo esc_html( get_the_title( $ex ) . ' — from ' . nefertari_excursion_price( $ex->ID ) ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="nx-trip-locked">
							<div class="nx-field-label">Excursion</div>
							<div class="nx-trip-locked-row">
								<span class="nx-trip-locked-name" id="nx-trip-locked-name"></span>
								<button type="button" class="nx-trip-change-btn" data-unlock-trip>Change</button>
							</div>
						</div>
					</div>

					<label class="nx-field-label" for="nx-slot-select">Departure</label>
					<select class="nx-input" id="nx-slot-select" disabled>
						<option value="">Select an excursion first…</option>
					</select>

					<div style="display:flex;flex-direction:column;gap:11px;margin-bottom:22px">
						<div class="nx-counter-row">
							<div><div class="nx-counter-label">Adults</div><div class="nx-counter-sub">Age 12+</div></div>
							<div class="nx-counter-controls">
								<button type="button" class="nx-counter-btn" data-counter="adults" data-dir="-1">−</button>
								<span class="nx-counter-value" id="nx-adults-value">2</span>
								<button type="button" class="nx-counter-btn" data-counter="adults" data-dir="1">+</button>
							</div>
						</div>
						<div class="nx-counter-row">
							<div><div class="nx-counter-label">Children</div><div class="nx-counter-sub">Half price, exact age set below</div></div>
							<div class="nx-counter-controls">
								<button type="button" class="nx-counter-btn" data-counter="children" data-dir="-1">−</button>
								<span class="nx-counter-value" id="nx-children-value">0</span>
								<button type="button" class="nx-counter-btn" data-counter="children" data-dir="1">+</button>
							</div>
						</div>
					</div>

					<button type="button" class="nx-btn nx-btn--primary nx-btn--block" id="nx-trip-next" data-next-step disabled>Continue</button>
				</div>

				<!-- SUB-STEP: travelers -->
				<div class="nx-sub-step" data-sub-step="travelers">
					<button type="button" class="nx-modal-back" data-prev-step>← Back</button>
					<p class="nx-substep-hint">Passport details for each traveler — needed for your booking confirmation and check-in.</p>
					<div id="nx-passenger-forms"></div>
					<button type="button" class="nx-btn nx-btn--primary nx-btn--block" id="nx-travelers-next" data-next-step disabled>Continue</button>
				</div>

				<!-- SUB-STEP: contact & pay -->
				<div class="nx-sub-step" data-sub-step="contact">
					<button type="button" class="nx-modal-back" data-prev-step>← Back</button>

					<label class="nx-field-label" for="nx-contact-name">Full name</label>
					<input type="text" id="nx-contact-name" class="nx-input" placeholder="e.g. Sarah Müller">

					<label class="nx-field-label" for="nx-contact-phone">Phone (with country code)</label>
					<input type="text" id="nx-contact-phone" class="nx-input" placeholder="+49 …">

					<label class="nx-field-label" for="nx-contact-country">Country of residence</label>
					<input type="text" id="nx-contact-country" class="nx-input">

					<div style="display:flex;gap:12px">
						<div style="flex:1">
							<label class="nx-field-label" for="nx-contact-hotel">Hotel</label>
							<input type="text" id="nx-contact-hotel" class="nx-input">
						</div>
						<div style="flex:1">
							<label class="nx-field-label" for="nx-contact-room">Room number</label>
							<input type="text" id="nx-contact-room" class="nx-input">
						</div>
					</div>

					<label class="nx-field-label" for="nx-contact-pickup">Pickup location</label>
					<input type="text" id="nx-contact-pickup" class="nx-input" placeholder="Hotel name or marina">

					<label class="nx-field-label" for="nx-contact-notes">Special requests</label>
					<textarea id="nx-contact-notes" class="nx-input" rows="2"></textarea>

					<label class="nx-toggle" style="display:flex;align-items:flex-start;gap:8px;font-size:13px;font-weight:500;color:var(--nx-ink-soft);margin-bottom:18px">
						<input type="checkbox" id="nx-terms" style="margin-top:3px"> I accept the terms, privacy policy, and cancellation policy.
					</label>

					<div class="nx-total-row">
						<div>
							<div class="nx-total-label">Estimated total</div>
							<div class="nx-total-sub" id="nx-total-breakdown">Select an excursion and departure</div>
						</div>
						<div class="nx-total-value" id="nx-total-value">—</div>
					</div>

					<?php if ( nefertari_paypal_enabled() ) : ?>
						<div class="nx-payment-methods" id="nx-payment-methods">
							<label class="nx-payment-method is-active">
								<input type="radio" name="nx-payment-method" value="kashier" checked> 💳 Card
							</label>
							<label class="nx-payment-method">
								<input type="radio" name="nx-payment-method" value="paypal"> 🅿️ PayPal
							</label>
						</div>
					<?php endif; ?>

					<button type="button" class="nx-btn nx-btn--primary nx-btn--block" id="nx-pay-btn" disabled>🔒 Pay securely — <span id="nx-pay-cta-total">—</span></button>
					<p class="nx-note" id="nx-form-message"></p>
				</div>
			</div>

			<!-- REDIRECTING -->
			<div class="nx-step-view" data-step-view="redirecting">
				<div class="nx-processing">
					<div class="nx-spinner"></div>
					<div class="nx-processing-title">Creating your secure payment session…</div>
					<div class="nx-processing-sub">You'll be redirected to our payment provider to complete payment.</div>
				</div>
			</div>

		</div>
	</div>
</div>
