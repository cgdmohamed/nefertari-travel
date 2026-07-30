<?php
/**
 * Booking modal: details -> demo card payment -> processing -> success.
 * All behavior lives in assets/js/booking.js (vanilla JS, no real charge —
 * front-end demo only, matching the original design).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$excursions = new WP_Query( array(
	'post_type'      => 'excursion',
	'posts_per_page' => -1,
	'orderby'        => 'menu_order title',
	'order'          => 'ASC',
	'no_found_rows'  => true,
) );
?>
<div class="nx-modal" id="nx-booking-modal">
	<button type="button" class="nx-modal-backdrop" data-close-booking aria-label="Close"></button>
	<div class="nx-modal-box">
		<div class="nx-modal-head">
			<div>
				<div class="nx-modal-eyebrow">Request a booking</div>
				<div class="nx-modal-title">Reserve your spot</div>
			</div>
			<button type="button" class="nx-modal-close" data-close-booking>✕</button>
		</div>
		<div class="nx-modal-body">

			<!-- DETAILS -->
			<div class="nx-step-view is-active" data-step-view="details">
				<label class="nx-field-label" for="nx-trip-select">Excursion</label>
				<select class="nx-input" id="nx-trip-select">
					<option value="">Select an excursion…</option>
					<?php foreach ( $excursions->posts as $ex ) : ?>
						<option value="<?php echo esc_attr( $ex->ID ); ?>" data-price="<?php echo esc_attr( get_post_meta( $ex->ID, '_nx_price', true ) ); ?>">
							<?php echo esc_html( get_the_title( $ex ) . ' — from ' . nefertari_excursion_price( $ex->ID ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<label class="nx-field-label" for="nx-trip-date">Preferred date</label>
				<input type="date" class="nx-input" id="nx-trip-date">

				<div style="display:flex;flex-direction:column;gap:11px;margin-bottom:18px">
					<div class="nx-counter-row">
						<div><div class="nx-counter-label">Adults</div><div class="nx-counter-sub">Age 12+</div></div>
						<div class="nx-counter-controls">
							<button type="button" class="nx-counter-btn" data-counter="adults" data-dir="-1">−</button>
							<span class="nx-counter-value" id="nx-adults-value">2</span>
							<button type="button" class="nx-counter-btn" data-counter="adults" data-dir="1">+</button>
						</div>
					</div>
					<div class="nx-counter-row">
						<div><div class="nx-counter-label">Children</div><div class="nx-counter-sub">Age 2–11 · half price</div></div>
						<div class="nx-counter-controls">
							<button type="button" class="nx-counter-btn" data-counter="children" data-dir="-1">−</button>
							<span class="nx-counter-value" id="nx-children-value">0</span>
							<button type="button" class="nx-counter-btn" data-counter="children" data-dir="1">+</button>
						</div>
					</div>
				</div>

				<label class="nx-field-label" for="nx-trip-name">Your name</label>
				<input type="text" class="nx-input" id="nx-trip-name" placeholder="e.g. Sarah Müller">

				<div class="nx-total-row">
					<div>
						<div class="nx-total-label">Estimated total</div>
						<div class="nx-total-sub" id="nx-total-breakdown">Select an excursion</div>
					</div>
					<div class="nx-total-value" id="nx-total-value">—</div>
				</div>

				<button type="button" class="nx-btn nx-btn--primary nx-btn--block" id="nx-go-pay" disabled>💳 Pay online &amp; confirm — <span id="nx-pay-cta-total">—</span></button>
				<a href="#" target="_blank" rel="noopener" class="nx-btn nx-btn--outline nx-btn--block" id="nx-wa-request" style="margin-top:10px">Or request via WhatsApp</a>
				<p class="nx-note">No payment now — we'll confirm availability &amp; final price.</p>
			</div>

			<!-- PAY -->
			<div class="nx-step-view" data-step-view="pay">
				<button type="button" class="nx-modal-back" data-back-to-details>← Back to details</button>
				<div class="nx-summary-box">
					<div class="nx-summary-row"><span>Excursion</span><span id="nx-pay-trip-name"></span></div>
					<div class="nx-summary-row"><span>Date</span><span id="nx-pay-date"></span></div>
					<div class="nx-summary-row"><span>Guests</span><span id="nx-pay-guests"></span></div>
					<div class="nx-summary-total"><span>Total to pay</span><span class="nx-total-big" id="nx-pay-total-big"></span></div>
				</div>

				<label class="nx-field-label" for="nx-card-number">Card number</label>
				<input type="text" inputmode="numeric" placeholder="4242 4242 4242 4242" class="nx-input" id="nx-card-number" style="letter-spacing:1px">

				<label class="nx-field-label" for="nx-card-name">Name on card</label>
				<input type="text" placeholder="SARAH MÜLLER" class="nx-input" id="nx-card-name">

				<div style="display:flex;gap:12px;margin-bottom:20px">
					<div style="flex:1">
						<label class="nx-field-label" for="nx-card-exp">Expiry</label>
						<input type="text" inputmode="numeric" placeholder="MM/YY" class="nx-input" id="nx-card-exp" style="margin-bottom:0">
					</div>
					<div style="flex:1">
						<label class="nx-field-label" for="nx-card-cvc">CVC</label>
						<input type="text" inputmode="numeric" placeholder="123" class="nx-input" id="nx-card-cvc" style="margin-bottom:0">
					</div>
				</div>

				<button type="button" class="nx-btn nx-btn--green nx-btn--block" id="nx-pay-btn" disabled>🔒 Pay <span id="nx-pay-btn-total"></span> securely</button>
				<p class="nx-note">Demo checkout — use any card number, e.g. 4242 4242 4242 4242. No real charge is made.</p>
			</div>

			<!-- PROCESSING -->
			<div class="nx-step-view" data-step-view="processing">
				<div class="nx-processing">
					<div class="nx-spinner"></div>
					<div class="nx-processing-title">Processing your payment…</div>
					<div class="nx-processing-sub">Please don't close this window</div>
				</div>
			</div>

			<!-- SUCCESS -->
			<div class="nx-step-view" data-step-view="success">
				<div class="nx-success">
					<div class="nx-success-check">✓</div>
					<div class="nx-success-title">Booking confirmed!</div>
					<p class="nx-success-text">Thank you, <span id="nx-success-name"></span>. Your spot on the <span id="nx-success-trip"></span> is reserved — a confirmation and pickup details are on their way.</p>
					<div class="nx-summary-box" style="text-align:left">
						<div class="nx-summary-row"><span>Booking ref</span><span style="font-weight:800;letter-spacing:0.5px;color:var(--nx-pink)" id="nx-success-ref"></span></div>
						<div class="nx-summary-row"><span>Excursion</span><span id="nx-success-trip-2"></span></div>
						<div class="nx-summary-row"><span>Date</span><span id="nx-success-date"></span></div>
						<div class="nx-summary-row"><span>Guests</span><span id="nx-success-guests"></span></div>
						<div class="nx-summary-total"><span>Paid</span><span class="nx-total-big" style="color:var(--nx-green)" id="nx-success-total"></span></div>
					</div>
					<button type="button" class="nx-btn nx-btn--dark nx-btn--block" id="nx-done-btn">Done</button>
				</div>
			</div>

		</div>
	</div>
</div>
