<?php
/**
 * Plugin Name: BIT – AutoScout24-Attrappe (nur lokal)
 * Description: Fuer den Browser-Test des Probelaufs. NIE auf den echten Server kopieren.
 * Nach wp-content/mu-plugins/ kopieren; aktiv nur, wenn die Option bit_as24_mock = 1 ist.
 */
add_action(
	'after_setup_theme',
	function () {
		if ( get_option( 'bit_as24_mock' ) && defined( 'BIT_DIR' ) && is_readable( BIT_DIR . '/tests/as24-mock.php' ) ) {
			require_once BIT_DIR . '/tests/as24-mock.php';
			add_filter( 'pre_http_request', 'bit_as24_mock_response', 10, 3 );
		}
	}
);
