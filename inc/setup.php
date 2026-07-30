<?php
/**
 * Core theme setup: supports, menus, assets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );

	set_post_thumbnail_size( 1000, 700, true );
	add_image_size( 'nefertari-card', 600, 400, true );
	add_image_size( 'nefertari-thumb', 240, 160, true );

	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'nefertari-travel' ),
	) );
}
add_action( 'after_setup_theme', 'nefertari_setup' );

function nefertari_content_width() {
	$GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'nefertari_content_width', 0 );

function nefertari_enqueue_assets() {
	wp_enqueue_style(
		'nefertari-google-fonts',
		'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;600;700;800&family=DM+Sans:wght@400;500;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'nefertari-style', NEFERTARI_URI . '/assets/css/style.css', array(), NEFERTARI_VERSION );

	wp_enqueue_script( 'nefertari-main', NEFERTARI_URI . '/assets/js/main.js', array(), NEFERTARI_VERSION, true );
	wp_enqueue_script( 'nefertari-booking', NEFERTARI_URI . '/assets/js/booking.js', array(), NEFERTARI_VERSION, true );

	wp_localize_script( 'nefertari-booking', 'nefertariBooking', array(
		'whatsapp'   => nefertari_whatsapp_number(),
		'siteName'   => get_bloginfo( 'name' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'nefertari_enqueue_assets' );

function nefertari_admin_assets( $hook ) {
	global $post_type;
	if ( 'excursion' !== $post_type ) {
		return;
	}
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'nefertari-repeater', NEFERTARI_URI . '/assets/js/admin-repeater.js', array( 'jquery', 'wp-color-picker' ), NEFERTARI_VERSION, true );
	wp_enqueue_style( 'nefertari-admin', NEFERTARI_URI . '/assets/css/admin.css', array(), NEFERTARI_VERSION );
}
add_action( 'admin_enqueue_scripts', 'nefertari_admin_assets' );
