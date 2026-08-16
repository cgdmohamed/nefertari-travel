<?php
/**
 * Customer accounts: login, registration, and the account page. Booking
 * requires a WordPress account (the plugin has no guest checkout), so this
 * gives customers a themed way to register/log in without touching wp-login.php.
 *
 * New accounts get the plugin's own `nefertari_customer` role when the
 * plugin is active, otherwise the site default.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Page seeding — page-{slug}.php templates need a real Page with that slug.
 * ---------------------------------------------------------------------- */

function nefertari_seed_account_pages() {
	$pages   = array(
		'login'           => 'Log In',
		'register'        => 'Create Account',
		'account'         => 'My Account',
		'payment-result'  => 'Payment Result',
	);
	$created = false;
	foreach ( $pages as $slug => $title ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		wp_insert_post( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => $title,
			'post_name'   => $slug,
		) );
		$created = true;
	}
	// A freshly created page can 404 until rewrite rules are rebuilt —
	// especially likely here since the plugin's "excursion" post type
	// already has its own custom rewrite rules in the mix.
	if ( $created ) {
		flush_rewrite_rules();
	}
}
add_action( 'after_switch_theme', 'nefertari_seed_account_pages' );

/**
 * Self-heals sites where the theme was already active before this feature
 * existed — `after_switch_theme` never fires again for them, so without
 * this the account pages would simply never get created. Runs the (cheap,
 * idempotent) seed check once per site via an option flag, not on every
 * request.
 */
function nefertari_maybe_seed_account_pages() {
	if ( ! get_option( 'nefertari_account_pages_seeded' ) ) {
		nefertari_seed_account_pages();
		update_option( 'nefertari_account_pages_seeded', '1' );
	}
	// Separate one-time flag: sites that already ran the block above before
	// the flush_rewrite_rules() call existed never got it, which can leave
	// their account pages 404ing even though the pages themselves exist.
	if ( ! get_option( 'nefertari_account_pages_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'nefertari_account_pages_flushed', '1' );
	}
}
add_action( 'admin_init', 'nefertari_maybe_seed_account_pages' );

function nefertari_booking_is_retryable( $status ) {
	return in_array( $status, array( 'awaiting_payment', 'payment_failed', 'payment_expired' ), true );
}

function nefertari_booking_is_cancellable( $status ) {
	return 'confirmed' === $status;
}

/**
 * URL for one of the account pages, falling back to home if it's somehow
 * missing (e.g. deleted after activation).
 */
function nefertari_account_url( $which ) {
	$page = get_page_by_path( $which );
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/* -------------------------------------------------------------------------
 * Login
 * ---------------------------------------------------------------------- */

function nefertari_handle_login_submit() {
	if ( ! is_page( 'login' ) || ! isset( $_POST['nefertari_login'] ) ) {
		return;
	}
	if ( ! isset( $_POST['nefertari_login_nonce'] ) || ! wp_verify_nonce( $_POST['nefertari_login_nonce'], 'nefertari_login' ) ) {
		wp_safe_redirect( add_query_arg( 'error', 'invalid_request', nefertari_account_url( 'login' ) ) );
		exit;
	}

	$creds = array(
		'user_login'    => sanitize_text_field( wp_unslash( $_POST['user_login'] ?? '' ) ),
		'user_password' => (string) ( $_POST['user_password'] ?? '' ),
		'remember'      => true,
	);
	$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : nefertari_account_url( 'account' );

	if ( '' === $creds['user_login'] || '' === $creds['user_password'] ) {
		wp_safe_redirect( add_query_arg( 'error', 'empty_fields', nefertari_account_url( 'login' ) ) );
		exit;
	}

	$user = wp_signon( $creds, is_ssl() );
	if ( is_wp_error( $user ) ) {
		wp_safe_redirect( add_query_arg( 'error', 'invalid_login', nefertari_account_url( 'login' ) ) );
		exit;
	}

	wp_safe_redirect( $redirect_to );
	exit;
}
add_action( 'template_redirect', 'nefertari_handle_login_submit' );

function nefertari_login_error_message( $code ) {
	$messages = array(
		'empty_fields'    => 'Please enter your username/email and password.',
		'invalid_login'   => 'That username/email and password don\'t match.',
		'invalid_request' => 'Your session expired — please try again.',
	);
	return $messages[ $code ] ?? '';
}

/* -------------------------------------------------------------------------
 * Registration
 * ---------------------------------------------------------------------- */

function nefertari_handle_register_submit() {
	if ( ! is_page( 'register' ) || ! isset( $_POST['nefertari_register'] ) ) {
		return;
	}
	if ( ! isset( $_POST['nefertari_register_nonce'] ) || ! wp_verify_nonce( $_POST['nefertari_register_nonce'], 'nefertari_register' ) ) {
		wp_safe_redirect( add_query_arg( 'error', 'invalid_request', nefertari_account_url( 'register' ) ) );
		exit;
	}
	if ( ! get_option( 'users_can_register' ) ) {
		wp_safe_redirect( add_query_arg( 'error', 'registration_closed', nefertari_account_url( 'register' ) ) );
		exit;
	}

	$name      = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email     = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$password  = (string) ( $_POST['password'] ?? '' );
	$password2 = (string) ( $_POST['password2'] ?? '' );
	$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : nefertari_account_url( 'account' );

	$error = '';
	if ( '' === $name || '' === $email || '' === $password ) {
		$error = 'empty_fields';
	} elseif ( ! is_email( $email ) ) {
		$error = 'invalid_email';
	} elseif ( email_exists( $email ) ) {
		$error = 'email_exists';
	} elseif ( strlen( $password ) < 8 ) {
		$error = 'weak_password';
	} elseif ( $password !== $password2 ) {
		$error = 'password_mismatch';
	}

	if ( $error ) {
		wp_safe_redirect( add_query_arg( 'error', $error, nefertari_account_url( 'register' ) ) );
		exit;
	}

	$username = nefertari_unique_username_from_email( $email );
	$user_id  = wp_insert_user( array(
		'user_login'   => $username,
		'user_email'   => $email,
		'user_pass'    => $password,
		'display_name' => $name,
		'first_name'   => $name,
		'role'         => nefertari_booking_plugin_active() ? 'nefertari_customer' : get_option( 'default_role', 'subscriber' ),
	) );

	if ( is_wp_error( $user_id ) ) {
		wp_safe_redirect( add_query_arg( 'error', 'registration_failed', nefertari_account_url( 'register' ) ) );
		exit;
	}

	wp_set_auth_cookie( $user_id, true );
	wp_safe_redirect( $redirect_to );
	exit;
}
add_action( 'template_redirect', 'nefertari_handle_register_submit' );

function nefertari_unique_username_from_email( $email ) {
	$base     = sanitize_user( current( explode( '@', $email ) ), true );
	$base     = $base ?: 'customer';
	$username = $base;
	$i        = 1;
	while ( username_exists( $username ) ) {
		$username = $base . $i;
		$i++;
	}
	return $username;
}

function nefertari_register_error_message( $code ) {
	$messages = array(
		'empty_fields'        => 'Please fill in every field.',
		'invalid_email'       => 'That doesn\'t look like a valid email address.',
		'email_exists'        => 'An account with that email already exists — try logging in instead.',
		'weak_password'       => 'Please use a password of at least 8 characters.',
		'password_mismatch'   => 'Passwords don\'t match.',
		'registration_failed' => 'Something went wrong creating your account. Please try again.',
		'registration_closed' => 'New account registration is currently closed.',
		'invalid_request'     => 'Your session expired — please try again.',
	);
	return $messages[ $code ] ?? '';
}

/* -------------------------------------------------------------------------
 * Account page: profile update
 * ---------------------------------------------------------------------- */

function nefertari_handle_account_update() {
	if ( ! is_page( 'account' ) || ! isset( $_POST['nefertari_update_profile'] ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	if ( ! isset( $_POST['nefertari_account_nonce'] ) || ! wp_verify_nonce( $_POST['nefertari_account_nonce'], 'nefertari_update_profile' ) ) {
		return;
	}

	$user_id = get_current_user_id();
	wp_update_user( array(
		'ID'           => $user_id,
		'display_name' => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'first_name'   => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
	) );
	update_user_meta( $user_id, 'nefertari_phone', sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ) );

	wp_safe_redirect( add_query_arg( 'updated', '1', nefertari_account_url( 'account' ) ) );
	exit;
}
add_action( 'template_redirect', 'nefertari_handle_account_update' );

/* -------------------------------------------------------------------------
 * Account page: retry payment on a booking that never got paid
 * ---------------------------------------------------------------------- */

function nefertari_handle_retry_payment() {
	if ( ! is_page( 'account' ) || ! isset( $_POST['nefertari_retry_payment'] ) ) {
		return;
	}
	if ( ! is_user_logged_in() || ! nefertari_booking_plugin_active() ) {
		return;
	}

	$booking_id = absint( $_POST['nefertari_retry_payment'] );
	if ( ! isset( $_POST['nefertari_retry_nonce'] ) || ! wp_verify_nonce( $_POST['nefertari_retry_nonce'], 'nefertari_retry_payment_' . $booking_id ) ) {
		wp_safe_redirect( add_query_arg( 'retry_error', 'invalid_request', nefertari_account_url( 'account' ) ) );
		exit;
	}

	$booking = ( new \Nefertari\Booking\Services\Booking_Service() )->prepare_for_retry( $booking_id, get_current_user_id() );
	if ( is_wp_error( $booking ) ) {
		wp_safe_redirect( add_query_arg( 'retry_error', $booking->get_error_code(), nefertari_account_url( 'account' ) ) );
		exit;
	}

	$settings = new \Nefertari\Booking\Settings\Settings();
	$method   = sanitize_key( wp_unslash( $_POST['payment_method'] ?? 'kashier' ) );
	$session  = 'paypal' === $method && $settings->get( 'paypal_enabled' )
		? ( new \Nefertari\Booking\Services\PayPal_Service( $settings ) )->create_order( $booking_id )
		: ( new \Nefertari\Booking\Services\Kashier_Service( $settings ) )->create_payment_session( $booking_id );

	$checkout_url = $session['checkout_url'] ?? '';
	if ( $checkout_url ) {
		wp_safe_redirect( $checkout_url );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'retry_error', 'payment_unavailable', nefertari_account_url( 'account' ) ) );
	exit;
}
add_action( 'template_redirect', 'nefertari_handle_retry_payment' );

function nefertari_retry_error_message( $code ) {
	$messages = array(
		'not_found'         => 'That booking could not be found.',
		'not_retryable'     => 'This booking can no longer be retried — its status has changed.',
		'slot_unavailable'  => 'Sorry, this departure no longer has enough availability. Please book a different date.',
		'payment_unavailable' => 'Payment couldn\'t be started — please contact us to complete this booking.',
		'invalid_request'  => 'Your session expired — please try again.',
	);
	return $messages[ $code ] ?? 'Something went wrong — please try again or contact us.';
}

/* -------------------------------------------------------------------------
 * Account page: self-service cancellation request
 * ---------------------------------------------------------------------- */

function nefertari_handle_cancellation_request() {
	if ( ! is_page( 'account' ) || ! isset( $_POST['nefertari_request_cancellation'] ) ) {
		return;
	}
	if ( ! is_user_logged_in() || ! nefertari_booking_plugin_active() ) {
		return;
	}

	$booking_id = absint( $_POST['nefertari_request_cancellation'] );
	if ( ! isset( $_POST['nefertari_cancel_nonce'] ) || ! wp_verify_nonce( $_POST['nefertari_cancel_nonce'], 'nefertari_request_cancellation_' . $booking_id ) ) {
		wp_safe_redirect( add_query_arg( 'cancel_error', '1', nefertari_account_url( 'account' ) ) );
		exit;
	}

	$reason = sanitize_textarea_field( wp_unslash( $_POST['reason'] ?? '' ) );
	$ok     = ( new \Nefertari\Booking\Services\Cancellation_Service() )->request_cancellation(
		$booking_id,
		get_current_user_id(),
		$reason ?: 'Requested by customer via My Account.'
	);

	wp_safe_redirect( add_query_arg( $ok ? 'cancel_requested' : 'cancel_error', '1', nefertari_account_url( 'account' ) ) );
	exit;
}
add_action( 'template_redirect', 'nefertari_handle_cancellation_request' );

/* -------------------------------------------------------------------------
 * Account page: customer review submission (text + star rating, no photo)
 * ---------------------------------------------------------------------- */

/**
 * Excursions a customer can leave a review for — anything they have a
 * confirmed or completed booking on, deduplicated. Used both to gate the
 * review form's visibility and to validate what's actually submitted.
 */
function nefertari_reviewable_excursions( $customer_id ) {
	if ( ! nefertari_booking_plugin_active() ) {
		return array();
	}
	$excursions = array();
	foreach ( ( new \Nefertari\Booking\Services\Booking_Service() )->for_customer( $customer_id ) as $booking ) {
		if ( in_array( $booking['status'], array( 'confirmed', 'completed' ), true ) ) {
			$excursions[ (int) $booking['excursion_id'] ] = get_the_title( (int) $booking['excursion_id'] );
		}
	}
	return $excursions;
}

function nefertari_handle_review_submit() {
	if ( ! is_page( 'account' ) || ! isset( $_POST['nefertari_submit_review'] ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	if ( ! isset( $_POST['nefertari_review_nonce'] ) || ! wp_verify_nonce( $_POST['nefertari_review_nonce'], 'nefertari_submit_review' ) ) {
		wp_safe_redirect( add_query_arg( 'review_error', 'invalid_request', nefertari_account_url( 'account' ) ) );
		exit;
	}

	$current_user = wp_get_current_user();
	$rating       = max( 1, min( 5, absint( $_POST['rating'] ?? 5 ) ) );
	$text         = sanitize_textarea_field( wp_unslash( $_POST['review_text'] ?? '' ) );
	$excursion_id = absint( $_POST['excursion_id'] ?? 0 );
	$reviewable   = nefertari_reviewable_excursions( $current_user->ID );

	if ( '' === trim( $text ) ) {
		wp_safe_redirect( add_query_arg( 'review_error', 'empty', nefertari_account_url( 'account' ) ) );
		exit;
	}
	if ( ! isset( $reviewable[ $excursion_id ] ) ) {
		wp_safe_redirect( add_query_arg( 'review_error', 'not_eligible', nefertari_account_url( 'account' ) ) );
		exit;
	}

	$review_id = wp_insert_post( array(
		'post_type'    => 'testimonial',
		// Pending, not publish: goes live only once reviewed in wp-admin
		// (Testimonials list) — this form has no photo/CAPTCHA/etc. to
		// screen submissions the way the rest of the site's forms don't
		// need to, so a moderation step takes that role instead.
		'post_status'  => 'pending',
		'post_title'   => $current_user->display_name,
		'post_content' => $text,
	) );
	if ( $review_id && ! is_wp_error( $review_id ) ) {
		update_post_meta( $review_id, '_nx_rating', $rating );
		update_post_meta( $review_id, '_nx_meta_line', $reviewable[ $excursion_id ] );
		update_post_meta( $review_id, '_nx_customer_id', $current_user->ID );
	}

	wp_safe_redirect( add_query_arg( 'review_submitted', '1', nefertari_account_url( 'account' ) ) );
	exit;
}
add_action( 'template_redirect', 'nefertari_handle_review_submit' );

function nefertari_review_error_message( $code ) {
	$messages = array(
		'empty'           => 'Please write a few words for your review.',
		'not_eligible'    => 'You can only review an excursion you\'ve completed with us.',
		'invalid_request' => 'Your session expired — please try again.',
	);
	return $messages[ $code ] ?? 'Something went wrong — please try again.';
}
