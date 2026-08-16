<?php
/**
 * Homepage hero.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$excursion_count = wp_count_posts( 'excursion' )->publish;
$wa_hero = nefertari_whatsapp_link( 'Hi ' . get_bloginfo( 'name' ) . ', I\'d like to know more about your excursion programs.' );
?>
<section class="nx-hero-section">
	<div class="nx-hero-blob"></div>
	<div class="nx-hero">
		<div>
			<div class="nx-hero-badge"><span class="nx-hero-badge-dot"></span> <?php echo esc_html( nefertari_option( 'hero_badge', 'Red Sea & Nile Valley · Daily departures' ) ); ?></div>
			<h1 class="nx-hero-title">
				<?php echo esc_html( nefertari_option( 'hero_heading_1', 'Egypt, the way' ) ); ?><br><?php echo esc_html( nefertari_option( 'hero_heading_2', "you'd dream it" ) ); ?><br>
				<span class="nx-gradient-text"><?php echo esc_html( nefertari_option( 'hero_heading_3', '— excursion by excursion.' ) ); ?></span>
			</h1>
			<p class="nx-hero-text"><?php echo esc_html( nefertari_option( 'hero_subheading', 'From desert safaris and the Pyramids to coral reefs and the temples of Luxor — handpicked day trips with hotel pickup, expert guides and small groups.' ) ); ?></p>
			<div class="nx-hero-ctas">
				<a href="#excursions" class="nx-btn nx-btn--primary">Explore excursions →</a>
				<a href="<?php echo esc_url( $wa_hero ); ?>" target="_blank" rel="noopener" class="nx-btn nx-btn--light">Ask a question</a>
			</div>
			<div class="nx-hero-stats">
				<div><div class="nx-hero-stat-num"><?php echo (int) $excursion_count; ?>+</div><div class="nx-hero-stat-label">Excursion types</div></div>
				<div><div class="nx-hero-stat-num"><?php echo esc_html( nefertari_option( 'travellers_count', '12k+' ) ); ?></div><div class="nx-hero-stat-label">Happy travellers</div></div>
				<div><div class="nx-hero-stat-num"><?php echo esc_html( nefertari_option( 'rating', '4.9' ) ); ?>★</div><div class="nx-hero-stat-label">Average rating</div></div>
			</div>
		</div>
		<div class="nx-hero-media">
			<div class="nx-hero-frame">
				<img src="<?php echo esc_url( nefertari_option( 'hero_image_url', 'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=1200&q=80' ) ); ?>" alt="Egypt" class="nx-hero-img">
			</div>
			<div class="nx-hero-float">
				<div class="nx-hero-float-icon">↗</div>
				<div>
					<div class="nx-hero-float-title"><?php echo esc_html( nefertari_option( 'hero_float_title', 'Free hotel pickup' ) ); ?></div>
					<div class="nx-hero-float-sub"><?php echo esc_html( nefertari_option( 'hero_float_sub', 'Hurghada · Sahl Hasheesh · Makadi' ) ); ?></div>
				</div>
			</div>
		</div>
	</div>
</section>
