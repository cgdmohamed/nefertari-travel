<?php
/**
 * Homepage excursions grid.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$excursions = new WP_Query( array(
	'post_type'      => 'excursion',
	'posts_per_page' => -1,
	'orderby'        => 'menu_order title',
	'order'          => 'ASC',
	'no_found_rows'  => true,
) );
?>
<section id="excursions" class="nx-section">
	<div class="nx-section-head">
		<div>
			<div class="nx-eyebrow">Choose your adventure</div>
			<h2 class="nx-heading" style="font-size:clamp(32px,3.6vw,46px)">Our excursion programs</h2>
		</div>
		<p>Tap any trip for the full itinerary, what's included and instant WhatsApp booking.</p>
	</div>
	<div class="nx-grid-4">
		<?php while ( $excursions->have_posts() ) : $excursions->the_post(); ?>
			<?php get_template_part( 'template-parts/home/excursion-card' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
