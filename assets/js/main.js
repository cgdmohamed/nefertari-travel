/**
 * Site-wide interactions outside the booking modal: the excursion gallery
 * (a small self-built auto-sliding slider, synced to the thumbnail strip
 * below it — no third-party library, so there's no risk of colliding with
 * another plugin's own bundled slider on a real WordPress site), its
 * click-to-zoom lightbox, and touch swipe support on both. Nav scrolling
 * and the itinerary accordion are plain anchors / <details> elements and
 * need no JS.
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

	var images = Array.prototype.map.call( slides, function ( slide ) {
		var img = slide.querySelector( 'img' );
		return { src: img.getAttribute( 'src' ), alt: img.getAttribute( 'alt' ) || '' };
	} );

	var index = 0;
	var timer = null;
	var dots = [];

	if ( dotsWrap && slides.length > 1 ) {
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
		if ( thumbs[ index ] ) {
			thumbs[ index ].scrollIntoView( { behavior: 'smooth', inline: 'center', block: 'nearest' } );
		}
		dots.forEach( function ( dot, d ) {
			dot.classList.toggle( 'is-active', d === index );
		} );
		if ( lightbox && lightbox.classList.contains( 'is-open' ) ) {
			renderLightbox();
		}
	}

	function startAutoplay() {
		if ( slides.length < 2 ) {
			return;
		}
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

	/* Swipe (main slider) --------------------------------------------------*/

	bindSwipe( wrap, function ( direction ) {
		goTo( index + direction );
		restartAutoplay();
	} );

	/* Lightbox --------------------------------------------------------------*/

	var lightbox = document.getElementById( 'nx-lightbox' );
	var lightboxImg = lightbox ? document.getElementById( 'nx-lightbox-img' ) : null;
	var lightboxCounter = lightbox ? document.getElementById( 'nx-lightbox-counter' ) : null;

	function renderLightbox() {
		lightboxImg.setAttribute( 'src', images[ index ].src );
		lightboxImg.setAttribute( 'alt', images[ index ].alt );
		lightboxCounter.textContent = images.length > 1 ? ( index + 1 ) + ' / ' + images.length : '';
	}

	function openLightbox( i ) {
		if ( ! lightbox ) {
			return;
		}
		goTo( i );
		renderLightbox();
		lightbox.classList.add( 'is-open' );
		document.body.style.overflow = 'hidden';
		stopAutoplay();
	}

	function closeLightbox() {
		lightbox.classList.remove( 'is-open' );
		document.body.style.overflow = '';
		startAutoplay();
	}

	if ( lightbox ) {
		track.querySelectorAll( '.nx-slide img' ).forEach( function ( img, i ) {
			img.style.cursor = 'zoom-in';
			img.addEventListener( 'click', function () {
				openLightbox( i );
			} );
		} );

		lightbox.querySelectorAll( '[data-lightbox-close]' ).forEach( function ( el ) {
			el.addEventListener( 'click', closeLightbox );
		} );
		lightbox.addEventListener( 'click', function ( e ) {
			if ( e.target === lightbox ) {
				closeLightbox();
			}
		} );
		var lightboxPrev = lightbox.querySelector( '[data-lightbox-prev]' );
		var lightboxNext = lightbox.querySelector( '[data-lightbox-next]' );
		if ( lightboxPrev ) {
			lightboxPrev.addEventListener( 'click', function () {
				goTo( index - 1 );
			} );
		}
		if ( lightboxNext ) {
			lightboxNext.addEventListener( 'click', function () {
				goTo( index + 1 );
			} );
		}
		if ( slides.length < 2 && lightboxPrev && lightboxNext ) {
			lightboxPrev.style.display = 'none';
			lightboxNext.style.display = 'none';
		}

		document.addEventListener( 'keydown', function ( e ) {
			if ( ! lightbox.classList.contains( 'is-open' ) ) {
				return;
			}
			if ( 'Escape' === e.key ) {
				closeLightbox();
			} else if ( 'ArrowLeft' === e.key ) {
				goTo( index - 1 );
			} else if ( 'ArrowRight' === e.key ) {
				goTo( index + 1 );
			}
		} );

		bindSwipe( lightbox, function ( direction ) {
			goTo( index + direction );
		} );
	}

	/**
	 * Minimal touch-swipe: horizontal drags past a threshold navigate;
	 * anything more vertical than horizontal is left alone so it doesn't
	 * fight the page's own scrolling.
	 */
	function bindSwipe( el, onSwipe ) {
		var startX = 0;
		var startY = 0;
		el.addEventListener( 'touchstart', function ( e ) {
			startX = e.touches[ 0 ].clientX;
			startY = e.touches[ 0 ].clientY;
		}, { passive: true } );
		el.addEventListener( 'touchend', function ( e ) {
			var dx = e.changedTouches[ 0 ].clientX - startX;
			var dy = e.changedTouches[ 0 ].clientY - startY;
			if ( Math.abs( dx ) > 40 && Math.abs( dx ) > Math.abs( dy ) ) {
				onSwipe( dx < 0 ? 1 : -1 );
			}
		}, { passive: true } );
	}

	goTo( 0 );
	startAutoplay();
} )();

/**
 * Review modal on the account page — one shared dialog triggered by any
 * "Review" button (booking list rows, booking detail header), scoped to
 * whichever excursion the trigger's data attributes name. Reopens itself
 * on page load if the server redirected back with a review_error (the
 * excursion id survives that redirect as a query arg).
 */
( function () {
	'use strict';

	var modal = document.getElementById( 'nx-review-modal' );
	if ( ! modal ) {
		return;
	}

	var titleEl = document.getElementById( 'nx-review-modal-excursion' );
	var idInput = document.getElementById( 'nx-review-excursion-id' );
	var fileInput = document.getElementById( 'review-images' );
	var preview = document.getElementById( 'nx-review-upload-preview' );
	var reviewable = {};
	try {
		reviewable = JSON.parse( modal.getAttribute( 'data-reviewable' ) || '{}' );
	} catch ( e ) {
		reviewable = {};
	}

	function openModal( excursionId, excursionTitle ) {
		idInput.value = excursionId;
		titleEl.textContent = excursionTitle || 'Leave a review';
		modal.classList.add( 'is-open' );
		document.body.style.overflow = 'hidden';
	}

	function closeModal() {
		modal.classList.remove( 'is-open' );
		document.body.style.overflow = '';
	}

	Array.prototype.forEach.call( document.querySelectorAll( '[data-review-trigger]' ), function ( btn ) {
		btn.addEventListener( 'click', function () {
			openModal( btn.getAttribute( 'data-review-excursion' ), btn.getAttribute( 'data-review-title' ) );
		} );
	} );

	Array.prototype.forEach.call( modal.querySelectorAll( '[data-review-close]' ), function ( el ) {
		el.addEventListener( 'click', closeModal );
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && modal.classList.contains( 'is-open' ) ) {
			closeModal();
		}
	} );

	if ( fileInput && preview ) {
		fileInput.addEventListener( 'change', function () {
			preview.innerHTML = '';
			Array.prototype.slice.call( fileInput.files, 0, 6 ).forEach( function ( file ) {
				var img = document.createElement( 'img' );
				img.src = URL.createObjectURL( file );
				preview.appendChild( img );
			} );
		} );
	}

	var params = new URLSearchParams( window.location.search );
	if ( params.has( 'review_error' ) || params.has( 'review_submitted' ) ) {
		var excursionId = params.get( 'excursion_id' ) || '';
		openModal( excursionId, reviewable[ excursionId ] || '' );
	}
} )();
