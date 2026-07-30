<?php
/**
 * Trip highlights grid.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$highlights = nefertari_excursion_highlights( get_the_ID() );
if ( empty( $highlights ) ) {
	return;
}
?>
<h3 class="nx-subheading">Trip highlights</h3>
<div class="nx-grid-2" style="margin-bottom:36px">
	<?php foreach ( $highlights as $highlight ) : ?>
		<div class="nx-highlight-item">
			<span class="nx-highlight-check">✓</span>
			<span class="nx-highlight-text"><?php echo esc_html( $highlight ); ?></span>
		</div>
	<?php endforeach; ?>
</div>
