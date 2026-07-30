<?php
/**
 * Theme bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NEFERTARI_VERSION', '1.0.0' );
define( 'NEFERTARI_DIR', get_template_directory() );
define( 'NEFERTARI_URI', get_template_directory_uri() );

require NEFERTARI_DIR . '/inc/setup.php';
require NEFERTARI_DIR . '/inc/template-tags.php';
require NEFERTARI_DIR . '/inc/post-types.php';
require NEFERTARI_DIR . '/inc/meta-boxes.php';
require NEFERTARI_DIR . '/inc/customizer.php';
require NEFERTARI_DIR . '/inc/seed-content.php';
