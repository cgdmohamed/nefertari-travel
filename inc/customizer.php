<?php
/**
 * Site-wide settings exposed via the Customizer: branding, contact info,
 * socials, trust stats, hero/why-us/footer/CTA copy, and legal page links.
 * Read anywhere through nefertari_option().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_customize_register( $wp_customize ) {

	$wp_customize->add_panel( 'nefertari_settings', array(
		'title'    => __( 'Nefertari Travel Settings', 'nefertari-travel' ),
		'priority' => 30,
	) );

	/* Branding --------------------------------------------------------------*/
	$wp_customize->add_section( 'nefertari_branding', array(
		'title'       => __( 'Branding & Colors', 'nefertari-travel' ),
		'panel'       => 'nefertari_settings',
		'description' => __( 'Upload a logo under Site Identity above. These colors drive every gradient, button, and accent across the site.', 'nefertari-travel' ),
	) );

	$color_fields = array(
		'color_gold'  => array( __( 'Primary color', 'nefertari-travel' ), '#FBBA6A' ),
		'color_coral' => array( __( 'Secondary color', 'nefertari-travel' ), '#F5786D' ),
		'color_pink'  => array( __( 'Accent color', 'nefertari-travel' ), '#D93A7C' ),
	);
	foreach ( $color_fields as $id => $field ) {
		list( $label, $default ) = $field;
		$wp_customize->add_setting( 'nefertari_' . $id, array(
			'default'           => $default,
			'sanitize_callback' => 'sanitize_hex_color',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'nefertari_' . $id, array(
			'label'   => $label,
			'section' => 'nefertari_branding',
		) ) );
	}

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

	$hero_text_fields = array(
		'hero_badge'      => array( __( 'Badge text (above heading)', 'nefertari-travel' ), 'text', 'Red Sea & Nile Valley · Daily departures' ),
		'hero_heading_1'  => array( __( 'Heading — line 1', 'nefertari-travel' ), 'text', "Egypt, the way" ),
		'hero_heading_2'  => array( __( 'Heading — line 2', 'nefertari-travel' ), 'text', "you'd dream it" ),
		'hero_heading_3'  => array( __( 'Heading — line 3 (highlighted)', 'nefertari-travel' ), 'text', '— excursion by excursion.' ),
		'hero_subheading' => array( __( 'Subheading', 'nefertari-travel' ), 'textarea', 'From desert safaris and the Pyramids to coral reefs and the temples of Luxor — handpicked day trips with hotel pickup, expert guides and small groups.' ),
		'hero_float_title'=> array( __( 'Floating badge title', 'nefertari-travel' ), 'text', 'Free hotel pickup' ),
		'hero_float_sub'  => array( __( 'Floating badge subtitle', 'nefertari-travel' ), 'text', 'Hurghada · Sahl Hasheesh · Makadi' ),
	);
	nefertari_add_text_settings( $wp_customize, 'nefertari_hero', $hero_text_fields );

	/* Why choose us ---------------------------------------------------------*/
	$wp_customize->add_section( 'nefertari_why', array(
		'title' => __( 'Why Choose Us Section', 'nefertari-travel' ),
		'panel' => 'nefertari_settings',
	) );

	$why_fields = array(
		'why_heading'    => array( __( 'Section heading', 'nefertari-travel' ), 'text', 'Travel with people who know Egypt' ),
		'why_subheading' => array( __( 'Section subheading', 'nefertari-travel' ), 'textarea', 'Licensed guides, comfortable air-conditioned transfers and small groups — so every trip feels personal.' ),
		'why_tile1_title' => array( __( 'Tile 1 — title', 'nefertari-travel' ), 'text', 'Free hotel pickup' ),
		'why_tile1_text'  => array( __( 'Tile 1 — text', 'nefertari-travel' ), 'textarea', 'Door-to-door transfers from all major resorts, included on every excursion.' ),
		'why_tile2_title' => array( __( 'Tile 2 — title', 'nefertari-travel' ), 'text', 'Expert local guides' ),
		'why_tile2_text'  => array( __( 'Tile 2 — text', 'nefertari-travel' ), 'textarea', 'Licensed Egyptologists and dive masters who bring every site to life.' ),
		'why_tile3_title' => array( __( 'Tile 3 — title', 'nefertari-travel' ), 'text', 'Book in seconds' ),
		'why_tile3_text'  => array( __( 'Tile 3 — text', 'nefertari-travel' ), 'textarea', 'No deposit needed — reserve your spot directly over WhatsApp.' ),
		'why_tile4_title' => array( __( 'Tile 4 — title', 'nefertari-travel' ), 'text', 'Loved by travellers' ),
		'why_tile4_text'  => array( __( 'Tile 4 — text', 'nefertari-travel' ), 'textarea', 'A 4.9-star average from thousands of guests across the Red Sea.' ),
	);
	nefertari_add_text_settings( $wp_customize, 'nefertari_why', $why_fields );

	/* Contact CTA -------------------------------------------------------------*/
	$wp_customize->add_section( 'nefertari_cta', array(
		'title' => __( 'Contact CTA Section', 'nefertari-travel' ),
		'panel' => 'nefertari_settings',
	) );

	$cta_fields = array(
		'cta_heading'    => array( __( 'Heading', 'nefertari-travel' ), 'text', 'Ready to explore?' ),
		'cta_subheading' => array( __( 'Subheading', 'nefertari-travel' ), 'textarea', "Message us on WhatsApp and we'll tailor the perfect program for your dates." ),
		'cta_button'     => array( __( 'Button label', 'nefertari-travel' ), 'text', 'Book an excursion →' ),
	);
	nefertari_add_text_settings( $wp_customize, 'nefertari_cta', $cta_fields );

	/* Footer ------------------------------------------------------------------*/
	$wp_customize->add_section( 'nefertari_footer', array(
		'title' => __( 'Footer', 'nefertari-travel' ),
		'panel' => 'nefertari_settings',
	) );

	$wp_customize->add_setting( 'nefertari_footer_description', array(
		'default'           => "Locally owned and Ministry-licensed since 2014. We've shown over 12,000 travellers the real Egypt — safely, comfortably and with people who love it.",
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'nefertari_footer_description', array(
		'label'   => __( 'Brand description', 'nefertari-travel' ),
		'section' => 'nefertari_footer',
		'type'    => 'textarea',
	) );

	$legal_fields = array(
		'privacy_page'      => __( 'Privacy policy page', 'nefertari-travel' ),
		'terms_page'        => __( 'Terms page', 'nefertari-travel' ),
		'cancellation_page' => __( 'Cancellation policy page', 'nefertari-travel' ),
	);
	foreach ( $legal_fields as $id => $label ) {
		$wp_customize->add_setting( 'nefertari_' . $id, array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		) );
		$wp_customize->add_control( 'nefertari_' . $id, array(
			'label'   => $label,
			'section' => 'nefertari_footer',
			'type'    => 'dropdown-pages',
		) );
	}
}
add_action( 'customize_register', 'nefertari_customize_register' );

/**
 * Registers a batch of plain text/textarea settings+controls in one go.
 *
 * @param array $fields key => [ label, type ('text'|'textarea'), default ]
 */
function nefertari_add_text_settings( $wp_customize, $section, $fields ) {
	foreach ( $fields as $id => $field ) {
		list( $label, $type, $default ) = $field;
		$wp_customize->add_setting( 'nefertari_' . $id, array(
			'default'           => $default,
			'sanitize_callback' => 'textarea' === $type ? 'sanitize_textarea_field' : 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'nefertari_' . $id, array(
			'label'   => $label,
			'section' => $section,
			'type'    => $type,
		) );
	}
}

/**
 * Convenience getter for any nefertari_* customizer option.
 */
function nefertari_option( $key, $default = '' ) {
	return get_theme_mod( 'nefertari_' . $key, $default );
}

/**
 * Applies the 3 brand colors everywhere via CSS custom property overrides,
 * so changing them in the Customizer re-themes the whole site — no CSS edits.
 */
function nefertari_output_custom_colors() {
	$gold  = nefertari_option( 'color_gold', '#FBBA6A' );
	$coral = nefertari_option( 'color_coral', '#F5786D' );
	$pink  = nefertari_option( 'color_pink', '#D93A7C' );
	printf(
		'<style id="nefertari-custom-colors">:root{--nx-gold:%1$s;--nx-coral:%2$s;--nx-pink:%3$s;--nx-gradient-brand:linear-gradient(135deg,%1$s,%2$s,%3$s);--nx-gradient-cta:linear-gradient(135deg,%2$s,%3$s);}</style>',
		esc_attr( $gold ),
		esc_attr( $coral ),
		esc_attr( $pink )
	);
}
add_action( 'wp_head', 'nefertari_output_custom_colors', 20 );
