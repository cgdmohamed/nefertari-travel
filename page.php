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
		<?php nefertari_render_breadcrumbs( array( array( 'label' => get_the_title(), 'url' => null ) ) ); ?>
		<h1 class="nx-post-title"><?php the_title(); ?></h1>
		<div class="nx-post-body">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
