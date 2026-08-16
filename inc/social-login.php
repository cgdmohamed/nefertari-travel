<?php
/**
 * "Continue with Google" / "Continue with Facebook" on the Login and
 * Create Account pages. Standard OAuth2 authorization-code flow against
 * each provider's own endpoints directly (wp_remote_*) — no SDKs, matching
 * how Kashier/PayPal are integrated elsewhere in this codebase.
 *
 * Credentials + on/off switches live in the Customizer (Social Login
 * section), read via nefertari_option() like everything else theme-owned.
 *
 * Account matching, in order: an existing user already linked to this
 * provider ID -> an existing user with the same email (gets linked) -> a
 * brand new account. This means a customer who registered normally and
 * later clicks "Continue with Google" with the same email signs into
 * their existing account rather than getting a duplicate.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_oauth_providers() {
	return array( 'google', 'facebook' );
}

function nefertari_oauth_provider_enabled( $provider ) {
	return (bool) nefertari_option( $provider . '_login_enabled' );
}

/**
 * The URL each provider redirects back to after the user approves —
 * register this exact URL in the provider's developer console.
 */
function nefertari_oauth_redirect_uri( $provider ) {
	return home_url( '/?nefertari_oauth_callback=' . $provider );
}

/**
 * The "Continue with…" button href — kicks off the flow, remembering
 * where to send the user back to once it's done.
 */
function nefertari_oauth_start_url( $provider, $redirect_to ) {
	return home_url( '/?nefertari_oauth=' . $provider . '&redirect_to=' . rawurlencode( $redirect_to ) );
}

function nefertari_oauth_url( $base, array $params ) {
	return $base . '?' . http_build_query( $params );
}

/* -------------------------------------------------------------------------
 * Step 1: redirect to the provider
 * ---------------------------------------------------------------------- */

function nefertari_handle_oauth_start() {
	if ( ! isset( $_GET['nefertari_oauth'] ) ) {
		return;
	}
	$provider = sanitize_key( wp_unslash( $_GET['nefertari_oauth'] ) );
	if ( ! in_array( $provider, nefertari_oauth_providers(), true ) || ! nefertari_oauth_provider_enabled( $provider ) ) {
		wp_safe_redirect( nefertari_account_url( 'login' ) );
		exit;
	}

	// The state token both prevents CSRF (the callback only accepts a code
	// paired with a state it issued) and carries the post-login redirect,
	// stored server-side rather than round-tripped through the provider.
	$state       = wp_generate_password( 32, false );
	$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : nefertari_account_url( 'account' );
	set_transient( 'nefertari_oauth_' . $state, $redirect_to, 10 * MINUTE_IN_SECONDS );

	$redirect_uri = nefertari_oauth_redirect_uri( $provider );

	if ( 'google' === $provider ) {
		$url = nefertari_oauth_url( 'https://accounts.google.com/o/oauth2/v2/auth', array(
			'client_id'     => nefertari_option( 'google_client_id' ),
			'redirect_uri'  => $redirect_uri,
			'response_type' => 'code',
			'scope'         => 'openid email profile',
			'state'         => $state,
			'prompt'        => 'select_account',
		) );
	} else {
		$url = nefertari_oauth_url( 'https://www.facebook.com/v21.0/dialog/oauth', array(
			'client_id'    => nefertari_option( 'facebook_app_id' ),
			'redirect_uri' => $redirect_uri,
			'state'        => $state,
			'scope'        => 'email,public_profile',
		) );
	}

	wp_redirect( $url ); // phpcs:ignore -- external provider URL, not a local one.
	exit;
}
add_action( 'template_redirect', 'nefertari_handle_oauth_start', 5 );

/* -------------------------------------------------------------------------
 * Step 2: the provider redirects back here with a code (or an error)
 * ---------------------------------------------------------------------- */

function nefertari_handle_oauth_callback() {
	if ( ! isset( $_GET['nefertari_oauth_callback'] ) ) {
		return;
	}
	$provider = sanitize_key( wp_unslash( $_GET['nefertari_oauth_callback'] ) );
	if ( ! in_array( $provider, nefertari_oauth_providers(), true ) ) {
		return;
	}

	$state       = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
	$redirect_to = $state ? get_transient( 'nefertari_oauth_' . $state ) : false;
	if ( ! $redirect_to ) {
		wp_safe_redirect( add_query_arg( 'error', 'invalid_request', nefertari_account_url( 'login' ) ) );
		exit;
	}
	delete_transient( 'nefertari_oauth_' . $state );

	if ( isset( $_GET['error'] ) || ! isset( $_GET['code'] ) ) {
		wp_safe_redirect( add_query_arg( 'error', 'oauth_cancelled', nefertari_account_url( 'login' ) ) );
		exit;
	}
	$code = sanitize_text_field( wp_unslash( $_GET['code'] ) );

	$profile = 'google' === $provider ? nefertari_google_profile( $code ) : nefertari_facebook_profile( $code );
	if ( is_wp_error( $profile ) ) {
		wp_safe_redirect( add_query_arg( 'error', $profile->get_error_code(), nefertari_account_url( 'login' ) ) );
		exit;
	}

	$user = nefertari_oauth_login_or_register( $provider, $profile['id'], $profile['email'], $profile['name'] );
	if ( is_wp_error( $user ) ) {
		wp_safe_redirect( add_query_arg( 'error', $user->get_error_code(), nefertari_account_url( 'login' ) ) );
		exit;
	}

	wp_set_auth_cookie( $user->ID, true );
	wp_safe_redirect( $redirect_to );
	exit;
}
add_action( 'template_redirect', 'nefertari_handle_oauth_callback', 5 );

/* -------------------------------------------------------------------------
 * Provider profile fetches
 * ---------------------------------------------------------------------- */

function nefertari_google_profile( $code ) {
	$token_response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
		'timeout' => 20,
		'body'    => array(
			'code'          => $code,
			'client_id'     => nefertari_option( 'google_client_id' ),
			'client_secret' => nefertari_option( 'google_client_secret' ),
			'redirect_uri'  => nefertari_oauth_redirect_uri( 'google' ),
			'grant_type'    => 'authorization_code',
		),
	) );
	if ( is_wp_error( $token_response ) ) {
		return new WP_Error( 'google_token_failed', $token_response->get_error_message() );
	}
	$tokens = json_decode( wp_remote_retrieve_body( $token_response ), true );
	if ( empty( $tokens['access_token'] ) ) {
		return new WP_Error( 'google_token_failed', 'Could not get a Google access token.' );
	}

	$profile_response = wp_remote_get( 'https://openidconnect.googleapis.com/v1/userinfo', array(
		'timeout' => 20,
		'headers' => array( 'Authorization' => 'Bearer ' . $tokens['access_token'] ),
	) );
	if ( is_wp_error( $profile_response ) ) {
		return new WP_Error( 'google_profile_failed', $profile_response->get_error_message() );
	}
	$profile = json_decode( wp_remote_retrieve_body( $profile_response ), true );
	if ( empty( $profile['sub'] ) ) {
		return new WP_Error( 'google_profile_failed', 'Could not get your Google profile.' );
	}

	// Only trust the email for account matching/creation if Google itself
	// says it's verified — an unverified email shouldn't be enough to link
	// into (or newly claim) an account on someone else's behalf.
	$email_ok = ! empty( $profile['email'] ) && ! empty( $profile['email_verified'] );

	return array(
		'id'    => sanitize_text_field( $profile['sub'] ),
		'email' => $email_ok ? sanitize_email( $profile['email'] ) : '',
		'name'  => ! empty( $profile['name'] ) ? sanitize_text_field( $profile['name'] ) : '',
	);
}

function nefertari_facebook_profile( $code ) {
	$token_response = wp_remote_get( nefertari_oauth_url( 'https://graph.facebook.com/v21.0/oauth/access_token', array(
		'client_id'     => nefertari_option( 'facebook_app_id' ),
		'redirect_uri'  => nefertari_oauth_redirect_uri( 'facebook' ),
		'client_secret' => nefertari_option( 'facebook_app_secret' ),
		'code'          => $code,
	) ), array( 'timeout' => 20 ) );
	if ( is_wp_error( $token_response ) ) {
		return new WP_Error( 'facebook_token_failed', $token_response->get_error_message() );
	}
	$tokens = json_decode( wp_remote_retrieve_body( $token_response ), true );
	if ( empty( $tokens['access_token'] ) ) {
		return new WP_Error( 'facebook_token_failed', 'Could not get a Facebook access token.' );
	}

	$profile_response = wp_remote_get( nefertari_oauth_url( 'https://graph.facebook.com/me', array(
		'fields'       => 'id,name,email',
		'access_token' => $tokens['access_token'],
	) ), array( 'timeout' => 20 ) );
	if ( is_wp_error( $profile_response ) ) {
		return new WP_Error( 'facebook_profile_failed', $profile_response->get_error_message() );
	}
	$profile = json_decode( wp_remote_retrieve_body( $profile_response ), true );
	if ( empty( $profile['id'] ) ) {
		return new WP_Error( 'facebook_profile_failed', 'Could not get your Facebook profile.' );
	}

	return array(
		'id'    => sanitize_text_field( $profile['id'] ),
		'email' => ! empty( $profile['email'] ) ? sanitize_email( $profile['email'] ) : '',
		'name'  => ! empty( $profile['name'] ) ? sanitize_text_field( $profile['name'] ) : '',
	);
}

/* -------------------------------------------------------------------------
 * Match or create the WordPress user
 * ---------------------------------------------------------------------- */

function nefertari_oauth_login_or_register( $provider, $provider_user_id, $email, $name ) {
	$meta_key = '_nefertari_' . $provider . '_id';

	$linked = get_users( array( 'meta_key' => $meta_key, 'meta_value' => $provider_user_id, 'number' => 1 ) ); // phpcs:ignore -- small, one-off lookup, no pagination needed.
	if ( $linked ) {
		return $linked[0];
	}

	if ( ! $email ) {
		return new WP_Error( 'oauth_no_email', ucfirst( $provider ) . " didn't share an email address with us — please try a different sign-in method." );
	}

	$existing = get_user_by( 'email', $email );
	if ( $existing ) {
		update_user_meta( $existing->ID, $meta_key, $provider_user_id );
		return $existing;
	}

	if ( ! get_option( 'users_can_register' ) ) {
		return new WP_Error( 'registration_closed', 'New account registration is currently closed.' );
	}

	$username = nefertari_unique_username_from_email( $email );
	$user_id  = wp_insert_user( array(
		'user_login'   => $username,
		'user_email'   => $email,
		'user_pass'    => wp_generate_password( 32 ),
		'display_name' => $name ?: $username,
		'first_name'   => $name ?: '',
		'role'         => nefertari_booking_plugin_active() ? 'nefertari_customer' : get_option( 'default_role', 'subscriber' ),
	) );
	if ( is_wp_error( $user_id ) ) {
		return new WP_Error( 'registration_failed', 'Something went wrong creating your account. Please try again.' );
	}

	update_user_meta( $user_id, $meta_key, $provider_user_id );
	return get_userdata( $user_id );
}
