<?php
/**
 * Recent traveller reviews block on the excursion detail page.
 * Shows the same latest testimonials as the homepage.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$excursion_id = get_the_ID();
$rating       = get_post_meta( $excursion_id, '_nx_rating', true ) ?: '4.9';
$reviews_n    = (int) get_post_meta( $excursion_id, '_nx_reviews_count', true );
$testimonials = nefertari_get_testimonials( 3 );

if ( empty( $testimonials ) ) {
	return;
}
?>
<div class="nx-detail-reviews">
	<h3 class="nx-detail-reviews-title">Recent traveller reviews</h3>
	<div class="nx-detail-reviews-sub"><span class="nx-stars">★★★★★</span> <?php echo esc_html( $rating ); ?> average · <?php echo esc_html( $reviews_n ); ?> verified reviews</div>
	<div class="nx-grid-3">
		<?php foreach ( $testimonials as $i => $testimonial ) : ?>
			<?php get_template_part( 'template-parts/home/review-card', null, array( 'testimonial' => $testimonial, 'index' => $i, 'size' => 'sm' ) ); ?>
		<?php endforeach; ?>
	</div>
</div>
