<?php
/**
 * Footer: 4-column footer, payment/legal bands, floating WhatsApp button,
 * and the booking modal markup (present on every page).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog/' );
$wa_generic = nefertari_whatsapp_link( "Hi " . get_bloginfo( 'name' ) . ", I'd like to know more about your excursion programs." );
?>
	<footer class="nx-footer">
		<div class="nx-foot">
			<div>
				<div class="nx-foot-brand">
					<?php if ( has_custom_logo() ) : ?>
						<span class="nx-foot-mark nx-foot-mark--logo"><?php the_custom_logo(); ?></span>
					<?php else : ?>
						<span class="nx-foot-mark"><?php echo esc_html( mb_substr( get_bloginfo( 'name' ), 0, 1 ) ); ?></span>
					<?php endif; ?>
					<div>
						<div class="nx-foot-name"><?php bloginfo( 'name' ); ?></div>
						<div class="nx-foot-sub">Red Sea &amp; Nile excursions · Egypt</div>
					</div>
				</div>
				<p class="nx-foot-desc"><?php echo esc_html( nefertari_option( 'footer_description', "Locally owned and Ministry-licensed since 2014. We've shown over 12,000 travellers the real Egypt — safely, comfortably and with people who love it." ) ); ?></p>
				<div class="nx-foot-rating"><span class="nx-stars">★★★★★</span> <strong style="color:#fff"><?php echo esc_html( nefertari_option( 'rating', '4.9' ) ); ?></strong> · <?php echo esc_html( nefertari_option( 'reviews_count', '2,400+' ) ); ?> reviews</div>
				<div class="nx-foot-socials">
					<a href="<?php echo esc_url( $wa_generic ); ?>" target="_blank" rel="noopener" class="nx-foot-social"><?php echo nefertari_icon( 'whatsapp', '#fff', 18 ); ?></a>
					<?php if ( nefertari_option( 'social_facebook' ) ) : ?><a href="<?php echo esc_url( nefertari_option( 'social_facebook' ) ); ?>" target="_blank" rel="noopener" class="nx-foot-social"><?php echo nefertari_icon( 'facebook', '#fff', 18 ); ?></a><?php endif; ?>
					<?php if ( nefertari_option( 'social_instagram' ) ) : ?><a href="<?php echo esc_url( nefertari_option( 'social_instagram' ) ); ?>" target="_blank" rel="noopener" class="nx-foot-social"><?php echo nefertari_icon( 'instagram', '#fff', 18 ); ?></a><?php endif; ?>
					<?php if ( nefertari_option( 'social_x' ) ) : ?><a href="<?php echo esc_url( nefertari_option( 'social_x' ) ); ?>" target="_blank" rel="noopener" class="nx-foot-social"><?php echo nefertari_icon( 'x-twitter', '#fff', 16 ); ?></a><?php endif; ?>
				</div>
			</div>
			<div>
				<div class="nx-foot-col-title">Excursions</div>
				<div class="nx-foot-links">
					<?php
					$excursions = new WP_Query( array( 'post_type' => 'excursion', 'posts_per_page' => -1, 'no_found_rows' => true ) );
					foreach ( $excursions->posts as $ex ) :
						?>
						<a href="<?php echo esc_url( get_permalink( $ex ) ); ?>"><?php echo esc_html( get_the_title( $ex ) ); ?></a>
						<?php
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			</div>
			<div>
				<div class="nx-foot-col-title">Company</div>
				<div class="nx-foot-links">
					<a href="<?php echo esc_url( home_url( '/#why' ) ); ?>">Why choose us</a>
					<a href="<?php echo esc_url( home_url( '/#reviews' ) ); ?>">Reviews</a>
					<a href="<?php echo esc_url( $blog_url ); ?>">Blog &amp; guides</a>
					<a href="<?php echo esc_url( home_url( '/#excursions' ) ); ?>">All excursions</a>
					<a href="<?php echo esc_url( nefertari_page_url( 'contact' ) ); ?>">Contact us</a>
					<a href="<?php echo esc_url( $wa_generic ); ?>" target="_blank" rel="noopener">FAQ &amp; help</a>
				</div>
			</div>
			<div>
				<div class="nx-foot-col-title">Get in touch</div>
				<div class="nx-foot-contact">
					<div class="nx-foot-contact-row"><?php echo nefertari_icon( 'pin', '#FBBA6A', 16 ); ?> <?php echo esc_html( nefertari_option( 'address', 'Sheraton Road, Hurghada, Red Sea Governorate, Egypt' ) ); ?></div>
					<div class="nx-foot-contact-row"><a href="tel:<?php echo esc_attr( nefertari_phone_tel() ); ?>" class="is-strong"><?php echo nefertari_icon( 'phone', '#FBBA6A', 16 ); ?> <?php echo esc_html( nefertari_option( 'phone', '+20 111 202 6922' ) ); ?></a></div>
					<div class="nx-foot-contact-row"><a href="mailto:<?php echo esc_attr( nefertari_option( 'email', 'hello@nefertaritravel.com' ) ); ?>"><?php echo nefertari_icon( 'mail', '#FBBA6A', 16 ); ?> <?php echo esc_html( nefertari_option( 'email', 'hello@nefertaritravel.com' ) ); ?></a></div>
					<div class="nx-foot-contact-row"><?php echo nefertari_icon( 'clock', '#FBBA6A', 16 ); ?> <?php echo esc_html( nefertari_option( 'hours', 'Open daily · 8:00am – 10:00pm' ) ); ?></div>
				</div>
			</div>
		</div>

		<div class="nx-foot-band">
			<div class="nx-foot-band-row">
				<div class="nx-payment-chips">
					<span style="color:var(--nx-brown)">We accept</span>
					<span class="nx-payment-chip">VISA</span>
					<span class="nx-payment-chip">Mastercard</span>
					<span class="nx-payment-chip">AMEX</span>
					<span class="nx-payment-chip">PayPal</span>
					<span class="nx-ssl-note">🔒 256-bit SSL secured</span>
				</div>
				<div class="nx-badges-row">
					<span>🛡️ Ministry of Tourism #<?php echo esc_html( nefertari_option( 'license_number', '4471' ) ); ?></span>
					<span>🏅 TripAdvisor Travellers' Choice</span>
				</div>
			</div>
		</div>
		<div class="nx-foot-band">
			<div class="nx-legal-row">
				<span>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</span>
				<span class="nx-legal-links">
					<?php
					$legal_pages = array(
						'privacy_page'      => 'Privacy',
						'terms_page'        => 'Terms',
						'cancellation_page' => 'Cancellation policy',
					);
					foreach ( $legal_pages as $option => $label ) :
						$page_id = (int) nefertari_option( $option, 0 );
						$url     = $page_id ? get_permalink( $page_id ) : '#';
						?>
						<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</span>
			</div>
		</div>
	</footer>

	<a href="<?php echo esc_url( $wa_generic ); ?>" target="_blank" rel="noopener" class="nx-whatsapp-float">
		<?php echo nefertari_icon( 'whatsapp', '#fff', 22 ); ?> Chat with us
	</a>

	<?php get_template_part( 'template-parts/modal/booking-modal' ); ?>

</div><!-- .nx-page -->
<?php wp_footer(); ?>
</body>
</html>
