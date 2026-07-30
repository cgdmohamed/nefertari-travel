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
	$pages = array(
		'login'           => 'Log In',
		'register'        => 'Create Account',
		'account'         => 'My Account',
		'payment-result'  => 'Payment Result',
	);
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
	}
}
add_action( 'after_switch_theme', 'nefertari_seed_account_pages' );

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
