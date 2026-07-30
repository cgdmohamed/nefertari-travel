<?php
/**
 * "From the journal" — 3 most recent blog posts on the homepage.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recent = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'no_found_rows' => true ) );
if ( ! $recent->have_posts() ) {
	return;
}

$blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog/' );
?>
<section class="nx-section" style="padding-top:20px">
	<div class="nx-section-head">
		<div>
			<div class="nx-eyebrow">From the journal</div>
			<h2 class="nx-heading" style="font-size:clamp(30px,3.4vw,44px)">Guides &amp; tips for your trip</h2>
		</div>
		<a href="<?php echo esc_url( $blog_url ); ?>" class="nx-view-all">View all articles →</a>
	</div>
	<div class="nx-grid-3">
		<?php while ( $recent->have_posts() ) : $recent->the_post(); ?>
			<?php get_template_part( 'template-parts/blog/card' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
