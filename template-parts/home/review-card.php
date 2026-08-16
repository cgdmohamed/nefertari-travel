<?php
/**
 * A single testimonial card. Pass $args = array( 'testimonial' => WP_Post, 'index' => int, 'size' => 'lg'|'sm' ).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$testimonial = $args['testimonial'];
$index       = $args['index'];
$size        = isset( $args['size'] ) ? $args['size'] : 'lg';
$meta_line   = get_post_meta( $testimonial->ID, '_nx_meta_line', true );
$initial     = mb_substr( $testimonial->post_title, 0, 1 );
$avatar_size = 'sm' === $size ? 38 : 42;
$rating      = max( 1, min( 5, (int) ( get_post_meta( $testimonial->ID, '_nx_rating', true ) ?: 5 ) ) );
?>
<div class="nx-review-card<?php echo 'sm' === $size ? ' nx-review-card--sm' : ''; ?>">
	<div class="nx-review-top">
		<div class="nx-stars" style="font-size:<?php echo 'sm' === $size ? '15' : '16'; ?>px"><?php echo str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ); ?></div>
		<span class="nx-verified">✓ Verified</span>
	</div>
	<p class="nx-review-text">“<?php echo esc_html( $testimonial->post_content ); ?>”</p>
	<div class="nx-review-person">
		<div class="nx-avatar" style="background:<?php echo esc_attr( nefertari_avatar_gradient( $index ) ); ?>;width:<?php echo $avatar_size; ?>px;height:<?php echo $avatar_size; ?>px;font-size:<?php echo 'sm' === $size ? '15' : '16'; ?>px"><?php echo esc_html( $initial ); ?></div>
		<div>
			<div class="nx-review-name"><?php echo esc_html( $testimonial->post_title ); ?></div>
			<div class="nx-review-meta"><?php echo esc_html( $meta_line ); ?></div>
		</div>
	</div>
</div>
