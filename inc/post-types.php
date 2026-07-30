<?php
/**
 * Custom post types & taxonomies: excursion, testimonial.
 *
 * NOTE: a dedicated data plugin is planned to take over content management
 * for excursions later. Registration lives here for now so the theme is
 * fully functional on its own; keep this file self-contained so it can be
 * removed cleanly once the plugin lands.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_register_post_types() {

	register_post_type( 'excursion', array(
		'labels' => array(
			'name'               => __( 'Excursions', 'nefertari-travel' ),
			'singular_name'      => __( 'Excursion', 'nefertari-travel' ),
			'add_new_item'       => __( 'Add New Excursion', 'nefertari-travel' ),
			'edit_item'          => __( 'Edit Excursion', 'nefertari-travel' ),
			'all_items'          => __( 'Excursions', 'nefertari-travel' ),
			'menu_name'          => __( 'Excursions', 'nefertari-travel' ),
			'not_found'          => __( 'No excursions found', 'nefertari-travel' ),
		),
		'public'        => true,
		'menu_icon'     => 'dashicons-palmtree',
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'has_archive'   => true,
		'rewrite'       => array( 'slug' => 'excursion' ),
		'show_in_rest'  => true,
	) );

	register_taxonomy( 'excursion_category', 'excursion', array(
		'labels' => array(
			'name'          => __( 'Categories', 'nefertari-travel' ),
			'singular_name' => __( 'Category', 'nefertari-travel' ),
		),
		'public'       => true,
		'hierarchical' => true,
		'rewrite'      => array( 'slug' => 'excursion-category' ),
		'show_in_rest' => true,
	) );

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

/**
 * Seed default excursion categories on init so the taxonomy always has terms
 * even before seed content runs (harmless if they already exist).
 */
function nefertari_register_default_excursion_categories() {
	$categories = array( 'Desert', 'Heritage', 'Nile', 'Sea', 'Family' );
	foreach ( $categories as $category ) {
		if ( ! term_exists( $category, 'excursion_category' ) ) {
			wp_insert_term( $category, 'excursion_category' );
		}
	}
}
add_action( 'after_switch_theme', 'nefertari_register_default_excursion_categories' );
