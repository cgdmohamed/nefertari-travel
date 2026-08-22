<?php
/**
 * Share buttons — Facebook, X, WhatsApp, email, copy link. Shared by the
 * single blog post and single excursion templates.
 * Pass $args = [ 'url' => permalink, 'title' => share text ].
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$share_url   = $args['url'] ?? get_permalink();
$share_title = $args['title'] ?? get_the_title();
?>
<div class="nx-share">
	<span class="nx-share-label">Share</span>
	<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $share_url ) ); ?>" class="nx-share-btn" target="_blank" rel="noopener noreferrer" aria-label="Share on Facebook"><?php echo nefertari_icon( 'facebook', '#1877F2', 16 ); ?></a>
	<a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . rawurlencode( $share_url ) . '&text=' . rawurlencode( $share_title ) ); ?>" class="nx-share-btn" target="_blank" rel="noopener noreferrer" aria-label="Share on X"><?php echo nefertari_icon( 'x-twitter', 'currentColor', 15 ); ?></a>
	<a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( $share_title . ' ' . $share_url ) ); ?>" class="nx-share-btn" target="_blank" rel="noopener noreferrer" aria-label="Share on WhatsApp"><?php echo nefertari_icon( 'whatsapp', '#25D366', 16 ); ?></a>
	<a href="<?php echo esc_url( 'mailto:?subject=' . rawurlencode( $share_title ) . '&body=' . rawurlencode( $share_url ) ); ?>" class="nx-share-btn" aria-label="Share by email"><?php echo nefertari_icon( 'mail', 'currentColor', 16 ); ?></a>
	<button type="button" class="nx-share-btn" data-copy-link="<?php echo esc_attr( $share_url ); ?>" aria-label="Copy link"><?php echo nefertari_icon( 'link', 'currentColor', 16 ); ?></button>
</div>
