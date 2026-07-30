<?php
/**
 * Fallback template (search results, generic archives, etc.).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="nx-section" style="padding-top:40px">
	<?php if ( have_posts() ) : ?>
		<div class="nx-grid-3">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'template-parts/blog/card' ); ?>
			<?php endwhile; ?>
		</div>
		<div style="margin-top:40px">
			<?php the_posts_pagination(); ?>
		</div>
	<?php else : ?>
		<p>Nothing found.</p>
	<?php endif; ?>
</section>
<?php
get_footer();
