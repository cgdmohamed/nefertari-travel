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
		tripNext: document.getElementById( 'nx-trip-next' ),
		travelersNext: document.getElementById( 'nx-travelers-next' ),
		tripField: document.getElementById( 'nx-trip-field' ),
		tripLockedName: document.getElementById( 'nx-trip-locked-name' ),
		paymentMethods: document.getElementById( 'nx-payment-methods' ),
		slotCalendar: document.getElementById( 'nx-slot-calendar' ),
	};

	var state = { adults: 2, children: 0, lastPrice: null, paymentMethod: 'kashier' };

	if ( els.paymentMethods ) {
		els.paymentMethods.querySelectorAll( 'input[name="nx-payment-method"]' ).forEach( function ( input ) {
			input.addEventListener( 'change', function () {
				state.paymentMethod = input.value;
				els.paymentMethods.querySelectorAll( '.nx-payment-method' ).forEach( function ( label ) {
					label.classList.toggle( 'is-active', label.querySelector( 'input' ).checked );
				} );
			} );
		} );
	}

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

	/* Sub-steps (trip -> travelers -> contact, within the "details" step) -------*/

	var subSteps = [ 'trip', 'travelers', 'contact' ];

	function showSubStep( name ) {
		modal.querySelectorAll( '[data-sub-step]' ).forEach( function ( view ) {
			view.classList.toggle( 'is-active', view.getAttribute( 'data-sub-step' ) === name );
		} );
		var currentIndex = subSteps.indexOf( name );
		modal.querySelectorAll( '[data-progress-step]' ).forEach( function ( step ) {
			var stepIndex = subSteps.indexOf( step.getAttribute( 'data-progress-step' ) );
			step.classList.toggle( 'is-active', stepIndex === currentIndex );
			step.classList.toggle( 'is-done', stepIndex < currentIndex );
		} );
		modal.querySelector( '.nx-modal-body' ).scrollTop = 0;
	}

	modal.querySelectorAll( '[data-next-step]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var current = btn.closest( '[data-sub-step]' ).getAttribute( 'data-sub-step' );
			var next = subSteps[ subSteps.indexOf( current ) + 1 ];
			if ( next ) {
				showSubStep( next );
			}
		} );
	} );

	modal.querySelectorAll( '[data-prev-step]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var current = btn.closest( '[data-sub-step]' ).getAttribute( 'data-sub-step' );
			var prev = subSteps[ subSteps.indexOf( current ) - 1 ];
			if ( prev ) {
				showSubStep( prev );
			}
		} );
	} );

	/* Passenger forms -----------------------------------------------------------*/

	/* Per-traveler details (passport, DOB, nationality) are optional at booking
	 * time — a guest can send them later, including over WhatsApp. Only the
	 * single contact/invoicing form on the next step is required, so nothing
	 * here gates progression; passengerHasData() just drives the "Added ✓"
	 * status pill on each traveler's collapsed row. */

	function passengerGroup( type, index ) {
		var label = ( 'adult' === type ? 'Adult ' : 'Child ' ) + ( index + 1 );
		var wrap = document.createElement( 'div' );
		wrap.className = 'nx-passenger-group';
		wrap.setAttribute( 'data-passenger-type', type );
		wrap.innerHTML =
			'<button type="button" class="nx-passenger-summary" data-toggle-passenger>' +
			'<span class="nx-passenger-summary-title">' + label + '</span>' +
			'<span class="nx-passenger-summary-status" data-passenger-status>Optional — tap to add</span>' +
			'<span class="nx-passenger-chevron">›</span>' +
			'</button>' +
			'<div class="nx-passenger-fields">' +
			'<div class="nx-passenger-fields-grid">' +
			'<input class="nx-input" data-field="first_name" placeholder="First name">' +
			'<input class="nx-input" data-field="last_name" placeholder="Last name">' +
			'<input class="nx-input" data-field="nationality" placeholder="Nationality">' +
			'<input class="nx-input" data-field="date_of_birth" type="date" placeholder="Date of birth">' +
			'<input class="nx-input" data-field="passport_number" placeholder="Passport number">' +
			'<input class="nx-input" data-field="passport_expiry" type="date" placeholder="Passport expiry">' +
			'</div>' +
			'<select class="nx-input" data-field="gender"><option value="">Gender</option><option value="female">Female</option><option value="male">Male</option><option value="prefer_not_to_say">Prefer not to say</option></select>' +
			'</div>';
		return wrap;
	}

	function passengerHasData( group ) {
		return Array.prototype.some.call( group.querySelectorAll( '[data-field]' ), function ( input ) {
			return '' !== input.value.trim();
		} );
	}

	function updatePassengerStatuses() {
		els.passengerForms.querySelectorAll( '.nx-passenger-group' ).forEach( function ( group ) {
			var hasData = passengerHasData( group );
			group.classList.toggle( 'is-complete', hasData );
			group.querySelector( '[data-passenger-status]' ).textContent = hasData ? 'Added ✓' : 'Optional — tap to add';
		} );
		els.travelersNext.disabled = false;
	}

	els.passengerForms.addEventListener( 'click', function ( e ) {
		var toggle = e.target.closest( '[data-toggle-passenger]' );
		if ( ! toggle ) {
			return;
		}
		toggle.closest( '.nx-passenger-group' ).classList.toggle( 'is-expanded' );
	} );

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

		updatePassengerStatuses();
		var groups = els.passengerForms.querySelectorAll( '.nx-passenger-group' );
		var firstIncomplete = Array.prototype.filter.call( groups, function ( group ) {
			return ! group.classList.contains( 'is-complete' );
		} )[ 0 ];
		if ( firstIncomplete ) {
			firstIncomplete.classList.add( 'is-expanded' );
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

	/* Trip/guests step gating ----------------------------------------------------*/

	function updateTripNextState() {
		els.tripNext.disabled = ! ( els.tripSelect.value && els.slotSelect.value && state.lastPrice );
	}

	/* Locking the excursion picker when booking is opened from an excursion's
	 * own page — the visitor already told us which trip they want, so making
	 * them pick it again from a dropdown is an unnecessary extra step. */

	function lockTripSelection( tripId ) {
		var option = els.tripSelect.querySelector( 'option[value="' + tripId + '"]' );
		els.tripLockedName.textContent = option ? option.textContent.trim() : '';
		els.tripField.classList.add( 'is-locked' );
	}

	function unlockTripSelection() {
		els.tripField.classList.remove( 'is-locked' );
	}

	els.tripField.querySelector( '[data-unlock-trip]' ).addEventListener( 'click', unlockTripSelection );

	modal.querySelectorAll( '[data-counter]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			setCounter( btn.getAttribute( 'data-counter' ), parseInt( btn.getAttribute( 'data-dir' ), 10 ) );
		} );
	} );

	/* Departure calendar ----------------------------------------------------
	 * els.slotSelect stays the single source of truth for the selected slot
	 * (its <option>s carry the id -> everything else, pricing/validation,
	 * already reads its .value and listens for its "change" event) — it's
	 * just hidden now. The calendar below is a second, friendlier view onto
	 * the same options, and only ever writes to the select, never reads
	 * from it, so there's one direction of truth. ------------------------*/

	var calendarState = { byDay: {}, viewYear: 0, viewMonth: 0, selectedDay: '' };

	function dayKey( date ) {
		return date.getFullYear() + '-' + String( date.getMonth() + 1 ).padStart( 2, '0' ) + '-' + String( date.getDate() ).padStart( 2, '0' );
	}

	function selectSlot( slotId ) {
		els.slotSelect.value = slotId;
		els.slotSelect.dispatchEvent( new Event( 'change' ) );
	}

	function renderSlotTimes( key ) {
		var timesEl = document.getElementById( 'nx-slot-times' );
		if ( ! timesEl ) {
			return;
		}
		var daySlots = ( calendarState.byDay[ key ] || [] ).slice().sort( function ( a, b ) {
			return a.date - b.date;
		} );
		timesEl.innerHTML = daySlots.map( function ( slot ) {
			var label = slot.date.toLocaleString( undefined, { hour: 'numeric', minute: '2-digit' } );
			return '<button type="button" class="nx-slot-time-btn" data-slot-id="' + slot.id + '">' + label + ' · ' + slot.remaining + ' left</button>';
		} ).join( '' );
		timesEl.querySelectorAll( '[data-slot-id]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				selectSlot( btn.getAttribute( 'data-slot-id' ) );
				timesEl.querySelectorAll( '.nx-slot-time-btn' ).forEach( function ( b ) {
					b.classList.toggle( 'is-selected', b === btn );
				} );
			} );
		} );
	}

	function selectCalendarDay( key ) {
		calendarState.selectedDay = key;
		els.slotCalendar.querySelectorAll( '[data-cal-day]' ).forEach( function ( btn ) {
			btn.classList.toggle( 'is-selected', btn.getAttribute( 'data-cal-day' ) === key );
		} );
		renderSlotTimes( key );

		var daySlots = calendarState.byDay[ key ] || [];
		selectSlot( 1 === daySlots.length ? daySlots[ 0 ].id : '' );
	}

	function drawCalendarMonth() {
		var byDay = calendarState.byDay;
		var year = calendarState.viewYear;
		var month = calendarState.viewMonth;
		var monthStart = new Date( year, month, 1 );
		var monthEnd = new Date( year, month + 1, 0 );
		var daysInMonth = monthEnd.getDate();
		var startWeekday = ( monthStart.getDay() + 6 ) % 7; // Monday-first

		var dayKeys = Object.keys( byDay );
		var hasEarlier = dayKeys.some( function ( key ) { return key < dayKey( monthStart ); } );
		var hasLater = dayKeys.some( function ( key ) { return key > dayKey( monthEnd ); } );

		var html = '<div class="nx-calendar-head">' +
			'<button type="button" class="nx-calendar-nav" data-cal-prev' + ( hasEarlier ? '' : ' disabled' ) + '>‹</button>' +
			'<span class="nx-calendar-title">' + monthStart.toLocaleString( undefined, { month: 'long', year: 'numeric' } ) + '</span>' +
			'<button type="button" class="nx-calendar-nav" data-cal-next' + ( hasLater ? '' : ' disabled' ) + '>›</button>' +
			'</div><div class="nx-calendar-grid">';

		[ 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su' ].forEach( function ( label ) {
			html += '<div class="nx-calendar-dow">' + label + '</div>';
		} );
		for ( var i = 0; i < startWeekday; i++ ) {
			html += '<div class="nx-calendar-day"></div>';
		}
		for ( var day = 1; day <= daysInMonth; day++ ) {
			var key = year + '-' + String( month + 1 ).padStart( 2, '0' ) + '-' + String( day ).padStart( 2, '0' );
			var hasSlots = !! byDay[ key ];
			var classes = 'nx-calendar-day' + ( hasSlots ? ' has-slots' : '' ) + ( key === calendarState.selectedDay ? ' is-selected' : '' );
			html += '<button type="button" class="' + classes + '"' + ( hasSlots ? ' data-cal-day="' + key + '"' : ' disabled' ) + '>' + day + '</button>';
		}
		html += '</div><div class="nx-slot-times" id="nx-slot-times"></div>';

		els.slotCalendar.innerHTML = html;

		var prevBtn = els.slotCalendar.querySelector( '[data-cal-prev]' );
		var nextBtn = els.slotCalendar.querySelector( '[data-cal-next]' );
		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () {
				calendarState.viewMonth--;
				if ( calendarState.viewMonth < 0 ) {
					calendarState.viewMonth = 11;
					calendarState.viewYear--;
				}
				drawCalendarMonth();
			} );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () {
				calendarState.viewMonth++;
				if ( calendarState.viewMonth > 11 ) {
					calendarState.viewMonth = 0;
					calendarState.viewYear++;
				}
				drawCalendarMonth();
			} );
		}
		els.slotCalendar.querySelectorAll( '[data-cal-day]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				selectCalendarDay( btn.getAttribute( 'data-cal-day' ) );
			} );
		} );

		if ( calendarState.selectedDay ) {
			renderSlotTimes( calendarState.selectedDay );
		}
	}

	function renderSlotCalendar( slots, emptyMessage ) {
		if ( ! slots || ! slots.length ) {
			els.slotCalendar.innerHTML = '<p class="nx-calendar-empty">' + emptyMessage + '</p>';
			calendarState = { byDay: {}, viewYear: 0, viewMonth: 0, selectedDay: '' };
			return;
		}

		var byDay = {};
		slots.forEach( function ( slot ) {
			var date = new Date( slot.departure_start.replace( ' ', 'T' ) );
			var key = dayKey( date );
			( byDay[ key ] = byDay[ key ] || [] ).push( { id: slot.id, date: date, remaining: slot.remaining_capacity } );
		} );

		var firstDate = new Date( Object.keys( byDay ).sort()[ 0 ] + 'T00:00:00' );
		calendarState = { byDay: byDay, viewYear: firstDate.getFullYear(), viewMonth: firstDate.getMonth(), selectedDay: '' };
		drawCalendarMonth();
	}

	/* Availability ------------------------------------------------------------*/

	els.tripSelect.addEventListener( 'change', function () {
		els.slotSelect.innerHTML = '<option value="">Loading departures…</option>';
		els.slotSelect.disabled = true;
		renderSlotCalendar( [], 'Loading departures…' );
		recalcPrice();
		validateForm();
		updateTripNextState();

		var excursionId = els.tripSelect.value;
		if ( ! excursionId ) {
			els.slotSelect.innerHTML = '<option value="">Select an excursion first…</option>';
			renderSlotCalendar( [], 'Select an excursion first…' );
			return;
		}

		api( '/excursions/' + excursionId + '/availability', 'GET' )
			.then( function ( data ) {
				var slots = data.slots || [];
				els.slotSelect.innerHTML = '<option value="">Select a departure…</option>' + slots.map( function ( slot ) {
					var date = new Date( slot.departure_start.replace( ' ', 'T' ) );
					var label = date.toLocaleString( undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' } );
					return '<option value="' + slot.id + '">' + label + ' — ' + slot.remaining_capacity + ' spots left</option>';
				} ).join( '' );
				els.slotSelect.disabled = false;
				renderSlotCalendar( slots, 'No departures scheduled yet — contact us to check availability.' );
			} )
			.catch( function () {
				els.slotSelect.innerHTML = '<option value="">Couldn’t load departures — try again</option>';
				renderSlotCalendar( [], 'Couldn’t load departures — try again.' );
			} );
	} );

	els.slotSelect.addEventListener( 'change', function () {
		recalcPrice();
		validateForm();
		updateTripNextState();
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
				updateTripNextState();
			} )
			.catch( function () {
				els.totalBreakdown.textContent = 'Couldn’t calculate price';
				updateTripNextState();
			} );
	}

	[ els.contactName, els.contactPhone, els.contactCountry, els.contactHotel, els.contactRoom, els.contactPickup, els.contactNotes, els.terms ].forEach( function ( el ) {
		el.addEventListener( 'input', validateForm );
		el.addEventListener( 'change', validateForm );
	} );
	els.passengerForms.addEventListener( 'input', function () {
		recalcPrice();
		validateForm();
		updatePassengerStatuses();
	} );

	/* Validation --------------------------------------------------------------*/

	function validateForm() {
		var valid = !! els.tripSelect.value &&
			!! els.slotSelect.value &&
			!! state.lastPrice &&
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
			payment_method: state.paymentMethod,
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
				lockTripSelection( tripId );
			} else {
				unlockTripSelection();
			}
			showStep( 'details' );
			showSubStep( 'trip' );
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
