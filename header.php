<?php
/**
 * Header: top trust bar + sticky nav.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="nx-page">

	<div class="nx-topbar">
		<div class="nx-topbar-row">
			<span class="nx-topbar-item"><span class="nx-stars" style="font-size:1em">★★★★★</span> <?php echo esc_html( nefertari_option( 'rating', '4.9' ) ); ?> · <?php echo esc_html( nefertari_option( 'reviews_count', '2,400+' ) ); ?> reviews</span>
			<span class="nx-topbar-sep">|</span>
			<span class="nx-topbar-item"><?php echo nefertari_icon( 'shield-check', '#FBBA6A', 14 ); ?> Ministry of Tourism licensed</span>
			<span class="nx-topbar-sep">|</span>
			<span class="nx-topbar-item"><?php echo nefertari_icon( 'card', '#FBBA6A', 14 ); ?> Secure online payment</span>
			<span class="nx-topbar-sep">|</span>
			<a href="tel:<?php echo esc_attr( nefertari_phone_tel() ); ?>" class="nx-topbar-phone"><?php echo nefertari_icon( 'phone', '#FBBA6A', 14 ); ?> <?php echo esc_html( nefertari_option( 'phone', '+20 111 202 6922' ) ); ?></a>
			<span class="nx-topbar-sep">|</span>
			<span><?php echo esc_html( nefertari_option( 'hours', 'Open daily · 8am–10pm' ) ); ?></span>
		</div>
	</div>

	<header class="nx-header">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nx-brand">
			<span class="nx-brand-mark">N</span>
			<span class="nx-brand-text">
				<span class="nx-brand-name"><?php bloginfo( 'name' ); ?><span>.</span></span>
				<div class="nx-brand-tag">Travel · Egypt</div>
			</span>
		</a>
		<div class="nx-header-right">
			<nav class="nx-nav">
				<a href="<?php echo esc_url( home_url( '/#excursions' ) ); ?>">Excursions</a>
				<a href="<?php echo esc_url( home_url( '/#why' ) ); ?>">Why us</a>
				<a href="<?php echo esc_url( home_url( '/#reviews' ) ); ?>">Reviews</a>
				<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>">Blog</a>
				<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">Contact</a>
			</nav>
			<button type="button" class="nx-btn nx-btn--primary nx-btn--sm" data-open-booking>Book now</button>
		</div>
	</header>
