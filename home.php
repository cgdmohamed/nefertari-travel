<?php
/**
 * Blog index ("The Nefertari journal") — used for the site's posts page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="nx-page-hero">
	<div class="nx-page-hero-blob"></div>
	<div class="nx-page-hero-inner">
		<div class="nx-eyebrow">The Nefertari journal</div>
		<h1>Stories, guides &amp; tips for your Egyptian adventure</h1>
		<p>Local knowledge from the people who run the trips — when to come, what to pack and how to see Egypt at its best.</p>
	</div>
</section>

<section class="nx-section" style="padding-top:24px">
	<div class="nx-grid-3">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'template-parts/blog/card' ); ?>
		<?php endwhile; ?>
	</div>

	<div style="margin-top:40px">
		<?php the_posts_pagination(); ?>
	</div>
</section>
<?php
get_footer();
