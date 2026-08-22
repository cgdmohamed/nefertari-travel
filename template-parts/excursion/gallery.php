<?php
/**
 * Excursion detail gallery: an auto-sliding image slider (assets/js/main.js)
 * + clickable thumbnails. Custom-built rather than a third-party library, so
 * there's no risk of colliding with another plugin's own bundled copy of a
 * common slider library on a real, multi-plugin WordPress site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$excursion_id = get_the_ID();
$main_image   = nefertari_image_url( $excursion_id, 'large' );
$gallery      = nefertari_excursion_gallery_urls( $excursion_id );
$all_images   = array_merge( array( $main_image ), $gallery );
$has_multiple = count( $all_images ) > 1;
?>
<div class="nx-gallery-main" id="nx-gallery" style="background:<?php echo esc_attr( nefertari_excursion_gradient_css( $excursion_id ) ); ?>">
	<span class="nx-pill"><?php echo esc_html( nefertari_excursion_category_label( $excursion_id ) ); ?></span>
	<div class="nx-slider-track">
		<?php foreach ( $all_images as $url ) : ?>
			<div class="nx-slide"><img src="<?php echo esc_url( $url ); ?>" alt="<?php the_title_attribute(); ?>"></div>
		<?php endforeach; ?>
	</div>
	<?php if ( $has_multiple ) : ?>
		<button type="button" class="nx-gallery-nav nx-gallery-nav--prev" aria-label="Previous image"></button>
		<button type="button" class="nx-gallery-nav nx-gallery-nav--next" aria-label="Next image"></button>
		<div class="nx-gallery-dots"></div>
	<?php endif; ?>
</div>
<?php if ( $has_multiple ) : ?>
	<div class="nx-thumbs">
		<?php foreach ( $all_images as $i => $url ) : ?>
			<img src="<?php echo esc_url( $url ); ?>" alt="" class="nx-thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" data-slide-index="<?php echo (int) $i; ?>">
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<div class="nx-lightbox" id="nx-lightbox">
	<button type="button" class="nx-lightbox-close" data-lightbox-close aria-label="Close">✕</button>
	<button type="button" class="nx-lightbox-nav nx-lightbox-nav--prev" data-lightbox-prev aria-label="Previous image">‹</button>
	<button type="button" class="nx-lightbox-nav nx-lightbox-nav--next" data-lightbox-next aria-label="Next image">›</button>
	<div class="nx-lightbox-stage">
		<img id="nx-lightbox-img" src="" alt="">
	</div>
	<div class="nx-lightbox-counter" id="nx-lightbox-counter"></div>
</div>
