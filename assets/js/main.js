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

	var swiper = new Swiper( el, {
		loop: multiSlide,
		autoplay: multiSlide ? { delay: 4000, disableOnInteraction: false, pauseOnMouseEnter: true } : false,
		pagination: multiSlide ? { el: '.swiper-pagination', clickable: true } : false,
		navigation: multiSlide ? { nextEl: '.nx-gallery-nav--next', prevEl: '.nx-gallery-nav--prev' } : false,
		on: {
			slideChange: function () {
				var active = swiper.realIndex;
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
} )();
