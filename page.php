<?php
/**
 * Generic page template (Privacy, Terms, etc.).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="nx-post">
		<h1 class="nx-post-title"><?php the_title(); ?></h1>
		<div class="nx-post-body">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
