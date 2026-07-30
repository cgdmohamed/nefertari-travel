<?php
/**
 * Homepage reviews section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$testimonials = nefertari_get_testimonials( 3 );
?>
<section id="reviews" class="nx-reviews">
	<div class="nx-reviews-inner">
		<div class="nx-reviews-head">
			<div class="nx-eyebrow">Loved by travellers</div>
			<h2>What our guests say</h2>
			<div class="nx-reviews-agg">
				<span class="nx-reviews-agg-item"><span class="nx-stars" style="font-size:17px">★★★★★</span> <?php echo esc_html( nefertari_option( 'rating', '4.9' ) ); ?> / 5</span>
				<span class="nx-reviews-agg-sep"></span>
				<span class="nx-reviews-agg-item"><?php echo esc_html( nefertari_option( 'reviews_count', '2,400+' ) ); ?> verified reviews</span>
				<span class="nx-reviews-agg-sep"></span>
				<span class="nx-reviews-agg-item"><?php echo esc_html( nefertari_option( 'travellers_count', '12,000+' ) ); ?> travellers since 2014</span>
			</div>
		</div>
		<div class="nx-grid-3">
			<?php foreach ( $testimonials as $i => $testimonial ) : ?>
				<?php get_template_part( 'template-parts/home/review-card', null, array( 'testimonial' => $testimonial, 'index' => $i, 'size' => 'lg' ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
