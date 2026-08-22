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
 * Rate limiting — shared by login lockout and registration throttling.
 * ---------------------------------------------------------------------- */

/**
 * Best-effort client IP. This site sits behind Cloudflare, so REMOTE_ADDR
 * alone would just be Cloudflare's edge IP for every visitor.
 */
function nefertari_client_ip() {
	$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
	return preg_match( '/^[0-9a-fA-F:.]+$/', $ip ) ? $ip : '0.0.0.0';
}

/** How many hits have been recorded for $key in the current window. */
function nefertari_rl_count( $key ) {
	$data = get_transient( 'nefertari_rl_' . md5( $key ) );
	return is_array( $data ) ? (int) $data['count'] : 0;
}

/** Records one hit for $key, starting a fresh $window_seconds window if the previous one lapsed. */
function nefertari_rl_hit( $key, $window_seconds ) {
	$transient_key = 'nefertari_rl_' . md5( $key );
	$data = get_transient( $transient_key );
	$now  = time();
	if ( ! is_array( $data ) || ( $now - $data['start'] ) > $window_seconds ) {
		set_transient( $transient_key, array( 'start' => $now, 'count' => 1 ), $window_seconds );
		return;
	}
	set_transient( $transient_key, array( 'start' => $data['start'], 'count' => $data['count'] + 1 ), $window_seconds - ( $now - $data['start'] ) );
}

/* -------------------------------------------------------------------------
 * Login
 * ---------------------------------------------------------------------- */

/**
 * Failed-attempt lockout, independent of whatever security plugin (if any)
 * is active — wp_signon() still fires this native hook either way, so this
 * works regardless of which form calls it. Counts only failures, not
 * successful logins, so a legitimate user logging in often never trips it.
 */
function nefertari_login_lockout_key( $ip, $username ) {
	return 'login_fail_' . $ip . '_' . strtolower( $username );
}

add_action( 'wp_login_failed', function ( $username ) {
	nefertari_rl_hit( nefertari_login_lockout_key( nefertari_client_ip(), $username ), 15 * MINUTE_IN_SECONDS );
} );

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

	$lockout_key = nefertari_login_lockout_key( nefertari_client_ip(), $creds['user_login'] );
	if ( nefertari_rl_count( $lockout_key ) >= 8 ) {
		wp_safe_redirect( add_query_arg( 'error', 'rate_limited', nefertari_account_url( 'login' ) ) );
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
		'empty_fields'          => 'Please enter your username/email and password.',
		'invalid_login'         => 'That username/email and password don\'t match.',
		'invalid_request'       => 'Your session expired — please try again.',
		'oauth_cancelled'       => 'Sign-in was cancelled.',
		'oauth_no_email'        => "We couldn't get an email address from that account — please try a different sign-in method.",
		'registration_closed'   => 'New account registration is currently closed.',
		'registration_failed'   => 'Something went wrong creating your account. Please try again.',
		'google_token_failed'   => "Couldn't connect to Google — please try again.",
		'google_profile_failed' => "Couldn't get your Google profile — please try again.",
		'facebook_token_failed' => "Couldn't connect to Facebook — please try again.",
		'facebook_profile_failed' => "Couldn't get your Facebook profile — please try again.",
		'rate_limited'          => 'Too many attempts — please wait a few minutes and try again.',
	);
	return $messages[ $code ] ?? 'Something went wrong — please try again.';
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
	// Honeypot: a field hidden off-screen that no real visitor fills in, but
	// bots that auto-fill every input on a form do. Fail silently as if the
	// account had actually been created, rather than pointing bots at the
	// exact reason their submission was rejected.
	if ( '' !== trim( (string) ( $_POST['nx_website'] ?? '' ) ) ) {
		wp_safe_redirect( nefertari_account_url( 'login' ) );
		exit;
	}
	if ( nefertari_rl_count( 'register_' . nefertari_client_ip() ) >= 5 ) {
		wp_safe_redirect( add_query_arg( 'error', 'rate_limited', nefertari_account_url( 'register' ) ) );
		exit;
	}
	nefertari_rl_hit( 'register_' . nefertari_client_ip(), 15 * MINUTE_IN_SECONDS );

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
		'rate_limited'        => 'Too many attempts — please wait a few minutes and try again.',
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
 * confirmed or completed booking on, minus excursions they've already
 * reviewed (pending or published). Used both to gate which bookings show
 * a "Review" button and to validate what's actually submitted.
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
	if ( empty( $excursions ) ) {
		return array();
	}
	$reviewed = get_posts( array(
		'post_type'      => 'testimonial',
		'post_status'    => array( 'pending', 'publish' ),
		'meta_key'       => '_nx_customer_id',
		'meta_value'     => $customer_id,
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );
	foreach ( $reviewed as $review_id ) {
		unset( $excursions[ (int) get_post_meta( $review_id, '_nx_excursion_id', true ) ] );
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
	$excursion_id = absint( $_POST['excursion_id'] ?? 0 );
	$redirect_args = array( 'excursion_id' => $excursion_id );

	if ( ! isset( $_POST['nefertari_review_nonce'] ) || ! wp_verify_nonce( $_POST['nefertari_review_nonce'], 'nefertari_submit_review' ) ) {
		wp_safe_redirect( add_query_arg( array_merge( $redirect_args, array( 'review_error' => 'invalid_request' ) ), nefertari_account_url( 'account' ) ) );
		exit;
	}

	$current_user = wp_get_current_user();
	$rating       = max( 1, min( 5, absint( $_POST['rating'] ?? 5 ) ) );
	$text         = sanitize_textarea_field( wp_unslash( $_POST['review_text'] ?? '' ) );
	$reviewable   = nefertari_reviewable_excursions( $current_user->ID );

	if ( '' === trim( $text ) ) {
		wp_safe_redirect( add_query_arg( array_merge( $redirect_args, array( 'review_error' => 'empty' ) ), nefertari_account_url( 'account' ) ) );
		exit;
	}
	if ( ! isset( $reviewable[ $excursion_id ] ) ) {
		wp_safe_redirect( add_query_arg( array_merge( $redirect_args, array( 'review_error' => 'not_eligible' ) ), nefertari_account_url( 'account' ) ) );
		exit;
	}

	$review_id = wp_insert_post( array(
		'post_type'    => 'testimonial',
		// Pending, not publish: goes live only once reviewed in wp-admin
		// (Testimonials list) — uploaded photos are moderated here too,
		// so a moderation step covers what a CAPTCHA/photo-screen would.
		'post_status'  => 'pending',
		'post_title'   => $current_user->display_name,
		'post_content' => $text,
	) );
	if ( $review_id && ! is_wp_error( $review_id ) ) {
		update_post_meta( $review_id, '_nx_rating', $rating );
		update_post_meta( $review_id, '_nx_meta_line', $reviewable[ $excursion_id ] );
		update_post_meta( $review_id, '_nx_customer_id', $current_user->ID );
		update_post_meta( $review_id, '_nx_excursion_id', $excursion_id );
		nefertari_handle_review_images( $review_id );
	}

	wp_safe_redirect( add_query_arg( 'review_submitted', '1', nefertari_account_url( 'account' ) ) );
	exit;
}
add_action( 'template_redirect', 'nefertari_handle_review_submit' );

/**
 * Attaches up to 6 uploaded review photos to the testimonial post via
 * WordPress's own media-upload pipeline (media_handle_upload) — the same
 * path wp-admin file uploads use, so the real file signature is validated,
 * not just the client-supplied extension/MIME type.
 */
function nefertari_handle_review_images( int $review_id ) {
	if ( empty( $_FILES['review_images']['name'][0] ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$allowed_types  = array( 'image/jpeg', 'image/png', 'image/webp' );
	$max_images     = 6;
	$max_bytes      = 5 * MB_IN_BYTES;
	$attachment_ids = array();
	$file_count     = count( $_FILES['review_images']['name'] );

	for ( $i = 0; $i < $file_count && count( $attachment_ids ) < $max_images; $i++ ) {
		if ( UPLOAD_ERR_OK !== $_FILES['review_images']['error'][ $i ] ) {
			continue;
		}
		if ( ! in_array( $_FILES['review_images']['type'][ $i ], $allowed_types, true ) ) {
			continue;
		}
		if ( $_FILES['review_images']['size'][ $i ] > $max_bytes ) {
			continue;
		}
		$_FILES['nefertari_review_image'] = array(
			'name'     => $_FILES['review_images']['name'][ $i ],
			'type'     => $_FILES['review_images']['type'][ $i ],
			'tmp_name' => $_FILES['review_images']['tmp_name'][ $i ],
			'error'    => $_FILES['review_images']['error'][ $i ],
			'size'     => $_FILES['review_images']['size'][ $i ],
		);
		$attachment_id = media_handle_upload( 'nefertari_review_image', $review_id, array(), array( 'test_form' => false ) );
		if ( ! is_wp_error( $attachment_id ) ) {
			$attachment_ids[] = $attachment_id;
		}
	}
	unset( $_FILES['nefertari_review_image'] );

	if ( ! empty( $attachment_ids ) ) {
		update_post_meta( $review_id, '_nx_review_images', $attachment_ids );
	}
}

function nefertari_review_error_message( $code ) {
	$messages = array(
		'empty'           => 'Please write a few words for your review.',
		'not_eligible'    => 'You can only review an excursion you\'ve completed with us.',
		'invalid_request' => 'Your session expired — please try again.',
	);
	return $messages[ $code ] ?? 'Something went wrong — please try again.';
}
