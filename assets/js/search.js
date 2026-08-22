/**
 * Site-wide search modal — queries WordPress core's own /wp/v2/search
 * endpoint (no custom backend needed; both `post` and `excursion` already
 * have show_in_rest enabled) and renders excursions/articles together,
 * badged by type.
 */
( function () {
	'use strict';

	var modal = document.getElementById( 'nx-search-modal' );
	var trigger = document.getElementById( 'nx-search-trigger' );
	if ( ! modal || ! trigger ) {
		return;
	}

	var input = document.getElementById( 'nx-search-input' );
	var results = document.getElementById( 'nx-search-results' );
	var restBase = ( window.nefertariSearch && window.nefertariSearch.restUrl ) || '/wp-json/';
	var debounceTimer = null;
	var currentRequest = 0;

	function openModal() {
		modal.classList.add( 'is-open' );
		document.body.style.overflow = 'hidden';
		window.setTimeout( function () { input.focus(); }, 50 );
	}

	function closeModal() {
		modal.classList.remove( 'is-open' );
		document.body.style.overflow = '';
	}

	trigger.addEventListener( 'click', openModal );
	modal.querySelectorAll( '[data-search-close]' ).forEach( function ( el ) {
		el.addEventListener( 'click', closeModal );
	} );
	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && modal.classList.contains( 'is-open' ) ) {
			closeModal();
		}
		if ( ( e.metaKey || e.ctrlKey ) && 'k' === e.key.toLowerCase() ) {
			e.preventDefault();
			modal.classList.contains( 'is-open' ) ? closeModal() : openModal();
		}
	} );

	function typeLabel( subtype ) {
		if ( 'excursion' === subtype ) {
			return 'Excursion';
		}
		if ( 'post' === subtype ) {
			return 'Article';
		}
		return subtype ? subtype.charAt( 0 ).toUpperCase() + subtype.slice( 1 ) : '';
	}

	function render( items ) {
		results.innerHTML = '';
		if ( ! items.length ) {
			results.innerHTML = '<p class="nx-search-hint">No matches — try a different word.</p>';
			return;
		}
		items.forEach( function ( item ) {
			var link = document.createElement( 'a' );
			link.className = 'nx-search-result';
			link.href = item.url;

			var badge = document.createElement( 'span' );
			badge.className = 'nx-search-result-badge nx-search-result-badge--' + item.subtype;
			badge.textContent = typeLabel( item.subtype );

			var title = document.createElement( 'span' );
			title.className = 'nx-search-result-title';
			// Title text arrives HTML-entity-encoded from WP (e.g. "Fish &amp; Chips");
			// textContent would render the entities literally, so decode via a
			// detached element rather than risk innerHTML on untrusted content.
			var decode = document.createElement( 'textarea' );
			decode.innerHTML = item.title;
			title.textContent = decode.value;

			link.appendChild( badge );
			link.appendChild( title );
			results.appendChild( link );
		} );
	}

	function search( term ) {
		var requestId = ++currentRequest;
		results.innerHTML = '<p class="nx-search-hint">Searching…</p>';

		fetch( restBase + 'wp/v2/search?search=' + encodeURIComponent( term ) + '&subtype=post,excursion&per_page=8&_fields=id,title,url,subtype' )
			.then( function ( res ) { return res.json(); } )
			.then( function ( items ) {
				if ( requestId !== currentRequest ) {
					return; // a newer keystroke already superseded this request
				}
				render( Array.isArray( items ) ? items : [] );
			} )
			.catch( function () {
				if ( requestId === currentRequest ) {
					results.innerHTML = '<p class="nx-search-hint">Something went wrong — please try again.</p>';
				}
			} );
	}

	input.addEventListener( 'input', function () {
		var term = input.value.trim();
		window.clearTimeout( debounceTimer );
		if ( term.length < 2 ) {
			currentRequest++; // invalidate any in-flight request
			results.innerHTML = '<p class="nx-search-hint">Start typing to search trips and journal articles.</p>';
			return;
		}
		debounceTimer = window.setTimeout( function () { search( term ); }, 300 );
	} );
} )();
