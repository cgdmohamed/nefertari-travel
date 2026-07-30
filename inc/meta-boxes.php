<?php
/**
 * Hand-rolled meta boxes for the Excursion and Testimonial post types.
 *
 * No plugin dependency — plain post meta, simple repeater rows driven by
 * assets/js/admin-repeater.js. Intended to be replaced by a dedicated data
 * plugin later; kept self-contained so it's easy to remove.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Registration
 * ---------------------------------------------------------------------- */

function nefertari_add_meta_boxes() {
	add_meta_box( 'nx_excursion_details', __( 'Excursion Details', 'nefertari-travel' ), 'nefertari_render_details_metabox', 'excursion', 'normal', 'high' );
	add_meta_box( 'nx_excursion_gallery', __( 'Gallery', 'nefertari-travel' ), 'nefertari_render_gallery_metabox', 'excursion', 'normal', 'default' );
	add_meta_box( 'nx_excursion_highlights', __( 'Trip Highlights', 'nefertari-travel' ), 'nefertari_render_highlights_metabox', 'excursion', 'normal', 'default' );
	add_meta_box( 'nx_excursion_inclusions', __( "What's Included / Not Included", 'nefertari-travel' ), 'nefertari_render_inclusions_metabox', 'excursion', 'normal', 'default' );
	add_meta_box( 'nx_excursion_itinerary', __( 'Excursion Timeline', 'nefertari-travel' ), 'nefertari_render_itinerary_metabox', 'excursion', 'normal', 'default' );

	add_meta_box( 'nx_testimonial_details', __( 'Testimonial Details', 'nefertari-travel' ), 'nefertari_render_testimonial_metabox', 'testimonial', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'nefertari_add_meta_boxes' );

/* -------------------------------------------------------------------------
 * Field helpers
 * ---------------------------------------------------------------------- */

function nefertari_field_row( $label, $field_html, $help = '' ) {
	echo '<div class="nx-field-row">';
	echo '<label class="nx-field-label">' . esc_html( $label ) . '</label>';
	echo '<div class="nx-field-input">' . $field_html . '</div>'; // phpcs:ignore -- built from esc_* below
	if ( $help ) {
		echo '<p class="description">' . esc_html( $help ) . '</p>';
	}
	echo '</div>';
}

function nefertari_render_simple_repeater( $field, $items, $placeholder = '' ) {
	$items = is_array( $items ) ? $items : array();
	if ( empty( $items ) ) {
		$items = array( '' );
	}
	echo '<div class="nx-repeater" data-repeater="simple" data-field="' . esc_attr( $field ) . '">';
	echo '<input type="hidden" name="' . esc_attr( $field ) . '_marker" value="1">';
	echo '<div class="nx-repeater-rows">';
	foreach ( $items as $item ) {
		echo '<div class="nx-repeater-row">';
		echo '<input type="text" name="' . esc_attr( $field ) . '[]" value="' . esc_attr( $item ) . '" placeholder="' . esc_attr( $placeholder ) . '" class="widefat">';
		echo '<button type="button" class="button nx-remove-row">&times;</button>';
		echo '</div>';
	}
	echo '</div>';
	echo '<template class="nx-row-template"><div class="nx-repeater-row"><input type="text" name="' . esc_attr( $field ) . '[]" value="" placeholder="' . esc_attr( $placeholder ) . '" class="widefat"><button type="button" class="button nx-remove-row">&times;</button></div></template>';
	echo '<button type="button" class="button nx-add-row">+ Add item</button>';
	echo '</div>';
}

/* -------------------------------------------------------------------------
 * Excursion: details
 * ---------------------------------------------------------------------- */

function nefertari_render_details_metabox( $post ) {
	wp_nonce_field( 'nefertari_save_excursion', 'nefertari_excursion_nonce' );

	$location     = get_post_meta( $post->ID, '_nx_location', true );
	$duration     = get_post_meta( $post->ID, '_nx_duration', true );
	$price        = get_post_meta( $post->ID, '_nx_price', true );
	$rating       = get_post_meta( $post->ID, '_nx_rating', true ) ?: '4.9';
	$reviews_n    = get_post_meta( $post->ID, '_nx_reviews_count', true ) ?: '500';
	$booked_n     = get_post_meta( $post->ID, '_nx_booked_count', true ) ?: '40';
	$grad_start   = get_post_meta( $post->ID, '_nx_gradient_start', true ) ?: '#FBBA6A';
	$grad_end     = get_post_meta( $post->ID, '_nx_gradient_end', true ) ?: '#D93A7C';
	$fallback_img = get_post_meta( $post->ID, '_nx_image_url', true );
	?>
	<div class="nx-metabox-grid">
		<?php nefertari_field_row( __( 'Location', 'nefertari-travel' ), '<input type="text" name="nx_location" value="' . esc_attr( $location ) . '" class="widefat" placeholder="e.g. Hurghada Desert">' ); ?>
		<?php nefertari_field_row( __( 'Duration', 'nefertari-travel' ), '<input type="text" name="nx_duration" value="' . esc_attr( $duration ) . '" class="widefat" placeholder="e.g. Full day">' ); ?>
		<?php nefertari_field_row( __( 'Price per person (USD)', 'nefertari-travel' ), '<input type="number" step="1" min="0" name="nx_price" value="' . esc_attr( $price ) . '" class="widefat" placeholder="e.g. 95">' ); ?>
		<?php nefertari_field_row( __( 'Rating', 'nefertari-travel' ), '<input type="text" name="nx_rating" value="' . esc_attr( $rating ) . '" class="small-text" placeholder="4.9">' ); ?>
		<?php nefertari_field_row( __( 'Review count', 'nefertari-travel' ), '<input type="number" min="0" name="nx_reviews_count" value="' . esc_attr( $reviews_n ) . '" class="small-text">' ); ?>
		<?php nefertari_field_row( __( 'Booked this month', 'nefertari-travel' ), '<input type="number" min="0" name="nx_booked_count" value="' . esc_attr( $booked_n ) . '" class="small-text">' ); ?>
		<?php nefertari_field_row( __( 'Card gradient', 'nefertari-travel' ), '<input type="text" class="nx-color-field" name="nx_gradient_start" value="' . esc_attr( $grad_start ) . '"> <input type="text" class="nx-color-field" name="nx_gradient_end" value="' . esc_attr( $grad_end ) . '">' ); ?>
		<?php nefertari_field_row( __( 'Fallback image URL', 'nefertari-travel' ), '<input type="text" name="nx_image_url" value="' . esc_attr( $fallback_img ) . '" class="widefat" placeholder="https://…">', __( 'Used only when no featured image is set.', 'nefertari-travel' ) ); ?>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Excursion: gallery
 * ---------------------------------------------------------------------- */

function nefertari_render_gallery_metabox( $post ) {
	$gallery = get_post_meta( $post->ID, '_nx_gallery', true );
	nefertari_render_simple_repeater( 'nx_gallery', $gallery, 'https://…' );
	echo '<p class="description">' . esc_html__( 'Image URLs shown in the excursion detail gallery, alongside the featured image.', 'nefertari-travel' ) . '</p>';
}

/* -------------------------------------------------------------------------
 * Excursion: highlights
 * ---------------------------------------------------------------------- */

function nefertari_render_highlights_metabox( $post ) {
	$highlights = get_post_meta( $post->ID, '_nx_highlights', true );
	nefertari_render_simple_repeater( 'nx_highlights', $highlights, 'e.g. Quad bike & buggy ride' );
}

/* -------------------------------------------------------------------------
 * Excursion: includes / excludes
 * ---------------------------------------------------------------------- */

function nefertari_render_inclusions_metabox( $post ) {
	$includes = get_post_meta( $post->ID, '_nx_includes', true );
	$excludes = get_post_meta( $post->ID, '_nx_excludes', true );
	?>
	<div class="nx-two-col">
		<div>
			<h4><?php esc_html_e( 'Included', 'nefertari-travel' ); ?></h4>
			<?php nefertari_render_simple_repeater( 'nx_includes', $includes, 'e.g. Hotel transfers' ); ?>
		</div>
		<div>
			<h4><?php esc_html_e( 'Not included', 'nefertari-travel' ); ?></h4>
			<?php nefertari_render_simple_repeater( 'nx_excludes', $excludes, 'e.g. Tips & personal expenses' ); ?>
		</div>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Excursion: itinerary (days -> steps)
 * ---------------------------------------------------------------------- */

function nefertari_render_itinerary_metabox( $post ) {
	$days = get_post_meta( $post->ID, '_nx_itinerary', true );
	$days = is_array( $days ) ? $days : array();
	if ( empty( $days ) ) {
		$days = array( array( 'label' => '', 'steps' => array( array( 'time' => '', 'title' => '', 'text' => '' ) ) ) );
	}
	$next_day_index = count( $days );
	?>
	<p class="description"><?php esc_html_e( 'Add one "day" per stage of the trip (a single-day excursion just needs one day). Each day has a list of timed steps.', 'nefertari-travel' ); ?></p>
	<input type="hidden" name="nx_itinerary_marker" value="1">
	<div class="nx-itinerary" data-next-day-index="<?php echo esc_attr( $next_day_index ); ?>">
		<div class="nx-itinerary-days">
			<?php foreach ( $days as $d_index => $day ) : ?>
				<?php nefertari_render_itinerary_day( $d_index, $day ); ?>
			<?php endforeach; ?>
		</div>
		<template class="nx-day-template"><?php nefertari_render_itinerary_day( '__DAY__', array( 'label' => '', 'steps' => array() ) ); ?></template>
		<button type="button" class="button button-primary nx-add-day">+ Add day</button>
	</div>
	<?php
}

function nefertari_render_itinerary_day( $d_index, $day ) {
	$label = isset( $day['label'] ) ? $day['label'] : '';
	$steps = isset( $day['steps'] ) && is_array( $day['steps'] ) ? $day['steps'] : array();
	if ( empty( $steps ) ) {
		$steps = array( array( 'time' => '', 'title' => '', 'text' => '' ) );
	}
	$next_step_index = '__DAY__' === $d_index ? 0 : count( $steps );
	?>
	<div class="nx-itinerary-day" data-next-step-index="<?php echo esc_attr( $next_step_index ); ?>">
		<div class="nx-itinerary-day-head">
			<input type="text" class="widefat" placeholder="Day label, e.g. Giza Plateau & Sphinx" name="nx_itinerary[<?php echo esc_attr( $d_index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>">
			<button type="button" class="button nx-remove-day">Remove day</button>
		</div>
		<div class="nx-itinerary-steps">
			<?php foreach ( $steps as $s_index => $step ) : ?>
				<?php nefertari_render_itinerary_step( $d_index, $s_index, $step ); ?>
			<?php endforeach; ?>
		</div>
		<template class="nx-step-template"><?php nefertari_render_itinerary_step( $d_index, '__STEP__', array( 'time' => '', 'title' => '', 'text' => '' ) ); ?></template>
		<button type="button" class="button nx-add-step">+ Add step</button>
	</div>
	<?php
}

function nefertari_render_itinerary_step( $d_index, $s_index, $step ) {
	$time  = isset( $step['time'] ) ? $step['time'] : '';
	$title = isset( $step['title'] ) ? $step['title'] : '';
	$text  = isset( $step['text'] ) ? $step['text'] : '';
	$name  = 'nx_itinerary[' . $d_index . '][steps][' . $s_index . ']';
	?>
	<div class="nx-itinerary-step">
		<input type="text" class="nx-step-time" placeholder="2:30 PM" name="<?php echo esc_attr( $name ); ?>[time]" value="<?php echo esc_attr( $time ); ?>">
		<input type="text" class="nx-step-title" placeholder="Step title" name="<?php echo esc_attr( $name ); ?>[title]" value="<?php echo esc_attr( $title ); ?>">
		<input type="text" class="nx-step-text widefat" placeholder="Description" name="<?php echo esc_attr( $name ); ?>[text]" value="<?php echo esc_attr( $text ); ?>">
		<button type="button" class="button nx-remove-step">&times;</button>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Testimonial
 * ---------------------------------------------------------------------- */

function nefertari_render_testimonial_metabox( $post ) {
	wp_nonce_field( 'nefertari_save_testimonial', 'nefertari_testimonial_nonce' );
	$meta_line = get_post_meta( $post->ID, '_nx_meta_line', true );
	$rating    = get_post_meta( $post->ID, '_nx_rating', true ) ?: '5';
	?>
	<?php nefertari_field_row( __( 'Meta line (country · trip)', 'nefertari-travel' ), '<input type="text" name="nx_meta_line" value="' . esc_attr( $meta_line ) . '" class="widefat" placeholder="e.g. United Kingdom · Cairo & Pyramids">' ); ?>
	<?php nefertari_field_row( __( 'Rating (1–5)', 'nefertari-travel' ), '<input type="number" min="1" max="5" name="nx_rating" value="' . esc_attr( $rating ) . '" class="small-text">' ); ?>
	<p class="description"><?php esc_html_e( 'Use the title field for the traveller\'s name and the main editor for the review text.', 'nefertari-travel' ); ?></p>
	<?php
}

/* -------------------------------------------------------------------------
 * Saving
 * ---------------------------------------------------------------------- */

function nefertari_sanitize_repeater_array( $raw ) {
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$clean = array();
	foreach ( $raw as $item ) {
		$item = sanitize_text_field( $item );
		if ( '' !== $item ) {
			$clean[] = $item;
		}
	}
	return $clean;
}

function nefertari_sanitize_itinerary( $raw ) {
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$days = array();
	foreach ( $raw as $day ) {
		$label = isset( $day['label'] ) ? sanitize_text_field( $day['label'] ) : '';
		$steps = array();
		if ( isset( $day['steps'] ) && is_array( $day['steps'] ) ) {
			foreach ( $day['steps'] as $step ) {
				$time  = isset( $step['time'] ) ? sanitize_text_field( $step['time'] ) : '';
				$title = isset( $step['title'] ) ? sanitize_text_field( $step['title'] ) : '';
				$text  = isset( $step['text'] ) ? sanitize_text_field( $step['text'] ) : '';
				if ( '' !== $time || '' !== $title || '' !== $text ) {
					$steps[] = array( 'time' => $time, 'title' => $title, 'text' => $text );
				}
			}
		}
		if ( '' !== $label || ! empty( $steps ) ) {
			$days[] = array( 'label' => $label, 'steps' => $steps );
		}
	}
	return $days;
}

function nefertari_save_excursion_meta( $post_id ) {
	if ( ! isset( $_POST['nefertari_excursion_nonce'] ) || ! wp_verify_nonce( $_POST['nefertari_excursion_nonce'], 'nefertari_save_excursion' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text_fields = array( 'nx_location', 'nx_duration', 'nx_rating', 'nx_gradient_start', 'nx_gradient_end', 'nx_image_url' );
	foreach ( $text_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	$number_fields = array( 'nx_price', 'nx_reviews_count', 'nx_booked_count' );
	foreach ( $number_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, absint( $_POST[ $field ] ) );
		}
	}

	$repeater_fields = array( 'nx_gallery', 'nx_highlights', 'nx_includes', 'nx_excludes' );
	foreach ( $repeater_fields as $field ) {
		if ( ! isset( $_POST[ $field . '_marker' ] ) ) {
			continue; // Meta box wasn't part of this submission at all.
		}
		$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : array();
		update_post_meta( $post_id, '_' . $field, nefertari_sanitize_repeater_array( $raw ) );
	}

	if ( isset( $_POST['nx_itinerary_marker'] ) ) {
		$raw = isset( $_POST['nx_itinerary'] ) ? wp_unslash( $_POST['nx_itinerary'] ) : array();
		update_post_meta( $post_id, '_nx_itinerary', nefertari_sanitize_itinerary( $raw ) );
	}
}
add_action( 'save_post_excursion', 'nefertari_save_excursion_meta' );

function nefertari_save_testimonial_meta( $post_id ) {
	if ( ! isset( $_POST['nefertari_testimonial_nonce'] ) || ! wp_verify_nonce( $_POST['nefertari_testimonial_nonce'], 'nefertari_save_testimonial' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['nx_meta_line'] ) ) {
		update_post_meta( $post_id, '_nx_meta_line', sanitize_text_field( wp_unslash( $_POST['nx_meta_line'] ) ) );
	}
	if ( isset( $_POST['nx_rating'] ) ) {
		update_post_meta( $post_id, '_nx_rating', absint( $_POST['nx_rating'] ) );
	}
}
add_action( 'save_post_testimonial', 'nefertari_save_testimonial_meta' );
