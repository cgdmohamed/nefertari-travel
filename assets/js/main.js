/**
 * Site-wide interactions outside the booking modal: excursion gallery
 * thumbnail switching. Nav scrolling and the itinerary accordion are plain
 * anchors / <details> elements and need no JS.
 */
( function () {
	'use strict';

	var mainImg = document.querySelector( '.nx-gallery-main img' );
	var thumbs = document.querySelectorAll( '.nx-thumb' );

	if ( ! mainImg || ! thumbs.length ) {
		return;
	}

	thumbs.forEach( function ( thumb ) {
		thumb.addEventListener( 'click', function () {
			mainImg.setAttribute( 'src', thumb.getAttribute( 'src' ) );
			thumbs.forEach( function ( t ) {
				t.classList.remove( 'is-active' );
			} );
			thumb.classList.add( 'is-active' );
		} );
	} );
} )();
