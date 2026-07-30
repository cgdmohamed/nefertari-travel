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
		<p>The page you're looking for doesn't exist. Let's get you back on track.</p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nx-btn nx-btn--dark">Back to homepage →</a>
	</div>
</section>
<?php
get_footer();
