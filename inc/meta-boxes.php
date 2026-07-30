<?php
/**
 * Hand-rolled meta box for the Testimonial post type.
 *
 * Excursion fields now live entirely in the Nefertari Booking Core plugin
 * (see inc/plugin-bridge.php for the theme's supplementary trust-signal
 * fields on that same post type).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_add_meta_boxes() {
	add_meta_box( 'nx_testimonial_details', __( 'Testimonial Details', 'nefertari-travel' ), 'nefertari_render_testimonial_metabox', 'testimonial', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'nefertari_add_meta_boxes' );

function nefertari_field_row( $label, $field_html, $help = '' ) {
	echo '<div class="nx-field-row">';
	echo '<label class="nx-field-label">' . esc_html( $label ) . '</label>';
	echo '<div class="nx-field-input">' . $field_html . '</div>'; // phpcs:ignore -- built from esc_* below
	if ( $help ) {
		echo '<p class="description">' . esc_html( $help ) . '</p>';
	}
	echo '</div>';
}

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
