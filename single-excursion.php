<?php
/**
 * Single excursion detail page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$excursion_id = get_the_ID();
	$rating       = get_post_meta( $excursion_id, '_nx_rating', true ) ?: '4.9';
	$reviews_n    = (int) get_post_meta( $excursion_id, '_nx_reviews_count', true );
	$booked_n     = (int) get_post_meta( $excursion_id, '_nx_booked_count', true );
	?>
	<div class="nx-detail-wrap">
		<?php
		nefertari_render_breadcrumbs( array(
			array( 'label' => 'Excursions', 'url' => get_post_type_archive_link( 'excursion' ) ),
			array( 'label' => get_the_title( $excursion_id ), 'url' => null ),
		) );
		?>
		<a href="<?php echo esc_url( home_url( '/#excursions' ) ); ?>" class="nx-back-link">← All excursions</a>

		<div class="nx-detail">
			<div class="nx-detail-main">
				<?php get_template_part( 'template-parts/excursion/gallery' ); ?>

				<h1 class="nx-detail-title"><?php the_title(); ?></h1>
				<div class="nx-detail-badges">
					<span class="nx-rating-inline"><span class="nx-stars">★★★★★</span> <?php echo esc_html( $rating ); ?> <span style="color:var(--nx-brown);font-weight:500">(<?php echo esc_html( $reviews_n ); ?> reviews)</span></span>
					<span class="nx-badge-green"><?php echo nefertari_icon( 'shield-check', '#1F8A5B', 13 ); ?> <?php echo esc_html( $booked_n ); ?> booked this month</span>
					<span class="nx-badge-orange"><svg width="13" height="13" viewBox="0 0 24 24" fill="#C2410C" stroke="none"><path d="M13 2L4 14h6l-1 8 9-12h-6l1-8z"></path></svg> Likely to sell out</span>
				</div>
				<div class="nx-detail-facts">
					<span class="nx-detail-fact"><?php echo nefertari_icon( 'pin', '#B07A55', 15 ); ?> <?php echo esc_html( nefertari_excursion_location( $excursion_id ) ); ?></span>
					<span class="nx-detail-fact"><?php echo nefertari_icon( 'clock', '#B07A55', 15 ); ?> <?php echo esc_html( nefertari_excursion_duration( $excursion_id ) ); ?></span>
					<span class="nx-detail-fact"><?php echo nefertari_icon( 'group', '#B07A55', 15 ); ?> Small groups</span>
					<span class="nx-detail-fact"><?php echo nefertari_icon( 'book', '#B07A55', 15 ); ?> English guide</span>
					<span class="nx-detail-fact"><?php echo nefertari_icon( 'shield-check', '#1F8A5B', 15 ); ?> Free cancellation</span>
				</div>

				<?php get_template_part( 'template-parts/share-buttons', null, array( 'url' => get_permalink( $excursion_id ), 'title' => get_the_title( $excursion_id ) ) ); ?>

				<p class="nx-detail-desc"><?php the_content(); ?></p>

				<?php get_template_part( 'template-parts/excursion/highlights' ); ?>
				<?php get_template_part( 'template-parts/excursion/inclusions' ); ?>
				<?php get_template_part( 'template-parts/excursion/itinerary' ); ?>
			</div>

			<?php get_template_part( 'template-parts/excursion/booking-card' ); ?>
		</div>

		<?php get_template_part( 'template-parts/excursion/reviews' ); ?>
	</div>
	<?php
endwhile;

get_footer();
