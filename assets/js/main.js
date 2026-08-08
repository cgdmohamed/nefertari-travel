/**
 * Site-wide interactions outside the booking modal: the excursion gallery
 * (an auto-sliding Swiper, synced to the thumbnail strip below it). Nav
 * scrolling and the itinerary accordion are plain anchors / <details>
 * elements and need no JS.
 */
( function () {
	'use strict';

	var el = document.getElementById( 'nx-gallery-swiper' );
	if ( ! el || 'undefined' === typeof Swiper ) {
		return;
	}

	var thumbs = document.querySelectorAll( '.nx-thumb' );
	var multiSlide = thumbs.length > 1;

	// Initializing on window "load" (rather than as soon as this script
	// parses) means the grid layout and web fonts have already settled, so
	// Swiper measures the container's real width instead of a too-narrow
	// mid-layout one. "observer"/"observeParents" additionally make it
	// re-measure automatically if the layout shifts after that.
	window.addEventListener( 'load', function () {
		var swiper = new Swiper( el, {
			loop: multiSlide,
			observer: true,
			observeParents: true,
			autoplay: multiSlide ? { delay: 4000, disableOnInteraction: false, pauseOnMouseEnter: true } : false,
			pagination: multiSlide ? { el: '.swiper-pagination', clickable: true } : false,
			navigation: multiSlide ? { nextEl: '.nx-gallery-nav--next', prevEl: '.nx-gallery-nav--prev' } : false,
			on: {
				// Swiper calls this during its own construction (synchronously,
				// as part of `new Swiper(...)`), before the assignment to the
				// `swiper` variable below has happened — so `this` (which
				// Swiper binds to the instance) must be used instead of that
				// variable.
				slideChange: function () {
					var active = this.realIndex;
					thumbs.forEach( function ( thumb, i ) {
						thumb.classList.toggle( 'is-active', i === active );
					} );
				},
			},
		} );

		thumbs.forEach( function ( thumb, i ) {
			thumb.addEventListener( 'click', function () {
				swiper.slideToLoop( i );
			} );
		} );
	} );
} )();
