<?php
/**
 * Site-wide search modal — searches excursions and blog posts together via
 * WordPress core's own /wp/v2/search REST endpoint (both post types already
 * have show_in_rest => true, so no custom endpoint is needed). Behavior in
 * assets/js/search.js.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="nx-modal nx-search-modal" id="nx-search-modal">
	<button type="button" class="nx-modal-backdrop" data-search-close aria-label="Close"></button>
	<div class="nx-modal-box nx-search-box">
		<div class="nx-search-field">
			<?php echo nefertari_icon( 'search', 'var(--nx-ink-soft)', 18 ); ?>
			<input type="text" id="nx-search-input" placeholder="Search excursions and articles…" autocomplete="off">
			<button type="button" class="nx-modal-close" data-search-close aria-label="Close">✕</button>
		</div>
		<div class="nx-search-results" id="nx-search-results">
			<p class="nx-search-hint">Start typing to search trips and journal articles.</p>
		</div>
	</div>
</div>
