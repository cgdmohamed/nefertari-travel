<?php
/**
 * Single blog post: author/date/read-time, cover, body, CTA, related posts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog/' );

while ( have_posts() ) :
	the_post();
	$post_id     = get_the_ID();
	$author_name = get_the_author();
	$grad        = nefertari_avatar_gradient( $post_id );
	?>
	<article class="nx-post">
		<?php
		nefertari_render_breadcrumbs( array(
			array( 'label' => 'Blog', 'url' => $blog_url ),
			array( 'label' => get_the_title( $post_id ), 'url' => null ),
		) );
		?>
		<a href="<?php echo esc_url( $blog_url ); ?>" class="nx-back-link">← All articles</a>

		<span class="nx-post-category"><?php echo esc_html( nefertari_post_category_label( $post_id ) ); ?></span>
		<h1 class="nx-post-title"><?php the_title(); ?></h1>
		<div class="nx-post-meta">
			<div class="nx-post-author">
				<div class="nx-avatar" style="background:<?php echo esc_attr( $grad ); ?>;width:36px;height:36px;font-size:15px"><?php echo esc_html( mb_substr( $author_name, 0, 1 ) ); ?></div>
				<span class="nx-post-author-name"><?php echo esc_html( $author_name ); ?></span>
			</div>
			<span class="nx-post-meta-dot"></span>
			<span><?php echo esc_html( get_the_date( 'M Y' ) ); ?></span>
			<span class="nx-post-meta-dot"></span>
			<span class="nx-post-meta-read"><?php echo nefertari_icon( 'clock', '#B07A55', 14 ); ?> <?php echo esc_html( nefertari_reading_time( get_the_content() ) ); ?> read</span>
		</div>

		<?php get_template_part( 'template-parts/share-buttons', null, array( 'url' => get_permalink( $post_id ), 'title' => get_the_title( $post_id ) ) ); ?>

		<div class="nx-post-cover" style="background:<?php echo esc_attr( $grad ); ?>">
			<img src="<?php echo esc_url( nefertari_image_url( $post_id, 'large' ) ); ?>" alt="<?php the_title_attribute(); ?>">
		</div>

		<?php if ( has_excerpt() ) : ?>
			<p class="nx-post-excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<?php
		list( $post_content, $toc ) = nefertari_post_toc( apply_filters( 'the_content', get_the_content() ) );
		?>
		<?php if ( count( $toc ) >= 2 ) : ?>
			<details class="nx-post-toc" aria-label="Table of contents">
				<summary class="nx-post-toc-title">On this page</summary>
				<ol>
					<?php foreach ( $toc as $item ) : ?>
						<li class="nx-post-toc-item nx-post-toc-item--h<?php echo (int) $item['level']; ?>"><a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a></li>
					<?php endforeach; ?>
				</ol>
			</details>
		<?php endif; ?>

		<div class="nx-post-body">
			<?php echo $post_content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content()'s own filtered output, round-tripped through DOMDocument only to stamp heading ids; nothing added is unescaped user input. ?>
		</div>

		<div class="nx-post-cta">
			<h3>Ready to see it for yourself?</h3>
			<p>Browse our handpicked excursions and book your spot in a couple of minutes.</p>
			<a href="<?php echo esc_url( home_url( '/#excursions' ) ); ?>" class="nx-btn nx-btn--dark">Explore excursions →</a>
		</div>

		<?php
		$related = new WP_Query( array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'post__not_in'   => array( $post_id ),
			'category__in'   => wp_get_post_categories( $post_id ),
			'no_found_rows'  => true,
		) );
		if ( $related->have_posts() ) :
			?>
			<h3 class="nx-related-title">More from the journal</h3>
			<div class="nx-grid-3">
				<?php while ( $related->have_posts() ) : $related->the_post(); ?>
					<?php get_template_part( 'template-parts/blog/related-card' ); ?>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
