<?php
/**
 * Homepage: hero, trust strip, excursions grid, why-us, reviews,
 * blog preview, contact CTA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/trust-strip' );
get_template_part( 'template-parts/home/excursions-grid' );
get_template_part( 'template-parts/home/why-us' );
get_template_part( 'template-parts/home/reviews' );
get_template_part( 'template-parts/home/blog-preview' );
get_template_part( 'template-parts/home/cta' );

get_footer();
