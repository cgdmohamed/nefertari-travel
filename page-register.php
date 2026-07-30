<?php
/**
 * Registration page (page slug: register). Posts to itself;
 * nefertari_handle_register_submit() in inc/accounts.php processes it.
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
	<h1>Create your account</h1>
	<p class="nx-lede">Create an account to book excursions and pay securely online.</p>

	<div class="nx-panel">
		<?php if ( $error ) : ?>
			<div class="nx-form-error"><?php echo esc_html( nefertari_register_error_message( $error ) ); ?></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'nefertari_register', 'nefertari_register_nonce' ); ?>
			<input type="hidden" name="nefertari_register" value="1">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>">

			<label class="nx-field-label" for="name">Full name</label>
			<input type="text" id="name" name="name" class="nx-input" required autofocus>

			<label class="nx-field-label" for="email">Email</label>
			<input type="email" id="email" name="email" class="nx-input" required>

			<label class="nx-field-label" for="password">Password</label>
			<input type="password" id="password" name="password" class="nx-input" minlength="8" required>

			<label class="nx-field-label" for="password2">Confirm password</label>
			<input type="password" id="password2" name="password2" class="nx-input" minlength="8" required>

			<button type="submit" class="nx-btn nx-btn--primary nx-btn--block">Create account</button>
		</form>
	</div>

	<div class="nx-auth-switch">
		Already have an account?
		<a href="<?php echo esc_url( add_query_arg( 'redirect_to', $redirect_to, nefertari_account_url( 'login' ) ) ); ?>">Log in</a>
	</div>
</div>
<?php
get_footer();
