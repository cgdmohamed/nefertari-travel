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
<div class="nx-gallery-main" style="background:<?php echo esc_attr( nefertari_excursion_gradient_css( $excursion_id ) ); ?>">
	<span class="nx-pill"><?php echo esc_html( nefertari_excursion_category_label( $excursion_id ) ); ?></span>
	<img src="<?php echo esc_url( $main_image ); ?>" alt="<?php the_title_attribute(); ?>">
</div>
<?php if ( count( $all_images ) > 1 ) : ?>
	<div class="nx-thumbs">
		<?php foreach ( $all_images as $i => $url ) : ?>
			<img src="<?php echo esc_url( $url ); ?>" alt="" class="nx-thumb<?php echo 0 === $i ? ' is-active' : ''; ?>">
		<?php endforeach; ?>
	</div>
<?php endif; ?>
