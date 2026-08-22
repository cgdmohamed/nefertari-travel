<?php
/**
 * Small "more from the journal" related post card.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = get_the_ID();
?>
<a href="<?php echo esc_url( get_permalink() ); ?>" class="nx-related-card">
	<div class="nx-related-media" style="background:<?php echo esc_attr( nefertari_avatar_gradient( $post_id ) ); ?>">
		<img src="<?php echo esc_url( nefertari_image_url( $post_id, 'nefertari-thumb' ) ); ?>" alt="<?php the_title_attribute(); ?>">
	</div>
	<div class="nx-related-body">
		<div class="nx-related-cat"><?php echo esc_html( nefertari_post_category_label( $post_id ) ); ?></div>
		<div class="nx-related-title-text"><?php the_title(); ?></div>
	</div>
</a>
