<?php
/**
 * 404 page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="nx-cta-section">
	<div class="nx-cta">
		<h2>Page not found</h2>
		<p>The page you're looking for doesn't exist. Try a search, or pick up from one of these instead.</p>
		<div class="nx-404-actions">
			<button type="button" class="nx-btn nx-btn--dark" id="nx-404-search"><?php echo nefertari_icon( 'search', 'currentColor', 16 ); ?> Search the site</button>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nx-btn nx-btn--outline">Back to homepage</a>
		</div>
		<div class="nx-404-links">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'excursion' ) ?: home_url( '/#excursions' ) ); ?>">Browse excursions</a>
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>">Read the journal</a>
			<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">Contact us</a>
		</div>
	</div>
</section>
<script>
document.getElementById( 'nx-404-search' ).addEventListener( 'click', function () {
	var trigger = document.getElementById( 'nx-search-trigger' );
	if ( trigger ) {
		trigger.click();
	}
} );
</script>
<?php
get_footer();
