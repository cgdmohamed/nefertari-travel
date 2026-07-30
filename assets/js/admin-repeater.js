/**
 * Admin repeater controls for the Excursion meta boxes.
 * Vanilla JS + a touch of jQuery only for the core wp-color-picker widget.
 */
( function () {
	'use strict';

	function cloneTemplate( template ) {
		return template.content.firstElementChild.cloneNode( true );
	}

	function initSimpleRepeaters( root ) {
		root.querySelectorAll( '.nx-repeater[data-repeater="simple"]' ).forEach( function ( repeater ) {
			var rows = repeater.querySelector( '.nx-repeater-rows' );
			var template = repeater.querySelector( '.nx-row-template' );
			var addBtn = repeater.querySelector( '.nx-add-row' );

			addBtn.addEventListener( 'click', function () {
				rows.appendChild( cloneTemplate( template ) );
			} );

			repeater.addEventListener( 'click', function ( e ) {
				if ( e.target.classList.contains( 'nx-remove-row' ) ) {
					e.target.closest( '.nx-repeater-row' ).remove();
				}
			} );
		} );
	}

	function initItinerary( root ) {
		var wrap = root.querySelector( '.nx-itinerary' );
		if ( ! wrap ) {
			return;
		}
		var daysList = wrap.querySelector( '.nx-itinerary-days' );
		var dayTemplate = wrap.querySelector( '.nx-day-template' );

		function nextDayIndex() {
			var i = parseInt( wrap.getAttribute( 'data-next-day-index' ), 10 ) || 0;
			wrap.setAttribute( 'data-next-day-index', i + 1 );
			return i;
		}

		function nextStepIndex( dayEl ) {
			var i = parseInt( dayEl.getAttribute( 'data-next-step-index' ), 10 ) || 0;
			dayEl.setAttribute( 'data-next-step-index', i + 1 );
			return i;
		}

		function addDay() {
			var dayIndex = nextDayIndex();
			var html = dayTemplate.innerHTML.split( '__DAY__' ).join( dayIndex );
			var tmp = document.createElement( 'template' );
			tmp.innerHTML = html.trim();
			var dayEl = tmp.content.firstElementChild;
			dayEl.setAttribute( 'data-next-step-index', 0 );
			daysList.appendChild( dayEl );
		}

		function addStep( dayEl ) {
			var dayIndex = dayEl.querySelector( '.nx-itinerary-day-head input' ).name.match( /\[(\d+)\]/ )[ 1 ];
			var stepIndex = nextStepIndex( dayEl );
			var stepTemplate = dayEl.querySelector( '.nx-step-template' );
			var html = stepTemplate.innerHTML.split( '__DAY__' ).join( dayIndex ).split( '__STEP__' ).join( stepIndex );
			var tmp = document.createElement( 'template' );
			tmp.innerHTML = html.trim();
			dayEl.querySelector( '.nx-itinerary-steps' ).appendChild( tmp.content.firstElementChild );
		}

		wrap.querySelector( '.nx-add-day' ).addEventListener( 'click', addDay );

		wrap.addEventListener( 'click', function ( e ) {
			if ( e.target.classList.contains( 'nx-add-step' ) ) {
				addStep( e.target.closest( '.nx-itinerary-day' ) );
			} else if ( e.target.classList.contains( 'nx-remove-step' ) ) {
				e.target.closest( '.nx-itinerary-step' ).remove();
			} else if ( e.target.classList.contains( 'nx-remove-day' ) ) {
				e.target.closest( '.nx-itinerary-day' ).remove();
			}
		} );
	}

	function initColorPickers() {
		if ( window.jQuery && window.jQuery.fn.wpColorPicker ) {
			window.jQuery( '.nx-color-field' ).wpColorPicker();
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initSimpleRepeaters( document );
		initItinerary( document );
		initColorPickers();
	} );
} )();
