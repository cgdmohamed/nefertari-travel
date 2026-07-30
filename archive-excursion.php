<?php
/**
 * Excursion archive — a plain grid fallback; the homepage's excursions
 * section is the primary way visitors browse trips.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="nx-page-hero">
	<div class="nx-page-hero-blob"></div>
	<div class="nx-page-hero-inner">
		<div class="nx-eyebrow">Choose your adventure</div>
		<h1>Our excursion programs</h1>
	</div>
</section>

<section class="nx-section" style="padding-top:24px">
	<div class="nx-grid-4">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'template-parts/home/excursion-card' ); ?>
		<?php endwhile; ?>
	</div>
	<div style="margin-top:40px">
		<?php the_posts_pagination(); ?>
	</div>
</section>
<?php
get_footer();
