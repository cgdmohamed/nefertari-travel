<?php
/**
 * "Continue with…" social login buttons + divider, shared by the Login and
 * Create Account pages. Pass $args = array( 'redirect_to' => $redirect_to ).
 * Renders nothing if neither provider is enabled.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$redirect_to     = $args['redirect_to'] ?? nefertari_account_url( 'account' );
$nx_has_google   = nefertari_oauth_provider_enabled( 'google' );
$nx_has_facebook = nefertari_oauth_provider_enabled( 'facebook' );

if ( ! $nx_has_google && ! $nx_has_facebook ) {
	return;
}
?>
<div class="nx-oauth-buttons">
	<?php if ( $nx_has_google ) : ?>
		<a href="<?php echo esc_url( nefertari_oauth_start_url( 'google', $redirect_to ) ); ?>" class="nx-btn nx-btn--outline nx-btn--block nx-oauth-btn">
			<?php echo nefertari_google_mark(); ?> Continue with Google
		</a>
	<?php endif; ?>
	<?php if ( $nx_has_facebook ) : ?>
		<a href="<?php echo esc_url( nefertari_oauth_start_url( 'facebook', $redirect_to ) ); ?>" class="nx-btn nx-btn--outline nx-btn--block nx-oauth-btn">
			<?php echo nefertari_icon( 'facebook', '#1877F2', 18 ); ?> Continue with Facebook
		</a>
	<?php endif; ?>
</div>
<div class="nx-oauth-divider"><span>or</span></div>
