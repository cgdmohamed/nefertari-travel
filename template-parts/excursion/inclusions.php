<?php
/**
 * What's included / not included, two columns.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$excursion_id = get_the_ID();
$includes     = nefertari_excursion_includes( $excursion_id );
$excludes     = nefertari_excursion_excludes( $excursion_id );

if ( empty( $includes ) && empty( $excludes ) ) {
	return;
}
?>
<h3 class="nx-subheading">What's included &amp; what's not</h3>
<div class="nx-grid-2" style="margin-bottom:36px">
	<div class="nx-incl-box nx-incl-box--yes">
		<div class="nx-incl-head nx-incl-head--yes"><span class="nx-incl-icon nx-incl-icon--yes">✓</span> Included</div>
		<div class="nx-incl-list">
			<?php foreach ( $includes as $item ) : ?>
				<div class="nx-incl-row"><span class="nx-incl-mark nx-incl-mark--yes">✓</span> <?php echo esc_html( $item ); ?></div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="nx-incl-box nx-incl-box--no">
		<div class="nx-incl-head nx-incl-head--no"><span class="nx-incl-icon nx-incl-icon--no">✕</span> Not included</div>
		<div class="nx-incl-list">
			<?php foreach ( $excludes as $item ) : ?>
				<div class="nx-incl-row nx-incl-row--no"><span class="nx-incl-mark nx-incl-mark--no">✕</span> <?php echo esc_html( $item ); ?></div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
