/**
 * Booking modal: real flow against the Nefertari Booking Core plugin's
 * REST API — real departure slots, real passenger/passport capture, a real
 * live price, and a redirect to Kashier's actual checkout. Booking requires
 * a WordPress account; logged-out visitors see a login/register prompt.
 */
( function () {
	'use strict';

	var cfg = window.nefertariBooking;
	var modal = document.getElementById( 'nx-booking-modal' );
	if ( ! cfg || ! modal ) {
		return;
	}

	/* Open / close is wired up first and depends on nothing else below, so a
	 * problem anywhere in the form (a missing field, a failed fetch, etc.)
	 * can never leave the "Book" button unresponsive. */
	function closeBooking() {
		modal.classList.remove( 'is-open' );
		document.body.style.overflow = '';
	}

	function openBookingSafe( tripId ) {
		try {
			openBooking( tripId );
		} catch ( err ) {
			// Even if step/field setup below fails, still show the modal.
			modal.classList.add( 'is-open' );
			document.body.style.overflow = 'hidden';
			if ( window.console ) {
				console.error( 'Nefertari booking modal error:', err ); // eslint-disable-line no-console
			}
		}
	}

	document.querySelectorAll( '[data-open-booking]' ).forEach( function ( trigger ) {
		trigger.addEventListener( 'click', function () {
			openBookingSafe( trigger.getAttribute( 'data-trip-id' ) );
		} );
	} );

	modal.querySelectorAll( '[data-close-booking]' ).forEach( function ( el ) {
		el.addEventListener( 'click', closeBooking );
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && modal.classList.contains( 'is-open' ) ) {
			closeBooking();
		}
	} );

	var els = {
		tripSelect: document.getElementById( 'nx-trip-select' ),
		slotSelect: document.getElementById( 'nx-slot-select' ),
		adultsValue: document.getElementById( 'nx-adults-value' ),
		childrenValue: document.getElementById( 'nx-children-value' ),
		passengerForms: document.getElementById( 'nx-passenger-forms' ),
		contactName: document.getElementById( 'nx-contact-name' ),
		contactPhone: document.getElementById( 'nx-contact-phone' ),
		contactCountry: document.getElementById( 'nx-contact-country' ),
		contactHotel: document.getElementById( 'nx-contact-hotel' ),
		contactRoom: document.getElementById( 'nx-contact-room' ),
		contactPickup: document.getElementById( 'nx-contact-pickup' ),
		contactNotes: document.getElementById( 'nx-contact-notes' ),
		terms: document.getElementById( 'nx-terms' ),
		totalBreakdown: document.getElementById( 'nx-total-breakdown' ),
		totalValue: document.getElementById( 'nx-total-value' ),
		payBtn: document.getElementById( 'nx-pay-btn' ),
		payCtaTotal: document.getElementById( 'nx-pay-cta-total' ),
		formMessage: document.getElementById( 'nx-form-message' ),
		gateLogin: document.getElementById( 'nx-gate-login' ),
		gateRegister: document.getElementById( 'nx-gate-register' ),
	};

	var state = { adults: 2, children: 0, lastPrice: null };

	/* API helper --------------------------------------------------------------*/

	function api( path, method, body ) {
		return fetch( cfg.restUrl + path, {
			method: method,
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
			body: body ? JSON.stringify( body ) : undefined,
		} ).then( function ( res ) {
			return res.json().then( function ( json ) {
				if ( ! res.ok ) {
					throw new Error( json.message || 'Something went wrong. Please try again.' );
				}
				return json;
			} );
		} );
	}

	/* Steps ---------------------------------------------------------------------*/

	function showStep( step ) {
		modal.querySelectorAll( '[data-step-view]' ).forEach( function ( view ) {
			view.classList.toggle( 'is-active', view.getAttribute( 'data-step-view' ) === step );
		} );
	}

	/* Passenger forms -----------------------------------------------------------*/

	function passengerGroup( type, index ) {
		var label = ( 'adult' === type ? 'Adult ' : 'Child ' ) + ( index + 1 );
		var wrap = document.createElement( 'div' );
		wrap.className = 'nx-passenger-group';
		wrap.setAttribute( 'data-passenger-type', type );
		wrap.style.cssText = 'border:1px solid var(--nx-border-2);border-radius:14px;padding:16px;margin-bottom:12px';
		wrap.innerHTML =
			'<div style="font-weight:700;margin-bottom:10px">' + label + '</div>' +
			'<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">' +
			'<input class="nx-input" style="margin-bottom:0" data-field="first_name" placeholder="First name">' +
			'<input class="nx-input" style="margin-bottom:0" data-field="last_name" placeholder="Last name">' +
			'<input class="nx-input" style="margin-bottom:0" data-field="nationality" placeholder="Nationality">' +
			'<input class="nx-input" style="margin-bottom:0" data-field="date_of_birth" type="date" placeholder="Date of birth">' +
			'<input class="nx-input" style="margin-bottom:0" data-field="passport_number" placeholder="Passport number">' +
			'<input class="nx-input" style="margin-bottom:0" data-field="passport_expiry" type="date" placeholder="Passport expiry">' +
			'</div>' +
			'<select class="nx-input" style="margin-bottom:0" data-field="gender"><option value="">Gender</option><option value="female">Female</option><option value="male">Male</option><option value="prefer_not_to_say">Prefer not to say</option></select>';
		return wrap;
	}

	function renderPassengerForms() {
		var existing = {};
		els.passengerForms.querySelectorAll( '.nx-passenger-group' ).forEach( function ( group, i ) {
			var data = {};
			group.querySelectorAll( '[data-field]' ).forEach( function ( input ) {
				data[ input.getAttribute( 'data-field' ) ] = input.value;
			} );
			existing[ group.getAttribute( 'data-passenger-type' ) + '-' + i ] = data;
		} );

		els.passengerForms.innerHTML = '';
		var adultIndex = 0;
		var childIndex = 0;
		for ( var i = 0; i < state.adults + state.children; i++ ) {
			var type = i < state.adults ? 'adult' : 'child';
			var index = 'adult' === type ? adultIndex++ : childIndex++;
			var group = passengerGroup( type, index );
			var saved = existing[ type + '-' + i ];
			if ( saved ) {
				group.querySelectorAll( '[data-field]' ).forEach( function ( input ) {
					if ( saved[ input.getAttribute( 'data-field' ) ] ) {
						input.value = saved[ input.getAttribute( 'data-field' ) ];
					}
				} );
			}
			els.passengerForms.appendChild( group );
		}
	}

	function collectPassengers() {
		var passengers = [];
		els.passengerForms.querySelectorAll( '.nx-passenger-group' ).forEach( function ( group ) {
			var passenger = { passenger_type: group.getAttribute( 'data-passenger-type' ) };
			group.querySelectorAll( '[data-field]' ).forEach( function ( input ) {
				passenger[ input.getAttribute( 'data-field' ) ] = input.value;
			} );
			passengers.push( passenger );
		} );
		return passengers;
	}

	function passengersComplete() {
		var required = [ 'first_name', 'last_name', 'nationality', 'date_of_birth', 'passport_number' ];
		return collectPassengers().every( function ( passenger ) {
			return required.every( function ( field ) {
				return passenger[ field ] && '' !== passenger[ field ].trim();
			} );
		} );
	}

	/* Counters --------------------------------------------------------------*/

	function setCounter( type, dir ) {
		if ( 'adults' === type ) {
			state.adults = Math.max( 1, Math.min( 20, state.adults + dir ) );
			els.adultsValue.textContent = state.adults;
		} else {
			state.children = Math.max( 0, Math.min( 20, state.children + dir ) );
			els.childrenValue.textContent = state.children;
		}
		renderPassengerForms();
		recalcPrice();
		validateForm();
	}

	modal.querySelectorAll( '[data-counter]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			setCounter( btn.getAttribute( 'data-counter' ), parseInt( btn.getAttribute( 'data-dir' ), 10 ) );
		} );
	} );

	/* Availability ------------------------------------------------------------*/

	els.tripSelect.addEventListener( 'change', function () {
		els.slotSelect.innerHTML = '<option value="">Loading departures…</option>';
		els.slotSelect.disabled = true;
		recalcPrice();
		validateForm();

		var excursionId = els.tripSelect.value;
		if ( ! excursionId ) {
			els.slotSelect.innerHTML = '<option value="">Select an excursion first…</option>';
			return;
		}

		api( '/excursions/' + excursionId + '/availability', 'GET' )
			.then( function ( data ) {
				var slots = data.slots || [];
				if ( ! slots.length ) {
					els.slotSelect.innerHTML = '<option value="">No departures available</option>';
					return;
				}
				els.slotSelect.innerHTML = '<option value="">Select a departure…</option>' + slots.map( function ( slot ) {
					var date = new Date( slot.departure_start.replace( ' ', 'T' ) );
					var label = date.toLocaleString( undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' } );
					return '<option value="' + slot.id + '">' + label + ' — ' + slot.remaining_capacity + ' spots left</option>';
				} ).join( '' );
				els.slotSelect.disabled = false;
			} )
			.catch( function () {
				els.slotSelect.innerHTML = '<option value="">Couldn’t load departures — try again</option>';
			} );
	} );

	els.slotSelect.addEventListener( 'change', function () {
		recalcPrice();
		validateForm();
	} );

	/* Pricing -------------------------------------------------------------------*/

	function recalcPrice() {
		var excursionId = els.tripSelect.value;
		var slotId = els.slotSelect.value;
		if ( ! excursionId || ! slotId ) {
			els.totalBreakdown.textContent = 'Select an excursion and departure';
			els.totalValue.textContent = '—';
			els.payCtaTotal.textContent = '—';
			state.lastPrice = null;
			return;
		}

		api( '/price', 'POST', { excursion_id: Number( excursionId ), slot_id: Number( slotId ), passengers: collectPassengers() } )
			.then( function ( price ) {
				state.lastPrice = price;
				var parts = [ price.adult_count + ' adult' + ( price.adult_count === 1 ? '' : 's' ) + ' × $' + price.adult_price ];
				if ( price.child_count > 0 ) {
					parts.push( price.child_count + ' child' + ( price.child_count === 1 ? '' : 'ren' ) + ' × $' + price.child_price );
				}
				els.totalBreakdown.textContent = parts.join( '   +   ' );
				els.totalValue.textContent = '$' + price.total_usd.toFixed( 2 );
				els.payCtaTotal.textContent = '$' + price.total_usd.toFixed( 2 );
			} )
			.catch( function () {
				els.totalBreakdown.textContent = 'Couldn’t calculate price';
			} );
	}

	[ els.contactName, els.contactPhone, els.contactCountry, els.contactHotel, els.contactRoom, els.contactPickup, els.contactNotes, els.terms ].forEach( function ( el ) {
		el.addEventListener( 'input', validateForm );
		el.addEventListener( 'change', validateForm );
	} );
	els.passengerForms.addEventListener( 'input', function () {
		recalcPrice();
		validateForm();
	} );

	/* Validation --------------------------------------------------------------*/

	function validateForm() {
		var valid = !! els.tripSelect.value &&
			!! els.slotSelect.value &&
			!! state.lastPrice &&
			passengersComplete() &&
			els.contactName.value.trim() !== '' &&
			els.contactPhone.value.trim() !== '' &&
			els.contactCountry.value.trim() !== '' &&
			els.contactPickup.value.trim() !== '' &&
			els.terms.checked;
		els.payBtn.disabled = ! valid;
		return valid;
	}

	/* Submit --------------------------------------------------------------------*/

	els.payBtn.addEventListener( 'click', function () {
		if ( ! validateForm() ) {
			return;
		}
		els.formMessage.textContent = '';
		els.payBtn.disabled = true;

		var payload = {
			excursion_id: Number( els.tripSelect.value ),
			slot_id: Number( els.slotSelect.value ),
			passengers: collectPassengers(),
			contact: {
				full_name: els.contactName.value,
				phone: els.contactPhone.value,
				country: els.contactCountry.value,
				hotel: els.contactHotel.value,
				room: els.contactRoom.value,
				pickup_location: els.contactPickup.value,
				special_requests: els.contactNotes.value,
			},
		};

		showStep( 'redirecting' );

		api( '/booking/create-payment-session', 'POST', payload )
			.then( function ( result ) {
				var checkoutUrl = result.payment_session && result.payment_session.checkout_url;
				if ( checkoutUrl ) {
					window.location.href = checkoutUrl;
					return;
				}
				showStep( 'details' );
				els.payBtn.disabled = false;
				els.formMessage.textContent = 'Your booking was created (ref ' + result.booking.booking_ref + '), but online payment isn’t configured yet. Please contact us to complete it.';
			} )
			.catch( function ( err ) {
				showStep( 'details' );
				els.payBtn.disabled = false;
				els.formMessage.textContent = err.message;
			} );
	} );

	function currentUrlWithRedirect( baseUrl ) {
		var url = new URL( baseUrl, window.location.origin );
		url.searchParams.set( 'redirect_to', window.location.href );
		return url.toString();
	}

	function openBooking( tripId ) {
		if ( ! cfg.loggedIn ) {
			els.gateLogin.setAttribute( 'href', currentUrlWithRedirect( cfg.loginUrl ) );
			els.gateRegister.setAttribute( 'href', currentUrlWithRedirect( cfg.registerUrl ) );
			showStep( 'gate' );
		} else {
			if ( tripId ) {
				els.tripSelect.value = String( tripId );
				els.tripSelect.dispatchEvent( new Event( 'change' ) );
			}
			showStep( 'details' );
		}
		modal.classList.add( 'is-open' );
		document.body.style.overflow = 'hidden';
	}

	renderPassengerForms();

	if ( cfg.contact ) {
		if ( cfg.contact.full_name ) {
			els.contactName.value = cfg.contact.full_name;
		}
		if ( cfg.contact.phone ) {
			els.contactPhone.value = cfg.contact.phone;
		}
	}
} )();
