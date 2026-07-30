<?php
/**
 * Login page (page slug: login). Posts to itself; nefertari_handle_login_submit()
 * in inc/accounts.php processes it on template_redirect.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_user_logged_in() ) {
	wp_safe_redirect( nefertari_account_url( 'account' ) );
	exit;
}

get_header();

$error       = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';
$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : nefertari_account_url( 'account' );
?>
<div class="nx-auth-page">
	<h1>Log in</h1>
	<p class="nx-lede">Log in to book excursions and see your booking history.</p>

	<div class="nx-panel">
		<?php if ( $error ) : ?>
			<div class="nx-form-error"><?php echo esc_html( nefertari_login_error_message( $error ) ); ?></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'nefertari_login', 'nefertari_login_nonce' ); ?>
			<input type="hidden" name="nefertari_login" value="1">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>">

			<label class="nx-field-label" for="user_login">Username or email</label>
			<input type="text" id="user_login" name="user_login" class="nx-input" required autofocus>

			<label class="nx-field-label" for="user_password">Password</label>
			<input type="password" id="user_password" name="user_password" class="nx-input" required>

			<button type="submit" class="nx-btn nx-btn--primary nx-btn--block">Log in</button>
		</form>

		<div class="nx-auth-switch">
			<a href="<?php echo esc_url( wp_lostpassword_url( $redirect_to ) ); ?>">Forgot your password?</a>
		</div>
	</div>

	<div class="nx-auth-switch">
		Don't have an account?
		<a href="<?php echo esc_url( add_query_arg( 'redirect_to', $redirect_to, nefertari_account_url( 'register' ) ) ); ?>">Create one</a>
	</div>
</div>
<?php
get_footer();
