<?php
/**
 * Template helper functions shared across theme templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Contact / WhatsApp
 * ---------------------------------------------------------------------- */

function nefertari_whatsapp_number() {
	return preg_replace( '/\D/', '', nefertari_option( 'whatsapp', '201112026922' ) );
}

function nefertari_phone_tel() {
	return preg_replace( '/[^0-9+]/', '', nefertari_option( 'phone', '+20 111 202 6922' ) );
}

function nefertari_whatsapp_link( $message = '' ) {
	$url = 'https://wa.me/' . nefertari_whatsapp_number();
	if ( $message ) {
		$url .= '?text=' . rawurlencode( $message );
	}
	return $url;
}

/* -------------------------------------------------------------------------
 * Excursion helpers
 * ---------------------------------------------------------------------- */

/**
 * Featured image URL, falling back to a plain _nx_image_url meta value.
 * Works for excursions and blog posts alike, so seeded demo content can show
 * photos before anyone has uploaded a real featured image.
 */
function nefertari_image_url( $post_id, $size = 'large' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
		if ( $url ) {
			return $url;
		}
	}
	return get_post_meta( $post_id, '_nx_image_url', true );
}

function nefertari_excursion_gallery_urls( $post_id ) {
	$gallery = get_post_meta( $post_id, '_nx_gallery', true );
	return is_array( $gallery ) ? $gallery : array();
}

function nefertari_excursion_gradient_css( $post_id ) {
	$start = get_post_meta( $post_id, '_nx_gradient_start', true ) ?: '#FBBA6A';
	$end   = get_post_meta( $post_id, '_nx_gradient_end', true ) ?: '#D93A7C';
	return sprintf( 'linear-gradient(135deg,%s,%s)', $start, $end );
}

function nefertari_excursion_price( $post_id ) {
	$price = get_post_meta( $post_id, '_nx_price', true );
	return $price ? '$' . (int) $price : '';
}

function nefertari_excursion_category_label( $post_id ) {
	$terms = get_the_terms( $post_id, 'excursion_category' );
	if ( is_array( $terms ) && ! empty( $terms ) ) {
		return $terms[0]->name;
	}
	return '';
}

function nefertari_excursion_highlights( $post_id ) {
	$items = get_post_meta( $post_id, '_nx_highlights', true );
	return is_array( $items ) ? $items : array();
}

function nefertari_excursion_includes( $post_id ) {
	$items = get_post_meta( $post_id, '_nx_includes', true );
	return is_array( $items ) ? $items : array();
}

function nefertari_excursion_excludes( $post_id ) {
	$items = get_post_meta( $post_id, '_nx_excludes', true );
	return is_array( $items ) ? $items : array();
}

function nefertari_excursion_itinerary( $post_id ) {
	$days = get_post_meta( $post_id, '_nx_itinerary', true );
	return is_array( $days ) ? array_values( $days ) : array();
}

/* -------------------------------------------------------------------------
 * Testimonials
 * ---------------------------------------------------------------------- */

function nefertari_avatar_gradient( $index ) {
	$palette = array(
		'linear-gradient(135deg,#F5786D,#D93A7C)',
		'linear-gradient(135deg,#36A9E1,#1AA7A0)',
		'linear-gradient(135deg,#FBBA6A,#F5786D)',
		'linear-gradient(135deg,#1AA7A0,#127E9A)',
	);
	return $palette[ $index % count( $palette ) ];
}

function nefertari_get_testimonials( $count = 3 ) {
	$query = new WP_Query( array(
		'post_type'      => 'testimonial',
		'posts_per_page' => $count,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	) );
	return $query->posts;
}

/* -------------------------------------------------------------------------
 * Blog
 * ---------------------------------------------------------------------- */

function nefertari_reading_time( $content ) {
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$minutes = max( 1, (int) round( $words / 200 ) );
	/* translators: %d: number of minutes */
	return sprintf( _n( '%d min', '%d min', $minutes, 'nefertari-travel' ), $minutes );
}

function nefertari_post_category_label( $post_id ) {
	$terms = get_the_category( $post_id );
	if ( ! empty( $terms ) ) {
		return $terms[0]->name;
	}
	return '';
}

/* -------------------------------------------------------------------------
 * Inline icons — small line-art SVGs used throughout the theme, kept as
 * static markup (no user input) so no escaping is required at output.
 * ---------------------------------------------------------------------- */

function nefertari_icon( $name, $stroke = 'currentColor', $size = 16 ) {
	$paths = array(
		'shield-check' => '<path d="M12 3l7 3v5c0 4.5-3 7.8-7 9-4-1.2-7-4.5-7-9V6l7-3z"></path><path d="M9 12l2 2 4-4"></path>',
		'card'         => '<rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path>',
		'phone'        => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L20 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"></path>',
		'medal'        => '<circle cx="12" cy="9" r="5"></circle><path d="M8.5 13l-2 7 5.5-3 5.5 3-2-7"></path>',
		'clock'        => '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3.5 2"></path>',
		'pin'          => '<path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11z"></path><circle cx="12" cy="10" r="2.5"></circle>',
		'group'        => '<circle cx="9" cy="8" r="3"></circle><path d="M3 20a6 6 0 0 1 12 0"></path><path d="M16 5.5a3 3 0 0 1 0 5.6"></path><path d="M21 20a5.5 5.5 0 0 0-4-5.3"></path>',
		'book'         => '<path d="M4 5h16v11H8l-4 4V5z"></path><path d="M8 9h8M8 12h5"></path>',
		'whatsapp'     => '<path d="M21 11.5a8.4 8.4 0 0 1-12 7.6L3 21l1.9-5.6A8.4 8.4 0 1 1 21 11.5z"></path>',
		'shuttle'      => '<path d="M3 13l1.4-5.2A2 2 0 0 1 6.3 6.3H15a2 2 0 0 1 1.8 1.1L18.8 12"></path><path d="M2 13h18.5a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-1.5"></path><path d="M6 17H4a1 1 0 0 1-1-1v-3"></path><path d="M9.5 17h4.5"></path><circle cx="7.2" cy="17" r="1.7"></circle><circle cx="16.2" cy="17" r="1.7"></circle>',
		'compass'      => '<circle cx="12" cy="12" r="9"></circle><path d="M15.6 8.4l-2 5.2-5.2 2 2-5.2 5.2-2z"></path>',
		'star-burst'   => '<path d="M12 3l2.6 5.6 6 .8-4.4 4.1 1.1 6L12 16.8 6.7 19.6l1.1-6L3.4 9.4l6-.8L12 3z"></path>',
		'mail'         => '<rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3.5 6.5l8.5 6 8.5-6"></path>',
		'facebook'     => '<path d="M14 9h2.5V6H14c-2 0-3.5 1.5-3.5 3.5V11H8.5v3h2v6h3v-6H16l.5-3h-3V9.7c0-.4.3-.7.7-.7z"></path>',
		'instagram'    => '<rect x="4" y="4" width="16" height="16" rx="5"></rect><circle cx="12" cy="12" r="3.6"></circle><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"></circle>',
		'x-twitter'    => '<path d="M17.5 3h3l-6.6 7.6L21.6 21h-5.9l-4.3-5.6L6.3 21H3.3l7-8.1L2.7 3h6l3.9 5.1L17.5 3z"></path>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	$fill = in_array( $name, array( 'facebook', 'x-twitter' ), true ) ? $stroke : 'none';

	return sprintf(
		'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="%2$s" stroke="%3$s" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">%4$s</svg>',
		(int) $size,
		esc_attr( $fill ),
		esc_attr( $stroke ),
		$paths[ $name ]
	);
}
