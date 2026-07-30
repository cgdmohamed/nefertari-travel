/**
 * Polls the plugin's booking-status endpoint after a Kashier redirect.
 * The redirect itself proves nothing — the webhook is the source of truth
 * and may land a moment after the customer lands back on this page.
 */
( function () {
	'use strict';

	var cfg = window.nefertariPaymentResult;
	var root = document.getElementById( 'nx-payment-result' );
	if ( ! cfg || ! root || 'pending' !== root.getAttribute( 'data-state' ) ) {
		return;
	}

	var icon    = document.getElementById( 'nx-result-icon' );
	var title   = document.getElementById( 'nx-result-title' );
	var text    = document.getElementById( 'nx-result-text' );
	var actions = document.getElementById( 'nx-result-actions' );

	var attempts = 0;
	var maxAttempts = 12;

	function setState( state, iconChar, titleText, bodyText ) {
		root.setAttribute( 'data-state', state );
		icon.className = 'nx-result-icon nx-result-icon--' + state;
		icon.innerHTML = iconChar;
		title.textContent = titleText;
		text.textContent = bodyText;
	}

	function showAccountLink( label ) {
		actions.innerHTML = '';
		var link = document.createElement( 'a' );
		link.href = cfg.accountUrl;
		link.className = 'nx-btn nx-btn--primary';
		link.textContent = label;
		actions.appendChild( link );
	}

	function check() {
		attempts++;
		fetch( cfg.restUrl + '/booking/' + encodeURIComponent( cfg.bookingRef ) + '/status', {
			headers: { 'X-WP-Nonce': cfg.nonce }
		} )
			.then( function ( res ) { return res.json(); } )
			.then( function ( data ) {
				var status = data.status || '';

				if ( 'confirmed' === status || 'completed' === status ) {
					setState( 'success', '✓', 'Booking confirmed!', 'Your spot on ' + ( data.excursion_name || 'your excursion' ) + ' is reserved. A confirmation is on its way.' );
					showAccountLink( 'View my bookings →' );
					return;
				}
				if ( 'payment_failed' === status || 'payment_expired' === status || 'cancelled' === status ) {
					setState( 'failed', '✕', 'Payment didn’t go through', 'Your booking wasn’t confirmed. No charge should have been made — please try again.' );
					showAccountLink( 'Back to My Account' );
					return;
				}
				if ( attempts >= maxAttempts ) {
					setState( 'pending', '…', 'Still confirming…', 'This is taking longer than usual. Check My Account in a minute, or contact us if it doesn’t update.' );
					showAccountLink( 'Check My Account' );
					return;
				}
				setTimeout( check, 2500 );
			} )
			.catch( function () {
				if ( attempts >= maxAttempts ) {
					setState( 'pending', '…', 'Still confirming…', 'Check My Account for the latest status.' );
					showAccountLink( 'Check My Account' );
					return;
				}
				setTimeout( check, 2500 );
			} );
	}

	check();
} )();
