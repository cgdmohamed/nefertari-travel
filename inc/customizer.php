<?php
/**
 * Site-wide settings exposed via the Customizer: contact info, socials,
 * trust stats and the hero image. Read anywhere through nefertari_option().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_customize_register( $wp_customize ) {

	$wp_customize->add_panel( 'nefertari_settings', array(
		'title'    => __( 'Nefertari Travel Settings', 'nefertari-travel' ),
		'priority' => 30,
	) );

	/* Contact & hours ---------------------------------------------------- */
	$wp_customize->add_section( 'nefertari_contact', array(
		'title' => __( 'Contact & Hours', 'nefertari-travel' ),
		'panel' => 'nefertari_settings',
	) );

	$contact_fields = array(
		'phone'         => array( __( 'Phone number', 'nefertari-travel' ), '+20 111 202 6922' ),
		'whatsapp'      => array( __( 'WhatsApp number (digits only, with country code)', 'nefertari-travel' ), '201112026922' ),
		'email'         => array( __( 'Contact email', 'nefertari-travel' ), 'hello@nefertaritravel.com' ),
		'address'       => array( __( 'Address', 'nefertari-travel' ), 'Sheraton Road, Hurghada, Red Sea Governorate, Egypt' ),
		'hours'         => array( __( 'Opening hours', 'nefertari-travel' ), 'Open daily · 8:00am – 10:00pm' ),
	);
	foreach ( $contact_fields as $id => $field ) {
		list( $label, $default ) = $field;
		$wp_customize->add_setting( 'nefertari_' . $id, array(
			'default'           => $default,
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'nefertari_' . $id, array(
			'label'   => $label,
			'section' => 'nefertari_contact',
			'type'    => 'text',
		) );
	}

	/* Social links --------------------------------------------------------*/
	$wp_customize->add_section( 'nefertari_social', array(
		'title' => __( 'Social Links', 'nefertari-travel' ),
		'panel' => 'nefertari_settings',
	) );

	$social_fields = array(
		'facebook'  => __( 'Facebook URL', 'nefertari-travel' ),
		'instagram' => __( 'Instagram URL', 'nefertari-travel' ),
		'tiktok'    => __( 'TikTok URL', 'nefertari-travel' ),
		'x'         => __( 'X / Twitter URL', 'nefertari-travel' ),
	);
	foreach ( $social_fields as $id => $label ) {
		$wp_customize->add_setting( 'nefertari_social_' . $id, array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( 'nefertari_social_' . $id, array(
			'label'   => $label,
			'section' => 'nefertari_social',
			'type'    => 'url',
		) );
	}

	/* Trust & stats ---------------------------------------------------- */
	$wp_customize->add_section( 'nefertari_trust', array(
		'title' => __( 'Trust & Stats', 'nefertari-travel' ),
		'panel' => 'nefertari_settings',
	) );

	$trust_fields = array(
		'rating'           => array( __( 'Average rating', 'nefertari-travel' ), '4.9' ),
		'reviews_count'    => array( __( 'Total review count label', 'nefertari-travel' ), '2,400+' ),
		'travellers_count' => array( __( 'Happy travellers label', 'nefertari-travel' ), '12k+' ),
		'license_number'   => array( __( 'Ministry of Tourism license #', 'nefertari-travel' ), '4471' ),
	);
	foreach ( $trust_fields as $id => $field ) {
		list( $label, $default ) = $field;
		$wp_customize->add_setting( 'nefertari_' . $id, array(
			'default'           => $default,
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'nefertari_' . $id, array(
			'label'   => $label,
			'section' => 'nefertari_trust',
			'type'    => 'text',
		) );
	}

	/* Hero ----------------------------------------------------------------*/
	$wp_customize->add_section( 'nefertari_hero', array(
		'title' => __( 'Homepage Hero', 'nefertari-travel' ),
		'panel' => 'nefertari_settings',
	) );

	$wp_customize->add_setting( 'nefertari_hero_image_url', array(
		'default'           => 'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=1200&q=80',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'nefertari_hero_image_url', array(
		'label'   => __( 'Hero image', 'nefertari-travel' ),
		'section' => 'nefertari_hero',
	) ) );
}
add_action( 'customize_register', 'nefertari_customize_register' );

/**
 * Convenience getter for any nefertari_* customizer option.
 */
function nefertari_option( $key, $default = '' ) {
	return get_theme_mod( 'nefertari_' . $key, $default );
}
