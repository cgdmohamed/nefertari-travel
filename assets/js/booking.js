/**
 * Booking modal: details -> demo card payment -> processing -> success.
 * Front-end only — no real charge is ever made (any card number "works"),
 * matching the original design's explicit demo scope.
 */
( function () {
	'use strict';

	var cfg = window.nefertariBooking || { whatsapp: '', siteName: 'Nefertari Travel' };

	var modal = document.getElementById( 'nx-booking-modal' );
	if ( ! modal ) {
		return;
	}

	var els = {
		tripSelect: document.getElementById( 'nx-trip-select' ),
		date: document.getElementById( 'nx-trip-date' ),
		name: document.getElementById( 'nx-trip-name' ),
		adultsValue: document.getElementById( 'nx-adults-value' ),
		childrenValue: document.getElementById( 'nx-children-value' ),
		totalBreakdown: document.getElementById( 'nx-total-breakdown' ),
		totalValue: document.getElementById( 'nx-total-value' ),
		goPay: document.getElementById( 'nx-go-pay' ),
		payCtaTotal: document.getElementById( 'nx-pay-cta-total' ),
		waRequest: document.getElementById( 'nx-wa-request' ),
		payTripName: document.getElementById( 'nx-pay-trip-name' ),
		payDate: document.getElementById( 'nx-pay-date' ),
		payGuests: document.getElementById( 'nx-pay-guests' ),
		payTotalBig: document.getElementById( 'nx-pay-total-big' ),
		cardNumber: document.getElementById( 'nx-card-number' ),
		cardName: document.getElementById( 'nx-card-name' ),
		cardExp: document.getElementById( 'nx-card-exp' ),
		cardCvc: document.getElementById( 'nx-card-cvc' ),
		payBtn: document.getElementById( 'nx-pay-btn' ),
		payBtnTotal: document.getElementById( 'nx-pay-btn-total' ),
		successName: document.getElementById( 'nx-success-name' ),
		successTrip: document.getElementById( 'nx-success-trip' ),
		successTrip2: document.getElementById( 'nx-success-trip-2' ),
		successDate: document.getElementById( 'nx-success-date' ),
		successGuests: document.getElementById( 'nx-success-guests' ),
		successTotal: document.getElementById( 'nx-success-total' ),
		successRef: document.getElementById( 'nx-success-ref' ),
		doneBtn: document.getElementById( 'nx-done-btn' ),
	};

	var state = { adults: 2, children: 0 };

	function waLink( text ) {
		return 'https://wa.me/' + cfg.whatsapp + '?text=' + encodeURIComponent( text );
	}

	function selectedOption() {
		return els.tripSelect.options[ els.tripSelect.selectedIndex ] || null;
	}

	function tripPrice() {
		var opt = selectedOption();
		return opt && opt.value ? parseInt( opt.getAttribute( 'data-price' ), 10 ) || 0 : 0;
	}

	function tripName() {
		var opt = selectedOption();
		if ( ! opt || ! opt.value ) {
			return '';
		}
		return opt.textContent.split( ' — ' )[ 0 ].trim();
	}

	function guestLine() {
		var line = state.adults + ' adult' + ( state.adults > 1 ? 's' : '' );
		if ( state.children > 0 ) {
			line += ', ' + state.children + ' child' + ( state.children > 1 ? 'ren' : '' );
		}
		return line;
	}

	function recalc() {
		var price = tripPrice();
		var hasTrip = !! els.tripSelect.value;
		var childPrice = Math.round( price / 2 );
		var total = price * state.adults + childPrice * state.children;

		if ( ! hasTrip ) {
			els.totalBreakdown.textContent = 'Select an excursion';
			els.totalValue.textContent = '—';
			els.payCtaTotal.textContent = '—';
			els.goPay.disabled = true;
			els.waRequest.setAttribute( 'href', '#' );
			return;
		}

		var parts = [ state.adults + ' adult' + ( state.adults > 1 ? 's' : '' ) + ' × $' + price ];
		if ( state.children > 0 ) {
			parts.push( state.children + ' child' + ( state.children > 1 ? 'ren' : '' ) + ' × $' + childPrice );
		}

		els.totalBreakdown.textContent = parts.join( '   +   ' );
		els.totalValue.textContent = '$' + total;
		els.payCtaTotal.textContent = '$' + total;
		els.goPay.disabled = false;

		var message =
			'Hi ' + cfg.siteName + "! I'd like to request a booking:\n" +
			'• Excursion: ' + tripName() + '\n' +
			'• Date: ' + ( els.date.value || 'to be confirmed' ) + '\n' +
			'• Guests: ' + guestLine() + '\n' +
			'• Name: ' + ( els.name.value || '—' ) + '\n' +
			'• Estimated total (approx): $' + total + '\n' +
			'Please confirm availability. Thank you!';
		els.waRequest.setAttribute( 'href', waLink( message ) );
	}

	function setCounter( type, dir ) {
		if ( type === 'adults' ) {
			state.adults = Math.max( 1, Math.min( 20, state.adults + dir ) );
			els.adultsValue.textContent = state.adults;
		} else {
			state.children = Math.max( 0, Math.min( 20, state.children + dir ) );
			els.childrenValue.textContent = state.children;
		}
		recalc();
	}

	modal.querySelectorAll( '[data-counter]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			setCounter( btn.getAttribute( 'data-counter' ), parseInt( btn.getAttribute( 'data-dir' ), 10 ) );
		} );
	} );

	[ els.tripSelect, els.date, els.name ].forEach( function ( el ) {
		el.addEventListener( 'input', recalc );
		el.addEventListener( 'change', recalc );
	} );

	/* Steps ----------------------------------------------------------------*/

	function showStep( step ) {
		modal.querySelectorAll( '[data-step-view]' ).forEach( function ( view ) {
			view.classList.toggle( 'is-active', view.getAttribute( 'data-step-view' ) === step );
		} );
	}

	els.goPay.addEventListener( 'click', function () {
		if ( ! els.tripSelect.value ) {
			return;
		}
		els.payTripName.textContent = tripName();
		els.payDate.textContent = els.date.value || 'To be confirmed';
		els.payGuests.textContent = guestLine();
		var totalText = els.totalValue.textContent;
		els.payTotalBig.textContent = totalText;
		els.payBtnTotal.textContent = totalText;
		showStep( 'pay' );
	} );

	modal.querySelector( '[data-back-to-details]' ).addEventListener( 'click', function () {
		showStep( 'details' );
	} );

	/* Card formatting --------------------------------------------------------*/

	els.cardNumber.addEventListener( 'input', function ( e ) {
		var digits = e.target.value.replace( /\D/g, '' ).slice( 0, 16 );
		var groups = digits.match( /.{1,4}/g );
		e.target.value = groups ? groups.join( ' ' ) : '';
		validateCard();
	} );

	els.cardExp.addEventListener( 'input', function ( e ) {
		var digits = e.target.value.replace( /\D/g, '' ).slice( 0, 4 );
		if ( digits.length > 2 ) {
			digits = digits.slice( 0, 2 ) + '/' + digits.slice( 2 );
		}
		e.target.value = digits;
		validateCard();
	} );

	els.cardCvc.addEventListener( 'input', function ( e ) {
		e.target.value = e.target.value.replace( /\D/g, '' ).slice( 0, 4 );
		validateCard();
	} );

	els.cardName.addEventListener( 'input', validateCard );

	function validateCard() {
		var digits = els.cardNumber.value.replace( /\D/g, '' ).length;
		var valid = digits >= 12 && els.cardName.value.trim() !== '' && els.cardExp.value.length >= 5 && els.cardCvc.value.length >= 3;
		els.payBtn.disabled = ! valid;
		return valid;
	}

	els.payBtn.addEventListener( 'click', function () {
		if ( ! validateCard() ) {
			return;
		}
		showStep( 'processing' );
		setTimeout( function () {
			var ref = 'NEF-' + Math.random().toString( 36 ).slice( 2, 6 ).toUpperCase() + Math.floor( 100 + Math.random() * 900 );
			els.successRef.textContent = ref;
			els.successName.textContent = els.name.value || 'traveller';
			els.successTrip.textContent = tripName();
			els.successTrip2.textContent = tripName();
			els.successDate.textContent = els.date.value || 'To be confirmed';
			els.successGuests.textContent = guestLine();
			els.successTotal.textContent = els.payTotalBig.textContent;
			showStep( 'success' );
		}, 1900 );
	} );

	els.doneBtn.addEventListener( 'click', closeBooking );

	/* Open / close -----------------------------------------------------------*/

	function openBooking( tripId ) {
		if ( tripId ) {
			els.tripSelect.value = String( tripId );
		}
		recalc();
		showStep( 'details' );
		modal.classList.add( 'is-open' );
		document.body.style.overflow = 'hidden';
	}

	function closeBooking() {
		modal.classList.remove( 'is-open' );
		document.body.style.overflow = '';
	}

	document.querySelectorAll( '[data-open-booking]' ).forEach( function ( trigger ) {
		trigger.addEventListener( 'click', function () {
			openBooking( trigger.getAttribute( 'data-trip-id' ) );
		} );
	} );

	modal.querySelectorAll( '[data-close-booking]' ).forEach( function ( el ) {
		el.addEventListener( 'click', closeBooking );
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && modal.classList.contains( 'is-open' ) ) {
			closeBooking();
		}
	} );

	recalc();
} )();
