<?php
/**
 * Single excursion card. Expects $post (global) to be set up via the loop.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$excursion_id = get_the_ID();
?>
<a href="<?php echo esc_url( get_permalink() ); ?>" class="nx-card">
	<div class="nx-card-media" style="background:<?php echo esc_attr( nefertari_excursion_gradient_css( $excursion_id ) ); ?>">
		<img src="<?php echo esc_url( nefertari_image_url( $excursion_id, 'nefertari-card' ) ); ?>" alt="<?php the_title_attribute(); ?>">
		<div class="nx-card-media-overlay"></div>
		<span class="nx-pill"><?php echo esc_html( nefertari_excursion_category_label( $excursion_id ) ); ?></span>
		<div class="nx-card-location"><?php echo esc_html( get_post_meta( $excursion_id, '_nx_location', true ) ); ?></div>
	</div>
	<div class="nx-card-body">
		<h3 class="nx-card-title"><?php the_title(); ?></h3>
		<p class="nx-card-blurb"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<div class="nx-card-foot">
			<div class="nx-card-duration"><?php echo nefertari_icon( 'clock', '#B07A55', 14 ); ?> <?php echo esc_html( get_post_meta( $excursion_id, '_nx_duration', true ) ); ?></div>
			<div class="nx-card-price">from <?php echo esc_html( nefertari_excursion_price( $excursion_id ) ); ?> →</div>
		</div>
	</div>
</a>
