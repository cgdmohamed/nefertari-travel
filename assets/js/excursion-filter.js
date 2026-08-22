/**
 * Excursion archive filter bar + pagination — progressively enhances the
 * server-rendered archive-excursion.php: filter changes and pagination
 * clicks re-query via admin-ajax (inc/ajax.php) and swap the grid in
 * place instead of a full page reload, pushing state to the URL so
 * results stay bookmarkable/shareable and back/forward still work.
 */
( function () {
	'use strict';

	var form = document.getElementById( 'nx-excursion-filters' );
	var grid = document.getElementById( 'nx-excursion-grid' );
	var pagination = document.getElementById( 'nx-excursion-pagination' );
	var count = document.getElementById( 'nx-excursion-count' );
	var cfg = window.nefertariExcursionFilter;
	if ( ! form || ! grid || ! pagination || ! cfg ) {
		return;
	}

	function currentFilters( page ) {
		return {
			category: form.category.value,
			destination: form.destination.value,
			min_price: form.min_price.value,
			max_price: form.max_price.value,
			sort: form.sort.value,
			paged: String( page || 1 ),
		};
	}

	function applyFiltersToForm( filters ) {
		form.category.value = filters.category || '';
		form.destination.value = filters.destination || '';
		form.min_price.value = filters.min_price || '';
		form.max_price.value = filters.max_price || '';
		form.sort.value = filters.sort || '';
	}

	function updateUrl( filters ) {
		var params = new URLSearchParams();
		Object.keys( filters ).forEach( function ( key ) {
			var value = filters[ key ];
			if ( value && ! ( 'paged' === key && '1' === value ) ) {
				params.set( key, value );
			}
		} );
		var query = params.toString();
		window.history.pushState( filters, '', window.location.pathname + ( query ? '?' + query : '' ) );
	}

	function fetchResults( filters, pushUrl ) {
		grid.classList.add( 'is-loading' );

		var body = new URLSearchParams( filters );
		body.set( 'action', 'nefertari_filter_excursions' );
		body.set( 'nonce', cfg.nonce );

		fetch( cfg.ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
		} )
			.then( function ( res ) { return res.json(); } )
			.then( function ( json ) {
				if ( ! json.success ) {
					return;
				}
				grid.innerHTML = json.data.html;
				pagination.innerHTML = json.data.pagination;
				if ( count ) {
					count.textContent = json.data.found + ( 1 === json.data.found ? ' excursion found' : ' excursions found' );
				}
				if ( pushUrl ) {
					updateUrl( filters );
				}
				grid.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} )
			.finally( function () {
				grid.classList.remove( 'is-loading' );
			} );
	}

	form.addEventListener( 'change', function () {
		fetchResults( currentFilters( 1 ), true );
	} );

	var resetBtn = document.getElementById( 'nx-filter-reset' );
	if ( resetBtn ) {
		resetBtn.addEventListener( 'click', function () {
			form.reset();
			fetchResults( currentFilters( 1 ), true );
		} );
	}

	pagination.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( '[data-page]' );
		if ( ! link ) {
			return;
		}
		e.preventDefault();
		fetchResults( currentFilters( link.getAttribute( 'data-page' ) ), true );
	} );

	window.addEventListener( 'popstate', function ( e ) {
		if ( ! e.state ) {
			return;
		}
		applyFiltersToForm( e.state );
		fetchResults( e.state, false );
	} );
} )();
