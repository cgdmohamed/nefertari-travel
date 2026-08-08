/**
 * Site-wide interactions outside the booking modal: the excursion gallery
 * (a small self-built auto-sliding slider, synced to the thumbnail strip
 * below it — no third-party library, so there's no risk of colliding with
 * another plugin's own bundled slider on a real WordPress site). Nav
 * scrolling and the itinerary accordion are plain anchors / <details>
 * elements and need no JS.
 */
( function () {
	'use strict';

	var wrap = document.getElementById( 'nx-gallery' );
	var track = wrap ? wrap.querySelector( '.nx-slider-track' ) : null;
	if ( ! wrap || ! track ) {
		return;
	}

	var slides = track.querySelectorAll( '.nx-slide' );
	var thumbs = document.querySelectorAll( '.nx-thumb' );
	var dotsWrap = wrap.querySelector( '.nx-gallery-dots' );
	var prevBtn = wrap.querySelector( '.nx-gallery-nav--prev' );
	var nextBtn = wrap.querySelector( '.nx-gallery-nav--next' );

	if ( slides.length < 2 ) {
		return;
	}

	var index = 0;
	var timer = null;
	var dots = [];

	if ( dotsWrap ) {
		slides.forEach( function ( slide, i ) {
			var dot = document.createElement( 'button' );
			dot.type = 'button';
			dot.className = 'nx-gallery-dot';
			dot.setAttribute( 'aria-label', 'Go to image ' + ( i + 1 ) );
			dot.addEventListener( 'click', function () {
				goTo( i );
				restartAutoplay();
			} );
			dotsWrap.appendChild( dot );
			dots.push( dot );
		} );
	}

	function goTo( i ) {
		index = ( i + slides.length ) % slides.length;
		track.style.transform = 'translateX(-' + ( index * 100 ) + '%)';
		thumbs.forEach( function ( thumb, t ) {
			thumb.classList.toggle( 'is-active', t === index );
		} );
		dots.forEach( function ( dot, d ) {
			dot.classList.toggle( 'is-active', d === index );
		} );
	}

	function startAutoplay() {
		timer = setInterval( function () {
			goTo( index + 1 );
		}, 4000 );
	}

	function stopAutoplay() {
		if ( timer ) {
			clearInterval( timer );
			timer = null;
		}
	}

	function restartAutoplay() {
		stopAutoplay();
		startAutoplay();
	}

	if ( nextBtn ) {
		nextBtn.addEventListener( 'click', function () {
			goTo( index + 1 );
			restartAutoplay();
		} );
	}
	if ( prevBtn ) {
		prevBtn.addEventListener( 'click', function () {
			goTo( index - 1 );
			restartAutoplay();
		} );
	}
	thumbs.forEach( function ( thumb, i ) {
		thumb.addEventListener( 'click', function () {
			goTo( i );
			restartAutoplay();
		} );
	} );

	wrap.addEventListener( 'mouseenter', stopAutoplay );
	wrap.addEventListener( 'mouseleave', startAutoplay );

	goTo( 0 );
	startAutoplay();
} )();
