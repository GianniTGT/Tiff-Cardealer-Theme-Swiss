<?php
/**
 * BIT Automobile — Theme-Einstieg.
 *
 * Alles, was die Seite kann, liegt in inc/: Firmendaten, Fahrzeuge,
 * Anfragen, Seiten-Einrichtung, Login, AutoScout24-Anbindung.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BIT_VERSION', '1.0.0' );
define( 'BIT_DIR', get_template_directory() );
define( 'BIT_URI', get_template_directory_uri() );

require BIT_DIR . '/inc/company.php';
require BIT_DIR . '/inc/setup.php';
require BIT_DIR . '/inc/vehicles.php';
require BIT_DIR . '/inc/vehicle-admin.php';
require BIT_DIR . '/inc/template-tags.php';
require BIT_DIR . '/inc/leads.php';
require BIT_DIR . '/inc/seed.php';
require BIT_DIR . '/inc/login.php';
require BIT_DIR . '/inc/admin.php';
require BIT_DIR . '/inc/autoscout24.php';
require BIT_DIR . '/inc/demo.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require BIT_DIR . '/inc/cli.php';
}
