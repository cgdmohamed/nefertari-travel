<?php
/**
 * AJAX handlers. Currently just the excursion archive filter — a plain
 * admin-ajax action rather than a REST route since this is pure page
 * presentation (which excursions to show), not booking/payment domain
 * logic, so it belongs in the theme, not the plugin's REST API.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_ajax_filter_excursions() {
	check_ajax_referer( 'nefertari_filter_excursions', 'nonce' );

	$filters = array(
		'category'    => sanitize_title( wp_unslash( $_POST['category'] ?? '' ) ),
		'destination' => sanitize_title( wp_unslash( $_POST['destination'] ?? '' ) ),
		'min_price'   => sanitize_text_field( wp_unslash( $_POST['min_price'] ?? '' ) ),
		'max_price'   => sanitize_text_field( wp_unslash( $_POST['max_price'] ?? '' ) ),
		'sort'        => sanitize_key( wp_unslash( $_POST['sort'] ?? '' ) ),
		'paged'       => absint( $_POST['paged'] ?? 1 ),
	);

	$query = new WP_Query( nefertari_excursion_query_args( $filters ) );

	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/home/excursion-card' );
		}
		wp_reset_postdata();
	} else {
		echo '<p class="nx-bookings-empty">No excursions match those filters — try widening your search.</p>';
	}
	$cards_html = ob_get_clean();

	ob_start();
	nefertari_render_ajax_pagination( $query->max_num_pages, max( 1, $filters['paged'] ) );
	$pagination_html = ob_get_clean();

	wp_send_json_success( array(
		'html'       => $cards_html,
		'pagination' => $pagination_html,
		'found'      => (int) $query->found_posts,
	) );
}
add_action( 'wp_ajax_nefertari_filter_excursions', 'nefertari_ajax_filter_excursions' );
add_action( 'wp_ajax_nopriv_nefertari_filter_excursions', 'nefertari_ajax_filter_excursions' );
