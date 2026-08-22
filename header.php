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
			<?php if ( has_custom_logo() ) : ?>
				<?php
				// Not the_custom_logo(): it self-wraps in its own <a>, which
				// is invalid nested inside the .nx-brand <a> below — browsers
				// recover by auto-closing that outer anchor early, right
				// after this span, which detaches the brand text from the
				// link entirely. wp_get_attachment_image() renders the same
				// <img> with no wrapping anchor.
				?>
				<span class="nx-brand-mark nx-brand-mark--logo"><?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'custom-logo', 'alt' => get_bloginfo( 'name' ) ) ); ?></span>
			<?php else : ?>
				<span class="nx-brand-mark"><?php echo esc_html( mb_substr( get_bloginfo( 'name' ), 0, 1 ) ); ?></span>
			<?php endif; ?>
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
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( nefertari_account_url( 'account' ) ); ?>">My Account</a>
				<?php else : ?>
					<a href="<?php echo esc_url( nefertari_account_url( 'login' ) ); ?>">Log in</a>
				<?php endif; ?>
			</nav>
			<button type="button" class="nx-icon-btn" id="nx-search-trigger" aria-label="Search"><?php echo nefertari_icon( 'search', 'currentColor', 18 ); ?></button>
			<?php nefertari_book_button( 'Book now', 'nx-btn nx-btn--primary nx-btn--sm' ); ?>
		</div>
	</header>

	<?php get_template_part( 'template-parts/search-modal' ); ?>
