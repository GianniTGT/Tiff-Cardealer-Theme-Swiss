<?php
/**
 * WP-CLI:
 *   wp bit setup                         Seiten, Menues, Startseite einrichten
 *   wp bit demo load | remove            Demo-Fahrzeuge (Testdaten)
 *   wp bit as24 import --file=<json>     Import aus Datei (z.B. Testdaten)  [--dry-run] [--demo]
 *   wp bit as24 sync [--dry-run]         Abgleich mit AutoScout24 (braucht Zugangsdaten); --dry-run = Probelauf
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

WP_CLI::add_command(
	'bit setup',
	function () {
		foreach ( bit_setup_site() as $line ) {
			WP_CLI::log( $line );
		}
		WP_CLI::success( 'Eingerichtet.' );
	}
);

WP_CLI::add_command(
	'bit demo',
	function ( $args ) {
		$do = $args[0] ?? 'load';
		if ( 'remove' === $do ) {
			WP_CLI::success( bit_demo_remove() . ' Demo-Fahrzeuge gelöscht.' );
			return;
		}
		$r = bit_demo_load();
		bit_cli_report( $r );
	}
);

WP_CLI::add_command(
	'bit as24',
	function ( $args, $assoc ) {
		$do = $args[0] ?? 'import';
		if ( 'sync' === $do ) {
			$r = bit_as24_sync( isset( $assoc['dry-run'] ) );
			bit_cli_report( $r );
			if ( ! empty( $r['raw_file'] ) ) {
				WP_CLI::log( 'Rohdaten: ' . bit_as24_raw_dir() . '/' . $r['raw_file'] );
			}
			return;
		}
		$file = $assoc['file'] ?? '';
		if ( ! $file || ! is_readable( $file ) ) {
			WP_CLI::error( 'Datei fehlt: --file=<pfad.json>' );
		}
		$items = bit_as24_extract_items( json_decode( (string) file_get_contents( $file ), true ) );
		bit_cli_report(
			bit_as24_import(
				$items,
				array(
					'dry_run' => isset( $assoc['dry-run'] ),
					'demo'    => isset( $assoc['demo'] ),
				)
			)
		);
	}
);

function bit_cli_report( $r ) {
	foreach ( (array) ( $r['log'] ?? array() ) as $line ) {
		WP_CLI::log( '  ' . $line );
	}
	foreach ( (array) ( $r['errors'] ?? array() ) as $err ) {
		WP_CLI::warning( $err );
	}
	WP_CLI::success(
		sprintf(
			'%sneu %d, geändert %d, gleich %d, verkauft %d, übersprungen %d',
			! empty( $r['dry_run'] ) ? '(Probelauf) ' : '',
			(int) ( $r['created'] ?? 0 ),
			(int) ( $r['updated'] ?? 0 ),
			(int) ( $r['unchanged'] ?? 0 ),
			(int) ( $r['sold'] ?? 0 ),
			(int) ( $r['skipped'] ?? 0 )
		)
	);
}
