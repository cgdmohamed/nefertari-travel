<?php
/**
 * Excursion archive — a filterable grid (category, destination, price,
 * sort). The initial page load is a real server-rendered WP_Query driven
 * by $_GET (so it works with no JS and is shareable/bookmarkable); after
 * that, assets/js/excursion-filter.js re-queries via admin-ajax and swaps
 * the grid/pagination in place. nefertari_excursion_query_args() builds
 * the query for both paths so they can't drift apart.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$categories   = get_terms( array( 'taxonomy' => 'excursion_category', 'hide_empty' => true ) );
$destinations = get_terms( array( 'taxonomy' => 'destination', 'hide_empty' => true ) );
$categories   = is_wp_error( $categories ) ? array() : $categories;
$destinations = is_wp_error( $destinations ) ? array() : $destinations;

$filters = array(
	'category'    => sanitize_title( wp_unslash( $_GET['category'] ?? '' ) ),
	'destination' => sanitize_title( wp_unslash( $_GET['destination'] ?? '' ) ),
	'min_price'   => sanitize_text_field( wp_unslash( $_GET['min_price'] ?? '' ) ),
	'max_price'   => sanitize_text_field( wp_unslash( $_GET['max_price'] ?? '' ) ),
	'sort'        => sanitize_key( wp_unslash( $_GET['sort'] ?? '' ) ),
	'paged'       => max( 1, (int) get_query_var( 'paged' ) ?: (int) ( $_GET['paged'] ?? 1 ) ),
);

$query = new WP_Query( nefertari_excursion_query_args( $filters ) );
?>
<section class="nx-page-hero">
	<div class="nx-page-hero-blob"></div>
	<div class="nx-page-hero-inner">
		<div class="nx-eyebrow">Choose your adventure</div>
		<h1>Our excursion programs</h1>
	</div>
</section>

<section class="nx-section" style="padding-top:24px">
	<form class="nx-excursion-filters" id="nx-excursion-filters">
		<select name="category" class="nx-input">
			<option value="">All categories</option>
			<?php foreach ( $categories as $term ) : ?>
				<option value="<?php echo esc_attr( $term->slug ); ?>"<?php selected( $filters['category'], $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="destination" class="nx-input">
			<option value="">All destinations</option>
			<?php foreach ( $destinations as $term ) : ?>
				<option value="<?php echo esc_attr( $term->slug ); ?>"<?php selected( $filters['destination'], $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="number" name="min_price" class="nx-input" placeholder="Min $" value="<?php echo esc_attr( $filters['min_price'] ); ?>" min="0">
		<input type="number" name="max_price" class="nx-input" placeholder="Max $" value="<?php echo esc_attr( $filters['max_price'] ); ?>" min="0">
		<select name="sort" class="nx-input">
			<option value=""<?php selected( $filters['sort'], '' ); ?>>Recommended</option>
			<option value="price_asc"<?php selected( $filters['sort'], 'price_asc' ); ?>>Price: low to high</option>
			<option value="price_desc"<?php selected( $filters['sort'], 'price_desc' ); ?>>Price: high to low</option>
		</select>
		<button type="button" class="nx-btn nx-btn--outline nx-btn--sm" id="nx-filter-reset">Reset</button>
	</form>

	<p class="nx-excursion-count" id="nx-excursion-count"><?php echo esc_html( $query->found_posts ); ?> excursion<?php echo 1 === (int) $query->found_posts ? '' : 's'; ?> found</p>

	<div class="nx-grid-4" id="nx-excursion-grid">
		<?php if ( $query->have_posts() ) : ?>
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php get_template_part( 'template-parts/home/excursion-card' ); ?>
			<?php endwhile; wp_reset_postdata(); ?>
		<?php else : ?>
			<p class="nx-bookings-empty">No excursions match those filters — try widening your search.</p>
		<?php endif; ?>
	</div>

	<div id="nx-excursion-pagination"><?php nefertari_render_ajax_pagination( $query->max_num_pages, $filters['paged'] ); ?></div>
</section>
<?php
get_footer();
