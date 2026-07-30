<?php
/**
 * Custom post types owned by the theme: testimonial.
 *
 * The `excursion` post type and `excursion_category` taxonomy are owned by
 * the Nefertari Booking Core plugin (real slots, capacity, pricing) — the
 * theme no longer registers them. See inc/plugin-bridge.php for the
 * dependency check.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_register_post_types() {
	register_post_type( 'testimonial', array(
		'labels' => array(
			'name'          => __( 'Testimonials', 'nefertari-travel' ),
			'singular_name' => __( 'Testimonial', 'nefertari-travel' ),
			'add_new_item'  => __( 'Add New Testimonial', 'nefertari-travel' ),
			'edit_item'     => __( 'Edit Testimonial', 'nefertari-travel' ),
			'all_items'     => __( 'Testimonials', 'nefertari-travel' ),
			'menu_name'     => __( 'Testimonials', 'nefertari-travel' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-star-filled',
		'menu_position' => 6,
		'supports'      => array( 'title', 'editor' ),
		'show_in_rest'  => false,
	) );
}
add_action( 'init', 'nefertari_register_post_types' );
