<?php
/**
 * Collapsible day-by-day itinerary. Uses native <details>/<summary> so the
 * accordion needs no JavaScript.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$days = nefertari_excursion_itinerary( get_the_ID() );
if ( empty( $days ) ) {
	return;
}

$multi = count( $days ) > 1;
?>
<h3 class="nx-subheading" style="margin-bottom:6px">Excursion timeline</h3>
<p class="nx-itinerary-note">Tap a day to expand the full step-by-step plan.</p>

<?php foreach ( $days as $day_index => $day ) : ?>
	<?php $steps = isset( $day['steps'] ) ? $day['steps'] : array(); ?>
	<details class="nx-day"<?php echo 0 === $day_index ? ' open' : ''; ?>>
		<summary class="nx-day-head">
			<div class="nx-day-head-left">
				<span class="nx-day-label"><?php echo $multi ? 'Day ' . ( $day_index + 1 ) : 'Itinerary'; ?></span>
				<span class="nx-day-title"><?php echo esc_html( $day['label'] ); ?></span>
			</div>
			<span class="nx-day-chev"></span>
		</summary>
		<div class="nx-day-body">
			<?php foreach ( $steps as $step_index => $step ) : ?>
				<div class="nx-step">
					<div class="nx-step-rail">
						<span class="nx-step-dot"></span>
						<?php if ( $step_index !== count( $steps ) - 1 ) : ?><span class="nx-step-line"></span><?php endif; ?>
					</div>
					<div class="nx-step-body">
						<div class="nx-step-time"><?php echo esc_html( $step['time'] ); ?></div>
						<div class="nx-step-title"><?php echo esc_html( $step['title'] ); ?></div>
						<div class="nx-step-text"><?php echo esc_html( $step['text'] ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</details>
<?php endforeach; ?>
