<?php
/**
 * Blog post card. Expects $post (global) to be set up via the loop.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = get_the_ID();
$grad    = nefertari_avatar_gradient( $post_id );
?>
<a href="<?php echo esc_url( get_permalink() ); ?>" class="nx-card nx-blog-card">
	<div class="nx-card-media" style="background:<?php echo esc_attr( $grad ); ?>">
		<span class="nx-pill"><?php echo esc_html( nefertari_post_category_label( $post_id ) ); ?></span>
		<img src="<?php echo esc_url( nefertari_image_url( $post_id, 'nefertari-card' ) ); ?>" alt="<?php the_title_attribute(); ?>">
	</div>
	<div class="nx-blog-card-body">
		<h3 class="nx-blog-card-title"><?php the_title(); ?></h3>
		<p class="nx-blog-card-excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<div class="nx-blog-card-meta">
			<span><?php echo esc_html( get_the_date( 'M Y' ) ); ?></span>
			<span class="nx-blog-card-read"><?php echo nefertari_icon( 'clock', '#B07A55', 13 ); ?> <?php echo esc_html( nefertari_reading_time( get_the_content() ) ); ?> read</span>
		</div>
	</div>
</a>
