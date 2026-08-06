<?php
/**
 * Excursion detail gallery: main image + clickable thumbnails.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$excursion_id = get_the_ID();
$main_image   = nefertari_image_url( $excursion_id, 'large' );
$gallery      = nefertari_excursion_gallery_urls( $excursion_id );
$all_images   = array_merge( array( $main_image ), $gallery );
?>
<div class="nx-gallery-main swiper" id="nx-gallery-swiper" style="background:<?php echo esc_attr( nefertari_excursion_gradient_css( $excursion_id ) ); ?>">
	<span class="nx-pill"><?php echo esc_html( nefertari_excursion_category_label( $excursion_id ) ); ?></span>
	<div class="swiper-wrapper">
		<?php foreach ( $all_images as $url ) : ?>
			<div class="swiper-slide"><img src="<?php echo esc_url( $url ); ?>" alt="<?php the_title_attribute(); ?>"></div>
		<?php endforeach; ?>
	</div>
	<?php if ( count( $all_images ) > 1 ) : ?>
		<div class="nx-gallery-nav nx-gallery-nav--prev"></div>
		<div class="nx-gallery-nav nx-gallery-nav--next"></div>
		<div class="swiper-pagination"></div>
	<?php endif; ?>
</div>
<?php if ( count( $all_images ) > 1 ) : ?>
	<div class="nx-thumbs">
		<?php foreach ( $all_images as $i => $url ) : ?>
			<img src="<?php echo esc_url( $url ); ?>" alt="" class="nx-thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" data-slide-index="<?php echo (int) $i; ?>">
		<?php endforeach; ?>
	</div>
<?php endif; ?>
